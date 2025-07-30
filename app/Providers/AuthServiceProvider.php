<?php

namespace App\Providers;

use App\User;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate as GateFacade;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any application authentication / authorization services.
     *
     * @param  GateContract $gate
     * @return void
     */
    public function boot(GateContract $gate)
    {
        $this->registerPolicies($gate);

        GateFacade::define('create-organization', function ($user) {
            return $user->hasRole('admin');
        });
        GateFacade::define('delete-organization', function ($user) {
            return $user->hasRole('admin');
        });
        GateFacade::define('access-organization', function ($user, $organizationId) {
            return $this->isAdvisorOrManagerHavingOrganization($user, $organizationId);
        });
        GateFacade::define('create-device', function ($user) {
            return $user->hasRole('admin');
        });
        GateFacade::define('delete-device', function ($user) {
            return $user->hasRole('admin');
        });
        GateFacade::define('access-order', function ($user) {
            return $user->hasRole('admin') || $user->hasRole('advisor');
        });
        GateFacade::define('access-devices', function ($user, $deviceable) {
            list(, $deviceableId) = \json_decode($deviceable);
            return $this->isAdvisorOrManagerHavingOrganization($user, $deviceableId);
        });
        GateFacade::define('access-device-attributes', function ($user, $deviceable) {
            list(, $deviceableId) = \json_decode($deviceable);
            return $this->isAdvisorOrManagerHavingOrganization($user, $deviceableId);
        });
        GateFacade::define('access-availability', function ($user, $deviceable) {
            return $this->isAdvisorOrManagerHavingOrganization($user, $deviceable);
        });
        GateFacade::define('create-availability', function ($user, $deviceable) {
            return $this->isManagerHavingOrganization($user, $deviceable);
        });
        GateFacade::define('access-organizationgroup', function (User $user, $organizationGroupId) {
            return $user->hasRole('admin');
        });
        GateFacade::define('create-organizationgroup', function (User $user, $organizationGroupId) {
            return $user->hasRole('admin');
        });
    }

    private function isManagerHavingOrganization(User $user, $organizationId): bool
    {
        return $user->hasRole('admin') || $user->hasRole('manager') && $user->hasOrganization((int)$organizationId);
    }

    private function isAdvisorOrManagerHavingOrganization(User $user, $organizationId): bool
    {
        return $user->hasRole('advisor') || $this->isManagerHavingOrganization($user, $organizationId);
    }
}
