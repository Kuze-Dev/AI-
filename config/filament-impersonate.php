<?php
return [
    // This is the guard used when logging in as the impersonated user.
    'guard' => 'admin',

    // After impersonating this is where we'll redirect you to.
    'redirect_to' => 'admin',

    // We wire up a route for the "leave" button. You can change the middleware stack here if needed.
    'leave_middleware' => 'web',

    'banner' => [
        // Currently supports 'dark' and 'light'.
        'style' => 'dark',

        // Turn this off if you want `absolute` positioning, so the banner scrolls out of view
        'fixed' => true,

        // Currently supports 'top' and 'bottom'.
        'position' => 'bottom',
    ],
];
