<?php

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Support\Collection;
use Laravel\Dusk\Browser;
use Laravel\Dusk\TestCase as BaseTestCase;
use Livewire\Features\SupportTesting\DuskBrowserMacros;
use PHPUnit\Framework\Attributes\BeforeClass;
use Symfony\Component\Process\Process;

abstract class DuskTestCase extends BaseTestCase
{
    protected static ?Process $appServerProcess = null;

    /**
     * Prepare for Dusk test execution.
     */
    #[BeforeClass]
    public static function prepare(): void
    {
        static::forceDuskRuntimeEnvironment();

        if (! static::runningInSail()) {
            static::startChromeDriver(['--port=9515']);
        }

        // Raise default wait window for slower Windows/browser startup timing.
        Browser::$waitSeconds = (int) (env('DUSK_WAIT_SECONDS', 15));

        \Laravel\Dusk\Browser::mixin(new DuskBrowserMacros);

        static::startAppServerIfNeeded();
    }

    /**
     * Create the RemoteWebDriver instance.
     */
    protected function driver(): RemoteWebDriver
    {
        $runHeadless = filter_var(env('DUSK_HEADLESS', true), FILTER_VALIDATE_BOOL);

        $options = (new ChromeOptions)->addArguments(collect([
            $this->shouldStartMaximized() ? '--start-maximized' : '--window-size=1920,1080',
            '--disable-search-engine-choice-screen',
            '--disable-extensions',
            '--ignore-certificate-errors',
            '--allow-insecure-localhost',
            '--log-level=3',
            '--silent',
            '--disable-logging',
            '--disable-web-security',
        ])->when($runHeadless && ! $this->hasHeadlessDisabled(), function (Collection $items) {
            return $items->merge([
                '--disable-gpu',
                // '--headless=new',
            ]);
        })->all());

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? env('DUSK_DRIVER_URL') ?? 'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            )
        );
    }

    protected static function startAppServerIfNeeded(): void
    {
        $appUrl = env('APP_URL', 'http://127.0.0.1:8000');
        $parsed = parse_url($appUrl);
        $host = $parsed['host'] ?? '127.0.0.1';
        $port = (int) ($parsed['port'] ?? 8000);

        $isListening = @fsockopen($host, $port, $errno, $errstr, 1);
        if ($isListening) {
            fclose($isListening);
            return;
        }

        static::$appServerProcess = new Process(
            [PHP_BINARY, 'artisan', 'serve', "--host={$host}", "--port={$port}", '--env=dusk.local'],
            getcwd() ?: __DIR__.'/..'
        );
        static::$appServerProcess->disableOutput();
        static::$appServerProcess->start();

        usleep(1800000);

        register_shutdown_function(static function (): void {
            if (static::$appServerProcess instanceof Process && static::$appServerProcess->isRunning()) {
                static::$appServerProcess->stop(1);
            }
        });
    }

    protected static function forceDuskRuntimeEnvironment(): void
    {
        $overrides = [
            'APP_ENV' => 'testing',
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => '127.0.0.1',
            'DB_PORT' => '3306',
            'DB_DATABASE' => 'exam_app',
            'DB_USERNAME' => 'root',
            'DB_PASSWORD' => '',
            'SESSION_DRIVER' => 'file',
            'CACHE_STORE' => 'array',
            'QUEUE_CONNECTION' => 'sync',
        ];

        foreach ($overrides as $key => $value) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}
