<?php

namespace App\Providers;
use App\Models\Foto;
use App\Observers\FotoObserver;
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
          Foto::observe(FotoObserver::class);
    }
}
