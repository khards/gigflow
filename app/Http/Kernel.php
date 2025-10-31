<?php

namespace App\Http;

use App\Domains\Auth\Http\Middleware\AdminCheck;
use App\Domains\Auth\Http\Middleware\PasswordExpires;
use App\Domains\Auth\Http\Middleware\SuperAdminCheck;
use App\Domains\Auth\Http\Middleware\ToBeLoggedOut;
use App\Domains\Auth\Http\Middleware\TwoFactorAuthenticationStatus;
use App\Domains\Auth\Http\Middleware\UserCheck;
use App\Domains\Auth\Http\Middleware\UserTypeCheck;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\CheckForMaintenanceMode;
use App\Http\Middleware\EncryptCookies;
use App\Http\Middleware\LocaleMiddleware;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\TrimStrings;
use App\Http\Middleware\TrustProxies;
use App\Http\Middleware\VerifyCsrfToken;
use Fruitcake\Cors\HandleCors;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Http\Middleware\SetCacheHeaders;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Middleware\ValidateSignature;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Spatie\Permission\Middlewares\PermissionMiddleware;
use Spatie\Permission\Middlewares\RoleMiddleware;

/**
 * Class Kernel.
 */
class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        // \App\Http\Middleware\TrustHosts::class,
        TrustProxies::class,
        CheckForMaintenanceMode::class,
        ValidatePostSize::class,
        TrimStrings::class,
        ConvertEmptyStringsToNull::class,

        //Add CORS support
        HandleCors::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            LocaleMiddleware::class,
            SubstituteBindings::class,
            ToBeLoggedOut::class,

            // Enable Consuming Your API With JavaScript
            // https://laravel.com/docs/8.x/passport#consuming-your-api-with-javascript
            // This is great, but only works when logged in.
            //\Laravel\Passport\Http\Middleware\CreateFreshApiToken::class,
        ],

        'api' => [
            'throttle:api',
            SubstituteBindings::class,
        ],

        'admin' => [
            /*
             * This is configurable, disable boilerplate.access.user.admin_requires_2fa instead of removing this
             */
            '2fa:enabled',
            'auth',
            'password.expires',
            'is_admin',
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        '2fa' => TwoFactorAuthenticationStatus::class,
        'auth' => Authenticate::class,
        'auth.basic' => AuthenticateWithBasicAuth::class,
        'bindings' => SubstituteBindings::class,
        'cache.headers' => SetCacheHeaders::class,
        'can' => Authorize::class,
        'guest' => RedirectIfAuthenticated::class,
        'is_admin' => AdminCheck::class,
        'is_super_admin' => SuperAdminCheck::class,
        'is_user' => UserCheck::class,
        'password.confirm' => RequirePassword::class,
        'password.expires' => PasswordExpires::class,
        'permission' => PermissionMiddleware::class,
        'role' => RoleMiddleware::class,
        'signed' => ValidateSignature::class,
        'throttle' => ThrottleRequests::class,
        'type' => UserTypeCheck::class,
        'verified' => EnsureEmailIsVerified::class,
    ];

    /**
     * The priority-sorted list of middleware.
     *
     * This forces non-global middleware to always be in the given order.
     *
     * @var array
     */
    protected $middlewarePriority = [
        StartSession::class,
        ShareErrorsFromSession::class,
        Authenticate::class,
        ThrottleRequests::class,
        AuthenticateSession::class,
        SubstituteBindings::class,
        Authorize::class,
    ];
}
