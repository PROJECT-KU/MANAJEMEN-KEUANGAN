<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use DB;
use App\Models\Todolist;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        View::composer('*', function ($view) {
            $user = Auth::user();
            $countAjukan = 0; // Default to 0

            if ($user) { // Check if user is authenticated
                $countAjukan = DB::table('perjalanan_dinas')
                    ->leftJoin('users', 'perjalanan_dinas.user_id', '=', 'users.id')
                    ->where('perjalanan_dinas.status', 'ajukan')
                    ->when($user->level == 'manager' || $user->level == 'ceo', function ($query) use ($user) {
                        return $query->where('users.company', $user->company);
                    }, function ($query) use ($user) {
                        return $query->where('perjalanan_dinas.user_id', $user->id);
                    })
                    ->count();
            }

            $view->with('countAjukan', $countAjukan);
        });

        View::composer('*', function ($view) {
            $user = Auth::user();
            $totalAssignTaskQuery = DB::table('todolist')->where('status', 'Assign Task');

            if ($user && $user->level !== 'manager') {
                $totalAssignTaskQuery->where(function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                        ->orWhere('user_id_kedua', $user->id);
                });
            }

            $totalAssignTask = $totalAssignTaskQuery->count();

            // Bagikan variabel ke semua view
            $view->with('totalAssignTask', $totalAssignTask);
        });
    }
}
