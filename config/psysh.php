<?php

return [
    /*
    |--------------------------------------------------------------------------
    | PsySH History Path
    |--------------------------------------------------------------------------
    |
    | By default, PsySH will store your command history in the user's home
    | directory. You can override that path here, or set it to null if
    | you'd like to disable history persistence entirely.
    |
    */

    'history_path' => storage_path('psysh/psysh_history.php'),

    /*
    |--------------------------------------------------------------------------
    | PsySH Runtime Path
    |--------------------------------------------------------------------------
    |
    | This is the path where PsySH will store runtime files (e.g. temporary
    | code, cache files). If this is `null`, PsySH will try to find a
    | suitable path in the user's home directory.
    |
    */

    'runtime_path' => storage_path('psysh'),
];
