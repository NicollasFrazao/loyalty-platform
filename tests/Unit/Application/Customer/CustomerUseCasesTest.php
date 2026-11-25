<?php

declare(strict_types=1);

use App\Application\Customer\ProfileInput;
use App\Domain\Customer\CodePolicy;
use App\Domain\Customer\CodeRefused;
use App\Domain\Customer\Customer;
use App\Domain\Customer\CustomerId;
use App\Domain\Customer\CustomerNotFound;
use App\Domain\Customer\InvalidCustomerData;
use App\Domain\Customer\TooManyCodes;
use App\Domain\Shared\Name;
use App\Domain\Tenancy\Company;
use App\Domain\Tenancy\CompanyId;
use App\Domain\Tenancy\CompanyNotFound;
use App\Domain\Tenancy\CompanyStatus;
use App\Domain\Tenancy\CompanySuspended;
use App\Domain\Tenancy\Plan;
use App\Domain\Tenancy\Subscription;
use App\Infrastructure\Customer\HmacOneTimeCodes;
use Tests\Support\Customer\CustomerKit;

it('signs a phone up with the code it received, and the same customer in the next time', function (): void {
    $kit = new CustomerKit;

    $first = $kit->signUp('+5511912345678');
    $kit->clock->advance('PT1H');
    $second = $kit->signUp('+5511912345678');

    expect($first)->toBe('1|Test phone')
        ->and($second)->toBe('1|Test phone')
        ->and($kit->customers->get(new CustomerId(1))->phoneVerifiedAt)->toEqual(new DateTimeImmutable('2026-11-25 12:00:00'));
});

it('refuses a wrong code, and closes the code after five wrong guesses', function (): void {
    $kit = new CustomerKit;
    $kit->requestCode()->handle('+5511912345678');
    $right = $kit->sender->codeFor('+5511912345678');
    $wrong = $right === '000000' ? '111111' : '000000';

    for ($guess = 1; $guess <= CodePolicy::MAX_ATTEMPTS; $guess++) {
        expect(static fn (): CustomerId => $kit->verifyCode()->handle('+5511912345678', $wrong))
            ->toThrow(CodeRefused::class, 'The code is wrong, expired or used up');
    }

    expect(static fn (): CustomerId => $kit->verifyCode()->handle('+5511912345678', $right))->toThrow(CodeRefused::class);
});

it('refuses a code once it expired and once it was used', function (): void {
    $kit = new CustomerKit;
    $verify = $kit->verifyCode();
    $kit->requestCode()->handle('+5511912345678');
    $expired = $kit->sender->codeFor('+5511912345678');
    $kit->clock->advance('PT5M');

    expect(static fn (): CustomerId => $verify->handle('+5511912345678', $expired))->toThrow(CodeRefused::class);

    $kit->requestCode()->handle('+5511912345678');
    $newest = $kit->sender->codeFor('+5511912345678');

    expect($verify->handle('+5511912345678', $newest))->toEqual(new CustomerId(1))
        ->and(static fn (): CustomerId => $verify->handle('+5511912345678', $newest))->toThrow(CodeRefused::class)
        ->and(static fn (): CustomerId => $verify->handle('+5511900000000', $newest))->toThrow(CodeRefused::class);
});

it('sends a phone three codes in fifteen minutes, and more once the window moves on', function (): void {
    $kit = new CustomerKit;
    $request = $kit->requestCode();

    $request->handle('+5511912345678');
    $request->handle('+5511912345678');
    $request->handle('+5511912345678');
    $request->handle('+5521987654321');

    expect(static fn () => $request->handle('+55 11 91234-5678'))->toThrow(TooManyCodes::class);

    $kit->clock->advance('PT15M');
    $request->handle('+5511912345678');

    expect($kit->sender->codeFor('+5511912345678'))->toMatch('/^\d{6}$/');
});

it('refuses a phone that is not international before sending anything', function (): void {
    $kit = new CustomerKit;

    expect(static fn () => $kit->requestCode()->handle('11912345678'))->toThrow(InvalidCustomerData::class)
        ->and(static fn (): CustomerId => $kit->verifyCode()->handle('11912345678', '123456'))->toThrow(InvalidCustomerData::class);
});

it('makes codes of six digits that only their own hash matches', function (): void {
    $codes = new HmacOneTimeCodes('a key');
    $issued = $codes->issue();

    expect($issued->code)->toMatch('/^\d{6}$/')
        ->and($issued->hash)->not->toContain($issued->code)
        ->and($codes->matches($issued->code, $issued->hash))->toBeTrue()
        ->and($codes->matches($issued->code, new HmacOneTimeCodes('another key')->issue()->hash))->toBeFalse()
        ->and(new HmacOneTimeCodes('another key')->matches($issued->code, $issued->hash))->toBeFalse();
});

it('changes the profile, erasing what is left empty, and refuses an invalid value', function (): void {
    $kit = new CustomerKit;
    $kit->signUp('+5511912345678');
    $me = new CustomerId(1);

    $changed = $kit->profile()->update($me, new ProfileInput('Maria', '1990-05-17', '529.982.247-25'));
    $cleared = $kit->profile()->update($me, new ProfileInput('Maria', '', ''));

    expect($changed->profile->document?->digits)->toBe('52998224725')
        ->and($cleared->profile->birthDate)->toBeNull()
        ->and($kit->profile()->show($me)->profile->name)->toEqual(Name::from('Maria'))
        ->and(static fn (): Customer => $kit->profile()->update($me, new ProfileInput('', '', '123')))->toThrow(InvalidCustomerData::class);
});

it('follows active companies once each, in order, and unfollows them', function (): void {
    $kit = new CustomerKit;
    $kit->signUp('+5511912345678');
    $me = new CustomerId(1);
    $following = $kit->followCompanies();

    $following->follow($me, new CompanyId(2));
    $following->follow($me, new CompanyId(1));
    $following->follow($me, new CompanyId(2));

    expect(array_map(static fn (Company $company): string => $company->name->value, $following->list($me)))->toBe(['Harbour Café', 'Corner Bar']);

    $following->unfollow($me, new CompanyId(2));

    expect($following->list($me))->toHaveCount(1)
        ->and(static fn () => $following->follow($me, new CompanyId(9)))->toThrow(CompanyNotFound::class);
});

it('refuses to follow a suspended company', function (): void {
    $kit = new CustomerKit;
    $kit->tenancy->companies->save(new Company(new CompanyId(1), Name::from('Corner Bar'), new Subscription(Plan::Starter, CompanyStatus::Suspended)));

    expect(static fn () => $kit->followCompanies()->follow(new CustomerId(1), new CompanyId(1)))->toThrow(CompanySuspended::class);
});

it('deletes an account: no data, no companies, no tokens, and the phone signs up anew', function (): void {
    $kit = new CustomerKit;
    $kit->signUp('+5511912345678');
    $kit->signUp('+5521987654321');
    $me = new CustomerId(1);
    $kit->followCompanies()->follow($me, new CompanyId(1));

    $kit->deleteAccount()->handle($me);

    expect(static fn (): Customer => $kit->profile()->show($me))->toThrow(CustomerNotFound::class)
        ->and(static fn () => $kit->deleteAccount()->handle($me))->toThrow(CustomerNotFound::class)
        ->and($kit->following->companiesOf($me))->toBe([])
        ->and($kit->tokens->tokens)->toBe(['2|Test phone']);

    $kit->clock->advance('PT1H');

    expect($kit->signUp('+5511912345678'))->toBe('3|Test phone');
});

it('signs a device out, keeping the other devices signed in', function (): void {
    $kit = new CustomerKit;
    $kit->requestCode()->handle('+5511912345678');
    $kit->session()->open('+5511912345678', $kit->sender->codeFor('+5511912345678'), 'Phone');
    $kit->requestCode()->handle('+5511912345678');
    $kit->session()->open('+5511912345678', $kit->sender->codeFor('+5511912345678'), 'Tablet');

    $kit->session()->close('1|Phone');

    expect($kit->tokens->tokens)->toBe(['1|Tablet']);
});
