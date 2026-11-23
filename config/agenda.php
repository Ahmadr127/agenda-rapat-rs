<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Agenda Public Scope
    |--------------------------------------------------------------------------
    |
    | Default scope agenda publik (internal = default). Domain internal /
    | eksternal dan filter host kini tidak digunakan.
    |
    */

    'default_scope' => env('AGENDA_DEFAULT_SCOPE', 'internal'),
];
