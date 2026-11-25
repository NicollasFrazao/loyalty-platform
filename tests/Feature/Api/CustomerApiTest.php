<?php

declare(strict_types=1);

use App\Domain\Customer\OtpSender;
use App\Domain\Tenancy\Role;
use App\Infrastructure\Persistence\Eloquent\CompanyRecord;
use App\Infrastructure\Persistence\Eloquent\User;
use Illuminate\Auth\AuthManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response;
use Tests\Support\Customer\RecordingOtpSender;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\instance;
use function Pest\Laravel\json;
use function Pest\Laravel\patchJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;
use function Pest\Laravel\travel;
use function Pest\Laravel\travelTo;

/*
 * L2's checklist: a customer signs up by phone, follows a company and deletes the account; the code
 * expires and is rate-limited. The phone receives its codes through a recording sender.
 */
uses(RefreshDatabase::class);

beforeEach(function (): void {
    travelTo(new DateTimeImmutable('2026-11-25 12:00:00'));
    $sender = new RecordingOtpSender;
    instance(RecordingOtpSender::class, $sender);
    instance(OtpSender::class, $sender);
});

/**
 * The code the phone received last.
 */
function codeSentTo(string $phone): string
{
    return app(RecordingOtpSender::class)->codeFor($phone);
}

/**
 * Asks for a code and opens a session with it.
 *
 * @return TestResponse<Response>
 */
function signUpByPhone(string $phone): TestResponse
{
    postJson('/api/v1/auth/code', ['phone' => $phone])->assertAccepted();

    return postJson('/api/v1/auth/token', ['phone' => $phone, 'code' => codeSentTo($phone), 'device' => 'Pixel 9']);
}

/**
 * The token a session answer carries.
 *
 * @param  TestResponse<Response>  $response  The answer of `POST /api/v1/auth/token`.
 *
 * @throws UnexpectedValueException when it carries none.
 */
function tokenOf(TestResponse $response): string
{
    $token = $response->assertCreated()->json('token');

    return is_string($token) ? $token : throw new UnexpectedValueException('No token in the answer.');
}

/**
 * Signs the phone up, or in, and returns the session's token.
 *
 * @throws UnexpectedValueException when the answer carries no token.
 */
function tokenFor(string $phone): string
{
    return tokenOf(signUpByPhone($phone));
}

/**
 * The bearer header of a token, for the next request; the guards forget the last request's customer.
 *
 * @return array{Authorization: string}
 */
function bearer(string $token): array
{
    app(AuthManager::class)->forgetGuards();

    return ['Authorization' => "Bearer {$token}"];
}

it('signs a customer up by phone, follows a company and deletes the account', function (): void {
    $bar = CompanyRecord::factory()->create(['name' => 'Corner Bar']);
    $token = tokenOf(signUpByPhone('+55 11 91234-5678')->assertJsonPath('token_type', 'Bearer'));

    // Sanctum's tokens are "<id>|<prefix><secret>"; the prefix lets a secret scanner find a leaked one.
    expect($token)->toMatch('/^\d+\|loyalty_\w{40}/');

    $id = getJson('/api/v1/me', bearer($token))->json('id');

    getJson('/api/v1/me', bearer($token))->assertOk()->assertExactJson([
        'id' => $id,
        'phone' => '+5511912345678',
        'phone_verified_at' => '2026-11-25T12:00:00+00:00',
        'name' => null,
        'birth_date' => null,
        'document' => null,
    ]);
    patchJson('/api/v1/me', ['name' => 'Maria Souza', 'birth_date' => '1990-05-17', 'document' => '529.982.247-25'], bearer($token))
        ->assertOk()->assertJsonPath('document', '52998224725');
    putJson("/api/v1/me/companies/{$bar->id}", [], bearer($token))->assertNoContent();
    putJson("/api/v1/me/companies/{$bar->id}", [], bearer($token))->assertNoContent();
    getJson('/api/v1/me/companies', bearer($token))->assertOk()->assertExactJson(['data' => [['id' => $bar->id, 'name' => 'Corner Bar']]]);

    deleteJson('/api/v1/me', [], bearer($token))->assertNoContent();

    getJson('/api/v1/me', bearer($token))->assertUnauthorized();
    expect(DB::table('customers')->where('id', $id)->value('phone'))->toBeNull()
        ->and(DB::table('company_customers')->count())->toBe(0)
        ->and(DB::table('personal_access_tokens')->count())->toBe(0);
});

it('signs the same customer in again, and a deleted phone up as a new customer', function (): void {
    $first = tokenFor('+5511912345678');
    $id = getJson('/api/v1/me', bearer($first))->json('id');
    travel(20)->minutes();
    $second = tokenFor('+5511912345678');

    getJson('/api/v1/me', bearer($second))->assertJsonPath('id', $id);
    deleteJson('/api/v1/auth/token', [], bearer($first))->assertNoContent();
    getJson('/api/v1/me', bearer($first))->assertUnauthorized();
    deleteJson('/api/v1/me', [], bearer($second))->assertNoContent();
    travel(20)->minutes();

    expect(getJson('/api/v1/me', bearer(tokenFor('+5511912345678')))->json('id'))->not->toBe($id);
});

it('refuses a code once it expired, and a wrong code without telling why', function (): void {
    postJson('/api/v1/auth/code', ['phone' => '+5511912345678'])->assertAccepted();
    $code = codeSentTo('+5511912345678');
    $wrong = $code === '000000' ? '111111' : '000000';

    postJson('/api/v1/auth/token', ['phone' => '+5511912345678', 'code' => $wrong, 'device' => 'Pixel 9'])
        ->assertUnprocessable()->assertJsonValidationErrors(['code' => 'The code is wrong, expired or used up']);
    travel(5)->minutes();
    postJson('/api/v1/auth/token', ['phone' => '+5511912345678', 'code' => $code, 'device' => 'Pixel 9'])
        ->assertUnprocessable()->assertJsonValidationErrors('code');

    expect(DB::table('customers')->count())->toBe(0);
});

it('sends a phone three codes in fifteen minutes', function (): void {
    for ($sent = 1; $sent <= 3; $sent++) {
        postJson('/api/v1/auth/code', ['phone' => '+5511912345678'])->assertAccepted();
    }

    postJson('/api/v1/auth/code', ['phone' => '+5511912345678'])->assertTooManyRequests()->assertHeader('Retry-After', '900');
    travel(15)->minutes();
    postJson('/api/v1/auth/code', ['phone' => '+5511912345678'])->assertAccepted();
});

it('limits the code requests of an address, whatever the phones', function (): void {
    foreach (range(1, 10) as $phone) {
        postJson('/api/v1/auth/code', ['phone' => '+55119123456'.str_pad((string) $phone, 2, '0', STR_PAD_LEFT)])->assertAccepted();
    }

    postJson('/api/v1/auth/code', ['phone' => '+5511912345699'])->assertTooManyRequests();
});

it('refuses invalid input with the field that is wrong', function (): void {
    $token = tokenFor('+5511912345678');

    postJson('/api/v1/auth/code', ['phone' => '11 91234-5678'])->assertUnprocessable()->assertJsonValidationErrors('phone');
    postJson('/api/v1/auth/token', ['phone' => '+5511912345678', 'code' => '12ab56', 'device' => 'x'])->assertJsonValidationErrors('code');
    postJson('/api/v1/auth/token', ['phone' => '119', 'code' => '123456', 'device' => 'x'])->assertJsonValidationErrors('phone');
    patchJson('/api/v1/me', ['birth_date' => '2030-01-01'], bearer($token))->assertJsonValidationErrors('birth_date');
    patchJson('/api/v1/me', ['document' => '123.456.789-00'], bearer($token))->assertJsonValidationErrors('document');
});

it('refuses to follow a company that does not exist or is suspended', function (): void {
    $suspended = CompanyRecord::factory()->create(['status' => 'suspended']);
    $token = tokenFor('+5511912345678');

    putJson('/api/v1/me/companies/999', [], bearer($token))->assertNotFound();
    putJson("/api/v1/me/companies/{$suspended->id}", [], bearer($token))->assertNotFound();
    deleteJson("/api/v1/me/companies/{$suspended->id}", [], bearer($token))->assertNoContent();
});

it('answers 401 to a request without a token', function (string $method, string $uri): void {
    json($method, $uri)->assertUnauthorized()->assertExactJson(['message' => 'Unauthenticated.']);
})->with([
    ['GET', '/api/v1/me'],
    ['PATCH', '/api/v1/me'],
    ['DELETE', '/api/v1/me'],
    ['DELETE', '/api/v1/auth/token'],
    ['GET', '/api/v1/me/companies'],
    ['PUT', '/api/v1/me/companies/1'],
    ['DELETE', '/api/v1/me/companies/1'],
]);

it('does not open the API to a staff member signed in to the back office', function (): void {
    $owner = User::factory()->withRole(Role::Owner)->create(['company_id' => CompanyRecord::factory()->create()->id]);

    actingAs($owner)->getJson('/api/v1/me')->assertUnauthorized();
});

it('keeps each customer to their own account', function (): void {
    $bar = CompanyRecord::factory()->create();
    $maria = tokenFor('+5511912345678');
    $joao = tokenFor('+5521987654321');

    putJson("/api/v1/me/companies/{$bar->id}", [], bearer($maria))->assertNoContent();
    patchJson('/api/v1/me', ['name' => 'Maria'], bearer($maria))->assertOk();

    getJson('/api/v1/me', bearer($joao))->assertJsonPath('name', null)->assertJsonPath('phone', '+5521987654321');
    getJson('/api/v1/me/companies', bearer($joao))->assertExactJson(['data' => []]);
});
