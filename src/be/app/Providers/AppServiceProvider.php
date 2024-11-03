<?php

namespace App\Providers;

use App\Http\Controllers\Tasks\Repositories\TaskRepository;
use App\Http\Controllers\Tasks\Repositories\TaskRepositoryInterface;
use App\Http\Controllers\Tasks\Services\TaskService;
use App\Http\Controllers\Tasks\Services\TaskServiceInterface;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\Users\Repository\{
    UserRepository,UserRepositoryInterface
};
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(TaskRepositoryInterface::class, TaskRepository::class);
        $this->app->bind(TaskServiceInterface::class, TaskService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->environment('local')) {
            $this->app->bind('seed.testing', function () {
                Artisan::call('migrate:fresh --seed');
            });
        }
    }
}
