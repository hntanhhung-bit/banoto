<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: env('TRUSTED_PROXIES', '*'));

        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'partner' => \App\Http\Middleware\PartnerMiddleware::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'payment/momo/ipn',
            'payment/sepay/webhook',
            'ghn/webhook',
            'login',
            'register',
            'logout',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, \Illuminate\Http\Request $request) {
            return redirect()->back()->withInput($request->except('_token', 'password', 'password_confirmation'))
                ->with('error', 'Phiên làm việc đã hết hạn (Token CSRF). Vui lòng thử lại.');
        });
    })->create();
