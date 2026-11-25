<?php

declare(strict_types=1);

use App\Providers\AppServiceProvider;
use App\Providers\BackOfficeServiceProvider;
use App\Providers\CheckInServiceProvider;
use App\Providers\CustomerApiServiceProvider;
use App\Providers\CustomerServiceProvider;
use App\Providers\OneTimeCodeServiceProvider;
use App\Providers\TenancyServiceProvider;

return [
    AppServiceProvider::class,
    TenancyServiceProvider::class,
    BackOfficeServiceProvider::class,
    CustomerServiceProvider::class,
    OneTimeCodeServiceProvider::class,
    CustomerApiServiceProvider::class,
    CheckInServiceProvider::class,
];
