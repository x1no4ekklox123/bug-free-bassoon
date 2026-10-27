<?php

return [
    // date-fns pattern for the date part of key timestamps, the time is appended in code.
    'date_format' => 'MMM do, yyyy',
    'overview' => [
        'title' => 'Account Overview',
        'two_factor_required' => [
            'title' => '2-Factor Required',
            'message' => 'Your account must have two-factor authentication enabled in order to continue.',
        ],
    ],
    'password' => [
        'title' => 'Update Password',
        'current' => 'Current Password',
        'new' => 'New Password',
        'new_description' => 'Your new password should be at least 8 characters in length and unique to this website.',
        'confirm' => 'Confirm New Password',
        'submit' => 'Update Password',
        'validation' => [
            'current_min' => 'current must be at least 1 characters',
            'current_required' => 'You must provide your current password.',
            'new_min' => 'password must be at least 8 characters',
            'new_required' => 'password is a required field',
            'mismatch' => 'Password confirmation does not match the password you entered.',
        ],
    ],
    'email' => [
        'title' => 'Update Email Address',
        'email' => 'Email',
        'confirm_password' => 'Confirm Password',
        'submit' => 'Update Email',
        'updated' => 'Your primary email has been updated.',
        'validation' => [
            'email' => 'email must be a valid email',
            'email_required' => 'email is a required field',
            'password_required' => 'You must provide your current account password.',
        ],
    ],
    'language' => [
        'title' => 'Language',
        'label' => 'Interface Language',
        'description' => 'The language used for the Panel interface.',
        'save' => 'Update Language',
        'updated' => 'Your language has been updated.',
    ],
    'two_factor' => [
        'title' => 'Two-Step Verification',
        'enabled' => 'Two-step verification is currently enabled on your account.',
        'disabled' => 'You do not currently have two-step verification enabled on your account. Click the button below to begin configuring it.',
        'enable' => 'Enable Two-Step',
        'disable' => 'Disable Two-Step',
        'setup' => [
            'title' => 'Enable Two-Step Verification',
            'description' => "Help protect your account from unauthorized access. You'll be prompted for a verification code each time you sign in.",
            'instructions' => 'Scan the QR code above using the two-step authentication app of your choice. Then, enter the 6-digit code generated into the field below.',
            'password' => 'Account Password',
            'waiting' => 'Waiting for QR code to load...',
            'missing_fields' => 'You must enter the 6-digit code and your password to continue.',
            'submit' => 'Enable',
        ],
        'disable_dialog' => [
            'title' => 'Disable Two-Step Verification',
            'description' => 'Disabling two-step verification will make your account less secure.',
            'password_required' => 'You must enter your account password to continue.',
            'submit' => 'Disable',
        ],
        'recovery' => [
            'title' => 'Two-Step Authentication Enabled',
            'description' => 'Store the codes below somewhere safe. If you lose access to your phone you can use these backup codes to sign in.',
            'warning' => 'These codes will not be shown again.',
            'done' => 'Done',
        ],
    ],
    'api' => [
        'title' => 'Account API',
        'create_title' => 'Create API Key',
        'keys_title' => 'API Keys',
        'description_help' => 'A description of this API key.',
        'allowed_ips' => 'Allowed IPs',
        'allowed_ips_help' => 'Leave blank to allow any IP address to use this API key, otherwise provide each IP address on a new line.',
        'empty' => 'No API keys exist for this account.',
        'last_used' => 'Last used:',
        'delete' => [
            'title' => 'Delete API Key',
            'confirm' => 'Delete Key',
            'message' => 'All requests using the <code>:identifier</code> key will be invalidated.',
        ],
        'modal' => [
            'title' => 'Your API Key',
            'description' => 'The API key you have requested is shown below. Please store this in a safe location, it will not be shown again.',
        ],
        'validation' => [
            'description_required' => 'description is a required field',
            'description_min' => 'description must be at least 4 characters',
        ],
    ],
    'ssh' => [
        'title' => 'SSH Keys',
        'add_title' => 'Add SSH Key',
        'keys_title' => 'SSH Keys',
        'empty' => 'No SSH Keys exist for this account.',
        'added_on' => 'Added on:',
        'name' => 'SSH Key Name',
        'public_key' => 'Public Key',
        'public_key_help' => 'Enter your public SSH key.',
        'delete' => [
            'title' => 'Delete SSH Key',
            'confirm' => 'Delete Key',
            'message' => 'Removing the <code>:name</code> SSH key will invalidate its usage across the Panel.',
        ],
        'validation' => [
            'name_required' => 'name is a required field',
            'public_key_required' => 'publicKey is a required field',
        ],
    ],
];
