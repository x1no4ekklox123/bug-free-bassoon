<?php

/**
 * Client area shell: sidebar, mobile top bar and the names of the routes
 * listed in resources/scripts/routers/routes.ts.
 */
return [
    'sections' => [
        'main' => 'Main',
        'account' => 'Account',
        'server' => 'Server',
    ],
    'dashboard' => 'Dashboard',
    'search' => 'Search',
    'admin_panel' => 'Admin panel',
    'admin_server' => 'Manage in admin',
    'routes' => [
        'account' => [
            'overview' => 'Account',
            'api' => 'API Credentials',
            'ssh' => 'SSH Keys',
            'activity' => 'Activity',
        ],
        'server' => [
            'console' => 'Overview',
            'files' => 'Files',
            'databases' => 'Databases',
            'schedules' => 'Schedules',
            'users' => 'Users',
            'backups' => 'Backups',
            'network' => 'Network',
            'startup' => 'Startup',
            'settings' => 'Settings',
            'activity' => 'Activity',
        ],
    ],
    'selected_server' => 'Selected server',
    'switch_server' => 'Switch server',
    'logout' => 'Log out',
    'open_menu' => 'Open menu',
    'close_menu' => 'Close menu',
];
