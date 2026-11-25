<?php

declare(strict_types=1);

use App\Domain\Tenancy\Role;
use App\Infrastructure\Persistence\Eloquent\CompanyModuleRecord;
use App\Infrastructure\Persistence\Eloquent\CompanyRecord;
use App\Infrastructure\Persistence\Eloquent\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\seed;

/*
 * L1's rule: a staff member of company A can never read or change data of company B, and modules gate
 * their endpoints. No route names a company: the company is the signed-in staff member's.
 */
uses(RefreshDatabase::class);

/**
 * A staff member of the company with the role, signing in with the password "password".
 */
function staffOf(CompanyRecord $company, Role $role, string $email): User
{
    return User::factory()->withRole($role)->create(['company_id' => $company->id, 'email' => $email]);
}

/**
 * Two companies with an owner each: A on the Starter plan, B on the Complete one.
 *
 * @return array{User, User}
 */
function twoOwners(): array
{
    $a = CompanyRecord::factory()->create(['name' => 'Corner Bar', 'plan' => 'starter']);
    $b = CompanyRecord::factory()->create(['name' => 'Harbour Café', 'plan' => 'complete']);

    return [staffOf($a, Role::Owner, 'owner@a.example'), staffOf($b, Role::Owner, 'owner@b.example')];
}

it('signs a staff member in to their company\'s back office, and refuses a wrong password', function (): void {
    [$owner] = twoOwners();

    post('/back-office/login', ['email' => 'OWNER@a.example', 'password' => 'password'])->assertRedirect('/back-office');
    get('/back-office')->assertOk()->assertSee('Corner Bar')->assertSee('Plan: starter');
    post('/back-office/logout')->assertRedirect('/back-office/login');
    post('/back-office/login', ['email' => 'owner@a.example', 'password' => 'wrong'])
        ->assertSessionHasErrors(['email' => 'The email or the password is wrong.']);

    expect($owner->exists)->toBeTrue();
});

it('shows each company only its own staff, whatever the request claims', function (): void {
    [$ownerA, $ownerB] = twoOwners();
    $companyB = $ownerB->company_id;

    actingAs($ownerA)->get("/back-office/staff?company_id={$companyB}")
        ->assertOk()
        ->assertSee('owner@a.example')
        ->assertDontSee('owner@b.example');
});

it('does not let company A remove or add staff in company B', function (): void {
    [$ownerA, $ownerB] = twoOwners();
    $operatorB = staffOf(CompanyRecord::query()->findOrFail($ownerB->company_id), Role::Operator, 'operator@b.example');

    actingAs($ownerA)->delete("/back-office/staff/{$operatorB->id}")->assertNotFound();
    actingAs($ownerA)->post('/back-office/staff', [
        'name' => 'Intruder', 'email' => 'intruder@x.example', 'role' => 'owner',
        'password' => 'correct horse', 'password_confirmation' => 'correct horse',
        'company_id' => $ownerB->company_id,
    ])->assertRedirect('/back-office/staff');

    expect(User::query()->where('email', 'intruder@x.example')->sole()->company_id)->toBe($ownerA->company_id)
        ->and(User::query()->whereKey($operatorB->id)->exists())->toBeTrue();
});

it('lets the owner add and remove staff, within the plan and never themselves', function (): void {
    [$owner] = twoOwners();
    $form = ['role' => 'operator', 'password' => 'correct horse', 'password_confirmation' => 'correct horse'];

    actingAs($owner)->post('/back-office/staff', [...$form, 'name' => 'Bia', 'email' => 'bia@a.example'])->assertRedirect('/back-office/staff');
    actingAs($owner)->post('/back-office/staff', [...$form, 'name' => 'Caio', 'email' => 'caio@a.example']);
    actingAs($owner)->from('/back-office/staff')->post('/back-office/staff', [...$form, 'name' => 'Dani', 'email' => 'dani@a.example'])
        ->assertSessionHasErrors(['email' => 'Your plan allows no more staff members.']);
    actingAs($owner)->delete("/back-office/staff/{$owner->id}")->assertForbidden();

    $bia = User::query()->where('email', 'bia@a.example')->sole();
    actingAs($owner)->delete("/back-office/staff/{$bia->id}")->assertRedirect('/back-office/staff');

    expect(User::query()->where('company_id', $owner->company_id)->pluck('email')->sort()->values()->all())
        ->toBe(['caio@a.example', 'owner@a.example']);
});

it('refuses each value it cannot accept, on its field', function (string $field, string $value, string $message): void {
    [$owner] = twoOwners();
    $form = ['name' => 'Bia', 'email' => 'bia@a.example', 'role' => 'operator', 'password' => 'correct horse', 'password_confirmation' => 'correct horse'];
    $form[$field] = $value;

    if ($field === 'password') {
        $form['password_confirmation'] = $value;
    }

    actingAs($owner)->from('/back-office/staff')->post('/back-office/staff', $form)->assertSessionHasErrors([$field => $message]);
})->with([
    ['email', 'bia', 'Type a valid email address.'],
    ['email', 'owner@b.example', 'A staff member already signs in with this email.'],
    ['password', 'short', 'The password needs at least 8 characters and at most 72 bytes.'],
    ['name', str_repeat('a', 101), 'Type a name of up to 100 characters.'],
]);

it('lets only owners manage staff and owners and managers configure modules', function (): void {
    [$owner] = twoOwners();
    $company = CompanyRecord::query()->findOrFail($owner->company_id);
    $manager = staffOf($company, Role::Manager, 'manager@a.example');
    $operator = staffOf($company, Role::Operator, 'operator@a.example');

    actingAs($manager)->delete("/back-office/staff/{$operator->id}")->assertForbidden();
    actingAs($manager)->put('/back-office/modules/check_in', ['enabled' => 1])->assertRedirect('/back-office');
    actingAs($operator)->put('/back-office/modules/cashback', ['enabled' => 1])->assertForbidden();
    actingAs($manager)->from('/back-office')->put('/back-office/modules/memberships', ['enabled' => 1])
        ->assertSessionHasErrors(['module' => 'Your plan does not include this module.']);
    actingAs($manager)->put('/back-office/modules/teleport', ['enabled' => 1])->assertNotFound();

    expect(CompanyModuleRecord::query()->where('company_id', $company->id)->pluck('module')->all())->toBe(['check_in']);
});

it('answers a module\'s routes only while the company has it enabled and is active', function (): void {
    Route::middleware(['web', 'auth', 'module:vouchers'])->get('/back-office/vouchers-probe', static fn (): string => 'vouchers');
    [$ownerA, $ownerB] = twoOwners();
    CompanyModuleRecord::query()->create(['company_id' => $ownerB->company_id, 'module' => 'vouchers']);

    actingAs($ownerA)->get('/back-office/vouchers-probe')->assertForbidden();
    actingAs($ownerB)->get('/back-office/vouchers-probe')->assertOk()->assertSee('vouchers');

    CompanyRecord::query()->whereKey($ownerB->company_id)->update(['status' => 'suspended']);

    actingAs($ownerB)->get('/back-office/vouchers-probe')->assertForbidden();
});

it('sends a guest to sign in, and a signed-in staff member away from the sign-in page', function (): void {
    [$owner] = twoOwners();

    get('/back-office/staff')->assertRedirect('/back-office/login');
    actingAs($owner)->get('/back-office/login')->assertRedirect('/back-office');
});

it('seeds two companies with an owner, a manager and an operator each, once', function (): void {
    seed();
    seed();

    expect(CompanyRecord::query()->pluck('name')->all())->toBe(['Corner Bar', 'Harbour Café'])
        ->and(User::query()->count())->toBe(6)
        ->and(User::query()->where('email', 'owner@corner-bar.example')->sole()->role)->toBe('owner');
});
