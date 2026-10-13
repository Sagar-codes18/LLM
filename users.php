<?php
declare(strict_types=1);

/*
 * users.php — static demo accounts (no database yet).
 * index.php loads this file to check the login.
 * Replace this file with a real database lookup later.
 * Plain-text passwords are fine for a demo only. Never ship them.
 */

return [
    [
        'username' => 'demo',
        'email'    => 'demo@example.com',
        'password' => 'Demo@1234',
        'name'     => 'Demo User',
        'phone'    => '+91 98765 43210',
    ],
];