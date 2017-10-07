<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;
use View;
use App\Models\Category;
use App\Models\SubCategory;

class ComposerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $category = Category::orderBy('view_count', 'desc')->get();
        $sub_category = SubCategory::orderBy('view_count', 'desc')->get();
        View::share('category', $category);
        View::share('sub_category', $sub_category);
        View::composer(['auth.login','auth.register','auth.passwords.email','auth.passwords.reset','pages.details','pages.rooms'],'App\Http\ViewComposers\HeaderComposer');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
