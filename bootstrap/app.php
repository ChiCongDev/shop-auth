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
        $middleware->appendToGroup('web', \App\Http\Middleware\DongBoSessionDoiTac::class);

        $middleware->alias([
            'kiemTraDangNhap' => \App\Http\Middleware\KiemTraDangNhap::class,
            'kiemTraDoiTac' => \App\Http\Middleware\KiemTraDoiTacDangNhap::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();



    
