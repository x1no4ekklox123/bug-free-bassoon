<?php

namespace Pterodactyl\Http\Controllers\Admin\Settings;

use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Illuminate\Contracts\Console\Kernel;
use Pterodactyl\Http\Controllers\Controller;
use Illuminate\Contracts\Encryption\Encrypter;
use Pterodactyl\Providers\SettingsServiceProvider;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Pterodactyl\Contracts\Repository\SettingsRepositoryInterface;
use Pterodactyl\Services\Databases\Backups\DatabaseBackupStorage;
use Pterodactyl\Http\Requests\Admin\Settings\DatabaseBackupSettingsFormRequest;

class DatabaseBackupsController extends Controller
{
    private const SECRET_KEY = 'pterodactyl:database_backups:s3:secret';

    public function __construct(
        private AlertsMessageBag $alert,
        private ConfigRepository $config,
        private Encrypter $encrypter,
        private Kernel $kernel,
        private SettingsRepositoryInterface $settings,
        private DatabaseBackupStorage $storage,
    ) {
    }

    /**
     * Render the database backup (S3) settings.
     */
    public function index(): View
    {
        return view('admin.settings.database-backups', [
            'enabled' => filter_var($this->config->get('pterodactyl.database_backups.enabled'), FILTER_VALIDATE_BOOL),
            'configured' => $this->storage->isConfigured(),
            'hasSecret' => !empty($this->config->get('pterodactyl.database_backups.s3.secret')),
        ]);
    }

    /**
     * @throws \Pterodactyl\Exceptions\Model\DataValidationException
     * @throws \Pterodactyl\Exceptions\Repository\RecordNotFoundException
     */
    public function update(DatabaseBackupSettingsFormRequest $request): RedirectResponse
    {
        $values = $request->normalize();

        // An empty secret keeps the stored one so it never has to be sent back to the browser.
        if (empty($values[self::SECRET_KEY])) {
            unset($values[self::SECRET_KEY]);

            if ($values['pterodactyl:database_backups:enabled'] === 'true' && empty($this->config->get('pterodactyl.database_backups.s3.secret'))) {
                return redirect()->route('admin.settings.database-backups')
                    ->withInput($request->except(self::SECRET_KEY))
                    ->withErrors([self::SECRET_KEY => 'A secret access key is required to enable database backups.']);
            }
        }

        foreach ($values as $key => $value) {
            if (in_array($key, SettingsServiceProvider::getEncryptedKeys()) && !empty($value)) {
                $value = $this->encrypter->encrypt($value);
            }

            $this->settings->set('settings::' . $key, $value ?? '');
        }

        $this->kernel->call('queue:restart');
        $this->alert->success('Database backup settings have been updated and the queue worker was restarted to apply these changes.')->flash();

        return redirect()->route('admin.settings.database-backups');
    }

    /**
     * Uploads and removes a small object using the saved settings.
     */
    public function test(): RedirectResponse
    {
        if (!$this->storage->isConfigured()) {
            $this->alert->danger('Save a bucket, access key and secret key before testing the connection.')->flash();

            return redirect()->route('admin.settings.database-backups');
        }

        try {
            $this->storage->test();
        } catch (\Exception $exception) {
            $this->alert->danger('Connection to the S3 bucket failed: ' . e($exception->getMessage()))->flash();

            return redirect()->route('admin.settings.database-backups');
        }

        $this->alert->success('Successfully wrote and removed a test object in the configured bucket.')->flash();

        return redirect()->route('admin.settings.database-backups');
    }
}
