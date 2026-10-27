<?php

/**
 * Server overview (console) page and the server state screens.
 */
return [
    'title' => 'Overview',
    'status' => [
        'running' => 'Online',
        'offline' => 'Offline',
        'starting' => 'Starting',
        'stopping' => 'Stopping',
    ],
    'uptime' => [
        'label' => 'Uptime',
        'units' => [
            'd' => 'd',
            'h' => 'h',
            'm' => 'm',
            's' => 's',
        ],
    ],
    'power' => [
        'start' => 'Start',
        'restart' => 'Restart',
        'stop' => 'Stop',
        'kill' => 'Kill',
        'kill_title' => 'Forcibly Stop Process',
        'kill_description' => 'Forcibly stopping a server can lead to data corruption.',
    ],
    'info' => [
        'connection' => 'Connection',
        'connection_hint' => 'Primary allocation',
        'no_allocation' => 'Not assigned',
        'sftp' => 'SFTP',
        'sftp_hint' => 'User :username',
        'node' => 'Node',
        'node_ok' => 'Operational',
        'node_maintenance' => 'Under maintenance',
        'limits' => 'Limits',
        'databases' => ':count DB',
        'backups_one' => ':count backup',
        'backups_other' => ':count backups',
        'ports_one' => ':count extra port',
        'ports_other' => ':count extra ports',
    ],
    'stats' => [
        'cpu' => 'CPU',
        'memory' => 'RAM',
        'disk' => 'Disk',
        'network' => 'Network',
        'limit' => 'Limit :limit',
        'per_second' => ':value/s',
    ],
    'console' => [
        'title' => 'Console',
        'connected' => 'Connected',
        'disconnected' => 'Disconnected',
        'connecting' => 'Waiting for connection to the server…',
        'placeholder' => 'Type a command...',
        'input_label' => 'Console command input',
        'status_changed' => 'Server marked as :state...',
        'states' => [
            'running' => 'running',
            'offline' => 'offline',
            'starting' => 'starting',
            'stopping' => 'stopping',
        ],
        'transfer_failed' => 'Transfer has failed.',
    ],
    'quick_actions' => [
        'title' => 'Quick actions',
        'files' => [
            'title' => 'Open files',
            'description' => 'Manage server files',
        ],
        'backup' => [
            'title' => 'Create a backup',
            'description' => 'Save the current state',
        ],
        'databases' => [
            'title' => 'Open databases',
            'description' => 'Credentials and connections',
        ],
        'network' => [
            'title' => 'Connection details',
            'description' => 'IP addresses and ports',
        ],
    ],
    'backups' => [
        'title' => 'Backups',
        'open' => 'Open',
        'empty' => 'No backups yet',
        'in_progress' => 'In progress',
        'failed' => 'Failed',
    ],
    'server_info' => [
        'title' => 'Server information',
        'id' => 'Server ID',
        'uuid' => 'UUID',
        'node' => 'Node',
        'image' => 'Docker image',
        'description' => 'Description',
    ],
    'alerts' => [
        'maintenance' => 'The node of this server is currently under maintenance and all actions are unavailable.',
        'installing' => 'This server is currently running its installation process and most actions are unavailable.',
        'transferring' => 'This server is currently being transferred to another node and all actions are unavailable.',
    ],
    'conflict' => [
        'installing' => [
            'title' => 'Running Installer',
            'message' => 'Your server should be ready soon, please try again in a few minutes.',
        ],
        'suspended' => [
            'title' => 'Server Suspended',
            'message' => 'This server is suspended and cannot be accessed.',
        ],
        'maintenance' => [
            'title' => 'Node under Maintenance',
            'message' => 'The node of this server is currently under maintenance.',
        ],
        'transferring' => [
            'title' => 'Transferring',
            'message' => 'Your server is being transferred to a new node, please check back later.',
        ],
        'restoring' => [
            'title' => 'Restoring from Backup',
            'message' => 'Your server is currently being restored from a backup, please check back in a few minutes.',
        ],
    ],
    'websocket' => [
        'connecting' => 'We\'re having some trouble connecting to your server, please wait...',
        'connect_failed' => 'Failed to connect to websocket instance after multiple attempts: try refreshing the page.',
        'jwt_error' => 'There was an error validating the credentials provided for the websocket. Please refresh the page.',
    ],
];
