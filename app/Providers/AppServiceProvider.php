<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // Notifikasi pemindahan ruang yang belum dibaca, ditampilkan sebagai pop-up di layout.
        View::composer('layouts.app', function ($view) {
            $unread = Auth::check()
                ? Auth::user()->roomNotifications()->where('is_read', false)->latest()->get()
                : collect();

            $view->with('unreadNotifications', $unread);
        });
    }
}
