<?php

return [

    /*
        * Whether or not a admin can change their email address after
        * their account has already been created
        */
    'can_change_email' => env('ADMIN_CHANGE_EMAIL', false),

    'default_timezone' => 'Asia/Manila',

    'role' => [

        /*
         * The name of the super admin role
         * Should be Super Admin by design and unable to change from the backend
         * It is not recommended to be changed
         */
        'super_admin' => 'Super Admin',
    ],
];
