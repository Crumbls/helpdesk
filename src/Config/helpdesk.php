<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Helpdesk Panel Path
    |--------------------------------------------------------------------------
    |
    | This is the URL path where your helpdesk panel will be accessible from.
    | By default, it is set to 'helpdesk', resulting in the panel being
    | accessible at {your-domain}/helpdesk
    |
    */
    'path' => 'helpdesk',

    /*
    |--------------------------------------------------------------------------
    | Livewire Components Path
    |--------------------------------------------------------------------------
    |
    | This is the path where your Livewire components for the frontend
    | will be located.
    |
    */
    'livewire' => [
        'path' => 'App\\Http\\Livewire\\Helpdesk',
        'namespace' => 'helpdesk',
    ],
];
