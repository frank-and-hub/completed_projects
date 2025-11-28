<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use App\Observers\PostObserver;
use App\Observers\CommentObserver;
use App\Models\Posts;
use App\Models\PostComments;
use App\Models\PostLikes;
use App\Observers\PostLikeObserver;
use App\Models\UserConnections;
use App\Observers\ConnectionObserver;
use App\Models\Followers;
use App\Models\ParentChildRelation;
use App\Observers\FollowerObserver;
use App\Observers\ParentChildRelationObserver;
use App\Models\Courses;
use App\Observers\CourseObserver;
use App\Models\CourseLikes;
use App\Observers\CourseLikeObserver;
use App\Observers\CourseCommentObserver;
use App\Models\CourseComment;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;

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
        Paginator::useBootstrap();

        View::composer('*', function ($view) {
            $view->with([
                'currentUserInfo'   => auth()->user(),
            ]);
        });

        RateLimiter::for('otp_limit', function (Request $request) {          
            return Limit::perMinute(40, 5)->by($request->ip())->response(function () {
                return response()->json([
                    'success' => false,
                    'message' => 'Too many OTP requests. Please try again later.'
                ], 429);
            });
        });
    }
}
