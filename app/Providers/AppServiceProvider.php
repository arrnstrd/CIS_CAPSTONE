<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL; // Idinagdag natin ito
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('qr-scan', function (Request $request) {
            $identity = $request->user()?->getAuthIdentifier() ?? $request->ip();
            $device = (string) $request->input('device_id', 'unknown');

            return Limit::perMinute(120)->by($identity . '|' . $device);
        });

        RateLimiter::for('password-reset', function (Request $request) {
            $email = (string) $request->input('email');

            return [
                Limit::perHour(5)->by($request->ip()),
                Limit::perHour(5)->by($email),
            ];
        });

        Paginator::useBootstrapFive();

        Schema::defaultStringLength(191);

        Blade::anonymousComponentPath(resource_path('views/shared/components'), 'shared');
        Blade::anonymousComponentPath(resource_path('views/shared/components'), 'ui');

        // Automatically map <x-ui.*> and <x-shared.*> dot-notation calls to shared/components
        foreach (glob(resource_path('views/shared/components/*.blade.php')) as $componentFile) {
            $componentName = basename($componentFile, '.blade.php');
            Blade::component('shared.components.' . $componentName, 'ui.' . $componentName);
            Blade::component('shared.components.' . $componentName, 'shared.' . $componentName);
        }

        // Force HTTPS in production or when behind Render/reverse proxy terminating SSL
        if (app()->environment('production') || config('app.env') === 'production' || request()->header('x-forwarded-proto') === 'https') {
            URL::forceScheme('https');
        }


        // TEMP: GLOBAL REQUEST PROFILE — remove after diagnosis.
        // Profiles the authenticated pages under investigation + the scan POST + /speed-test baseline.
        if (!app()->runningInConsole()) {
            $path = request()->path();
            $profileTargets = ['speed-test', 'student-management', 'entry-exit', 'qr-station', 'scan'];
            $shouldProfile = collect($profileTargets)->contains(
                fn($p) => str_starts_with($path, $p) || str_starts_with($path, 'api/' . $p)
            );

            if ($shouldProfile) {
                $profile = ['start' => microtime(true), 'renderStart' => null, 'queries' => []];
                \Illuminate\Support\Facades\DB::listen(function ($query) use (&$profile) {
                    $profile['queries'][] = ['sql' => $query->sql, 'time' => round($query->time, 2)];
                });
                \Illuminate\Support\Facades\Event::listen('composing:*', function () use (&$profile) {
                    if ($profile['renderStart'] === null) {
                        $profile['renderStart'] = microtime(true);
                    }
                });
                app()->terminating(function () use (&$profile) {
                    $total = round((microtime(true) - $profile['start']) * 1000, 1);
                    $dbMs = round(array_sum(array_column($profile['queries'], 'time')), 1);
                    $preRender = $profile['renderStart'] !== null
                        ? round(($profile['renderStart'] - $profile['start']) * 1000, 1)
                        : null;
                    $render = $profile['renderStart'] !== null
                        ? round((microtime(true) - $profile['renderStart']) * 1000, 1)
                        : null;
                    $out = '[PROFILE-GLOBAL] ' . request()->method() . ' /' . request()->path()
                        . ' total=' . $total . 'ms dbQueries=' . count($profile['queries'])
                        . ' dbMs=' . $dbMs . 'ms preRender=' . ($preRender ?? '?')
                        . 'ms render=' . ($render ?? '?') . 'ms';
                    foreach ($profile['queries'] as $q) {
                        $out .= "\n  [" . $q['time'] . 'ms] ' . $q['sql'];
                    }
                    \Illuminate\Support\Facades\Log::debug($out);
                });
            }
        }
    }
}


