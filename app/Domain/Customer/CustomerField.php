<?php

declare(strict_types=1);

namespace App\Domain\Customer;

/**
 * The values of a customer the app sends, named as the API names them.
 */
enum CustomerField: string
{
    case Phone = 'phone';
    case Code = 'code';
    case Name = 'name';
    case BirthDate = 'birth_date';
    case Document = 'document';
}
