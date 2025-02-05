<?php

return [
    'not_found' => [
        'name' => 'Handling of not found skins',
        'default_skin' => 'Return default skin',
        '404' => 'Return HTTP 404 (Not Found) error response',
    ],

    'skins' => 'Skins',
    'capes' => 'Capes',

    'enable_capes' => 'Enable capes (user must have a role with the permission to upload a cape)',

    'fields' => [
        'width' => 'Width',
        'height' => 'Height',
        'scale' => 'Max scale',
        'default' => 'Default skin',
    ],

    'api' => [
        'title' => 'API Information',
        'info' => 'You can find the Skin API documentation below.',
    ],

    'permissions' => [
        'cape' => 'Upload a cape',
        'manage' => 'Manage skins and capes settings',
    ],
];
