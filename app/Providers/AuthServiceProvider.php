<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // App\Models\Order::class => App\Policies\OrderPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('is-admin-super', function($user) {
            return $user->role->name === 'super admin';
        });
        Gate::define('is-super-or-admin', function($user) {
            return $user->role->name === 'super admin' || $user->role->name === 'admin';
        });
    }
}
