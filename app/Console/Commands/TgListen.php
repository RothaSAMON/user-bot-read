<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Telegram\TgEventHandler;
use danog\MadelineProto\Settings;
use danog\MadelineProto\Settings\AppInfo;
use Illuminate\Console\Command;


class TgListen extends Command
{
    protected $signature = 'tg:listen';
    protected $description = 'Listen to Telegram updates via MadelineProto';

    public function handle(): int
    {
        // Use /tmp for session (supports UNIX sockets in Docker)
        $session = storage_path('madeline/session.madeline');

        if (!is_dir(dirname($session))) {
            mkdir(dirname($session), 0775, true);
        }

        $settings = new Settings();

        // Set app info
        $settings->setAppInfo(
            (new AppInfo())
                ->setApiId((int) env('TG_API_ID'))
                ->setApiHash(env('TG_API_HASH', 'hash'))
        );

        $this->info('Starting Telegram Event Handler...');
        $this->info('Session path: ' . $session);
        $this->info('Press Ctrl+C to stop');
        $this->newLine();

        TgEventHandler::startAndLoop($session, $settings);

        return self::SUCCESS;
    }
}