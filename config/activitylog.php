<?php

return [
    /*
     * The database connection to use for the activity log table.
     */
    'database_connection' => env('ACTIVITY_LOG_DB_CONNECTION', null),

    /*
     * The table name to use for the activity log table.
     */
    'table_name' => 'activity_log',

    /*
     * The default log name to use when logging an activity.
     */
    'default_log_name' => 'default',
];








