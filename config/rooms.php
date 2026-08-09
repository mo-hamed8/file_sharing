<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Room Durations
    |--------------------------------------------------------------------------
    |
    | Selectable room lifetimes, in hours, offered when creating a room.
    |
    */

    'durations' => [1, 3, 6, 24],

    'default_duration' => 3,

    /*
    |--------------------------------------------------------------------------
    | Room Join Code
    |--------------------------------------------------------------------------
    |
    | The alphabet deliberately excludes ambiguous characters (0/O, 1/I/L).
    |
    */

    'code' => [
        'length' => 7,
        'alphabet' => 'ABCDEFGHJKMNPQRSTUVWXYZ23456789',
    ],

    /*
    |--------------------------------------------------------------------------
    | Tokens
    |--------------------------------------------------------------------------
    */

    'tokens' => [
        'host_length' => 40,
        'participant_length' => 40,
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage Disk
    |--------------------------------------------------------------------------
    |
    | Isolated from the default "local" disk so swapping to a cloud disk
    | (e.g. Google Cloud Storage) later is a one-line config change.
    |
    */

    'disk' => 'rooms',

    /*
    |--------------------------------------------------------------------------
    | Upload Limits (defaults, can be overridden per-room)
    |--------------------------------------------------------------------------
    */

    'uploads' => [
        'max_file_size' => 100 * 1024 * 1024, // 100 MB
        'max_files_per_room' => 200,
        'max_total_storage' => 1024 * 1024 * 1024, // 1 GB
        'blocked_extensions' => [
            'php', 'phtml', 'php3', 'php4', 'php5', 'phar',
            'exe', 'sh', 'bat', 'cmd', 'msi', 'dll', 'com', 'ps1', 'js', 'jar', 'py',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Live Updates
    |--------------------------------------------------------------------------
    */

    'polling_interval_seconds' => 5,

    /*
    |--------------------------------------------------------------------------
    | Rate Limits
    |--------------------------------------------------------------------------
    */

    'rate_limits' => [
        'room_creation' => ['max_attempts' => 60, 'decay_minutes' => 60],
        'room_join' => ['max_attempts' => 60, 'decay_minutes' => 60],
        'file_upload' => ['max_attempts' => 30, 'decay_minutes' => 1],
    ],

];
