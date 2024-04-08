<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This option controls the default authentication "guard" and password
    | reset options for your application. You may change these defaults
    | as required, but they're a perfect start for most applications.
    |
     */

    'defaults'  => [
        'guard'     => 'backend_public',
        'passwords' => 'public_user',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Next, you may define every authentication guard for your application.
    | Of course, a great default configuration has been defined for you
    | here which uses session storage and the Eloquent user provider.
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | Supported: "session", "token"
    |
     */
    'guards'    => [
        // 'web'           => [
        //     'driver'   => 'session',
        //     'provider' => 'users',
        // ],

        // 'admin-user'    => [
        //     'driver'   => 'session',
        //     'provider' => 'bo_users',
        // ],

        // 'admin-partner' => [
        //     'driver'   => 'session',
        //     'provider' => 'user_partners',
        // ],

        // 'api'           => [
        //     'driver'   => 'jwt',
        //     'provider' => 'users',
        // ],

        // 'api-user'      => [
        //     'driver'   => 'jwt',
        //     'provider' => 'bo_users',
        // ],

        // 'api-partner'   => [
        //     'driver'   => 'jwt',
        //     'provider' => 'user_partners',
        // ],

        // 'api-customer'  => [
        //     'driver'   => 'jwt',
        //     'provider' => 'bo_customers',
        // ],
        // 'customer'      => [
        //     'driver'   => 'session',
        //     'provider' => 'bo_customers',
        // ],
        'backend_public'      => [
            'driver'   => 'session',
            'provider' => 'public_user',
        ],
        'public_user'  => [
            'driver'   => 'jwt',
            'provider' => 'public_user',
        ],
        'public_user_v2'  => [
            'driver'   => 'jwt',
            'provider' => 'public_user_v2',
        ],
    ],

    // 'guards'    => [
    //     'web'           => [
    //         'driver'   => 'session',
    //         'provider' => 'users',
    //     ],

    //     'admin-user'    => [
    //         'driver'   => 'session',
    //         'provider' => 'bo_users',
    //     ],

    //     'admin-partner' => [
    //         'driver'   => 'session',
    //         'provider' => 'user_partners',
    //     ],

    //     'api'           => [
    //         'driver'   => 'passport',
    //         'provider' => 'bo_users',
    //     ],

    //     'api-user'      => [
    //         'driver'   => 'session',
    //         'provider' => 'bo_users',
    //     ],

    //     'api-partner'   => [
    //         'driver'   => 'session',
    //         'provider' => 'user_partners',
    //     ],

    //     'api-customer'  => [
    //         'driver'   => 'session',
    //         'provider' => 'bo_customers',
    //     ],

    //     'customer'  => [
    //         'driver'   => 'session',
    //         'provider' => 'bo_customers',
    //     ],

    // ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    |
    |
     */

    'providers' => [
        'public_user'  => [
            'driver' => 'eloquent',
            'model'  => App\Models\PublicUser\Users::class,
        ],
        'public_user_v2'  => [
            'driver' => 'eloquent',
            'model'  => App\Models\PublicUser\V2\User::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    |
    | You may specify multiple password reset configurations if you have more
    | than one user table or model in the application and you want to have
    | separate password reset settings based on the specific user types.
    |
    | The expire time is the number of minutes that the reset token should be
    | considered valid. This security feature keeps tokens short-lived so
    | they have less time to be guessed. You may change this as needed.
    |
     */

    'passwords' => [
        // 'users'         => [
        //     'provider' => 'users',
        //     'table'    => 'password_resets',
        //     'expire'   => 15,
        // ],
        // 'bo_users'      => [
        //     'provider' => 'bo_users',
        //     'table'    => 'password_resets',
        //     'expire'   => 15,
        // ],
        // 'user_partners' => [
        //     'provider' => 'user_partners',
        //     'table'    => 'password_resets',
        //     'expire'   => 15,
        // ],
        // 'bo_customers'  => [
        //     'provider' => 'bo_customers',
        //     'table'    => 'password_resets',
        //     'expire'   => 15,
        // ],
        'public_user'  => [
            'provider' => 'public_user',
            'table'    => 'password_resets',
            'expire'   => 15,
        ],
        'public_user_v2'  => [
            'provider' => 'public_user_v2',
            'table'    => 'password_resets',
            'expire'   => 15,
        ],
    ],

    'types'     => [
        'public_user'  => App\Models\PublicUser\Users::class,
        'public_user_v2'  => App\Models\PublicUser\V2\User::class,
    ],
];
