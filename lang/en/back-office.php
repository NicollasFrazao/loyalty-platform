<?php

declare(strict_types=1);

return [
    'title' => [
        'login' => 'Back office sign-in',
        'home' => 'Back office',
        'staff' => 'Staff',
    ],
    'fields' => [
        'email' => 'Email',
        'password' => 'Password',
        'password_confirmation' => 'Password again',
        'name' => 'Name',
        'role' => 'Role',
    ],
    'actions' => [
        'login' => 'Sign in',
        'logout' => 'Sign out',
        'enable' => 'Turn on',
        'disable' => 'Turn off',
        'add' => 'Add staff member',
        'remove' => 'Remove',
    ],
    'plan' => 'Plan: :plan (up to :staff staff members)',
    'module' => [
        'on' => 'on',
        'off' => 'off',
        'not_in_plan' => 'not in your plan',
    ],
    'roles' => [
        'owner' => 'Owner',
        'manager' => 'Manager',
        'operator' => 'Operator',
    ],
    'status' => [
        'modules' => 'Modules saved.',
        'added' => 'Staff member added.',
        'removed' => 'Staff member removed.',
    ],
    'errors' => [
        'credentials' => 'The email or the password is wrong.',
        'throttled' => 'Too many attempts. Try again in :seconds seconds.',
        'not_in_plan' => 'Your plan does not include this module.',
        'name' => 'Type a name of up to 100 characters.',
        'email' => 'Type a valid email address.',
        'password' => 'The password needs at least 8 characters and at most 72 bytes.',
        'taken' => 'A staff member already signs in with this email.',
        'limit' => 'Your plan allows no more staff members.',
    ],
];
