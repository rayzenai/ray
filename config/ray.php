<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Allowed Users
    |--------------------------------------------------------------------------
    |
    | Define which users can access the Ray debug viewer by their email.
    | Use ['*'] to allow all authenticated users.
    | Leave empty [] to see a configuration reminder.
    |
    */

    'allowed_emails' => [
        // '*',                    // Allow all authenticated users
        // 'admin@example.com',    // Or specify individual emails
    ],

    /*
    |--------------------------------------------------------------------------
    | User Model Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the user model class and email field for your application.
    |
    */

    'user_model' => \App\Models\User::class,

    'user_email_field' => 'email',

    /*
    |--------------------------------------------------------------------------
    | Slow Query Threshold
    |--------------------------------------------------------------------------
    |
    | Queries taking longer than this threshold (in milliseconds) will be
    | automatically logged to the Ray debug viewer.
    |
    */

    'slow_query_threshold' => 100,

    /*
    |--------------------------------------------------------------------------
    | Max Entries
    |--------------------------------------------------------------------------
    |
    | Maximum number of debug entries to keep in the log file.
    |
    */

    'max_entries' => 100,

    /*
    |--------------------------------------------------------------------------
    | Storage Path
    |--------------------------------------------------------------------------
    |
    | Where to store the debug log file. Defaults to storage/logs/ray-debug.json
    |
    */

    'storage_path' => storage_path('logs/ray-debug.json'),
];
