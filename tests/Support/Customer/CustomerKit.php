<?php

declare(strict_types=1);

namespace Tests\Support\Customer;

use App\Application\Customer\CustomerSession;
use App\Application\Customer\DeleteAccount;
use App\Application\Customer\FollowCompanies;
use App\Application\Customer\ManageProfile;
use App\Application\Customer\RequestCode;
use App\Application\Customer\VerifyCode;
use App\Infrastructure\Customer\HmacOneTimeCodes;
use Tests\Support\Tenancy\TenancyKit;

/**
 * The customer use cases over in-memory ports, the two companies of {@see TenancyKit} and a clock
 * that stands still.
 */
final readonly class CustomerKit
{
    /**
     * The customers.
     */
    public InMemoryCustomerRepository $customers;

    /**
     * The codes sent.
     */
    public InMemoryCodeChallenges $challenges;

    /**
     * Who follows which company.
     */
    public InMemoryFollowing $following;

    /**
     * The tokens.
     */
    public InMemoryAccessTokens $tokens;

    /**
     * The codes as the phones receive them.
     */
    public RecordingOtpSender $sender;

    /**
     * The time.
     */
    public FixedClock $clock;

    /**
     * The companies and their staff.
     */
    public TenancyKit $tenancy;

    /**
     * Starts with no customer.
     */
    public function __construct()
    {
        $this->customers = new InMemoryCustomerRepository;
        $this->challenges = new InMemoryCodeChallenges;
        $this->following = new InMemoryFollowing;
        $this->tokens = new InMemoryAccessTokens;
        $this->sender = new RecordingOtpSender;
        $this->clock = new FixedClock;
        $this->tenancy = new TenancyKit;
    }

    /**
     * Sends codes.
     */
    public function requestCode(): RequestCode
    {
        return new RequestCode($this->challenges, new HmacOneTimeCodes('test key'), $this->sender, $this->clock);
    }

    /**
     * Checks codes.
     */
    public function verifyCode(): VerifyCode
    {
        return new VerifyCode($this->challenges, new HmacOneTimeCodes('test key'), $this->customers, $this->clock);
    }

    /**
     * Opens and closes sessions.
     */
    public function session(): CustomerSession
    {
        return new CustomerSession($this->verifyCode(), $this->tokens);
    }

    /**
     * Reads and changes profiles.
     */
    public function profile(): ManageProfile
    {
        return new ManageProfile($this->customers, $this->clock);
    }

    /**
     * Deletes accounts.
     */
    public function deleteAccount(): DeleteAccount
    {
        return new DeleteAccount($this->customers, $this->following, $this->tokens, $this->clock);
    }

    /**
     * Follows companies.
     */
    public function followCompanies(): FollowCompanies
    {
        return new FollowCompanies($this->following, $this->tenancy->companies, $this->clock);
    }

    /**
     * Signs the phone up with the code it receives, and returns its token.
     */
    public function signUp(string $phone): string
    {
        $this->requestCode()->handle($phone);

        return $this->session()->open($phone, $this->sender->codeFor($phone), 'Test phone');
    }
}
