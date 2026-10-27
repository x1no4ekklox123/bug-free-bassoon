<?php

/**
 * Dashboard: server list, server cards and the server search modal.
 */
return [
    'title' => 'Servers',
    'count_one' => ':count server',
    'count_other' => ':count servers',
    'showing_own' => 'Showing your servers',
    'showing_others' => 'Showing others\' servers',
    'empty' => 'There are no servers associated with your account.',
    'empty_others' => 'There are no other servers to display.',
    'server' => [
        'cpu' => 'CPU',
        'memory' => 'RAM',
        'disk' => 'Disk',
        'of' => 'of :limit',
        'no_allocation' => 'No allocation',
        'status' => [
            'running' => 'Online',
            'offline' => 'Offline',
            'starting' => 'Starting',
            'stopping' => 'Stopping',
            'suspended' => 'Suspended',
            'connection_error' => 'Connection error',
            'maintenance' => 'Under maintenance',
            'transferring' => 'Transferring',
            'installing' => 'Installing',
            'restoring_backup' => 'Restoring backup',
            'unavailable' => 'Unavailable',
            'loading' => 'Loading',
        ],
    ],
    'search' => [
        'title' => 'Search servers',
        'label' => 'Search term',
        'description' => 'Enter a server name, uuid, or allocation to begin searching.',
        'min_length' => 'Please enter at least three characters to begin searching.',
        'no_results' => 'No servers match your search.',
    ],
];
