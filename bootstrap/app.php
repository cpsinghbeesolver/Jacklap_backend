<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
return Application::configure(basePath: dirname(__DIR__))
  ->withRouting(
        channels: __DIR__.'/../routes/channels.php',
    web: __DIR__ . '/../routes/web.php',
    commands: __DIR__ . '/../routes/console.php',
    health: '/up',
  )
  ->withMiddleware(function (Middleware $middleware) {
    $middleware->statefulApi();
    $middleware->alias([
      'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
      'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
      'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class
    ]);
    $middleware->encryptCookies(except: [
        'isLoggedIn',
        'userRole',
        'is_auth_incomplete',
    ]);
    $middleware->validateCsrfTokens(except: [
        'broadcasting/auth',
    ]);
  })
  ->withExceptions(function (Exceptions $exceptions) {
    //
  })->create();
