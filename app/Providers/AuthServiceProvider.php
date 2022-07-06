<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // set passport route to be inside API routes.
        Passport::routes(null, [
            'prefix' => config('app.api_version') . '/oauth',
        ]);

        Passport::tokensExpireIn(Carbon::now()->addDay());

        Passport::refreshTokensExpireIn(Carbon::now()->addDays(30));
    }
}
