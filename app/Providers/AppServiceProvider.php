<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL; // Idinagdag natin ito
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        Schema::defaultStringLength(191);

        // Eto ang mag-force ng HTTPS kapag nasa production (Render)
        if (env('APP_ENV') === 'production') {
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
