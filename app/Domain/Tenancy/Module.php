<?php

declare(strict_types=1);

namespace App\Domain\Tenancy;

/**
 * A feature a company turns on; its endpoints answer only while it is enabled.
 */
enum Module: string
{
    case CheckIn = 'check_in';
    case Cashback = 'cashback';
    case Vouchers = 'vouchers';
    case Memberships = 'memberships';
    case Notifications = 'notifications';
}
