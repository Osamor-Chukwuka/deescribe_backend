<?php

use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\LookupController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//show api status
Route::get('/', function () {
    return response()->json(['status' => 'API is running']);
});

Route::post('/login', [SessionController::class, 'login']);
Route::post('/register', [SessionController::class, 'register']);
Route::post('/forgot-password', [SessionController::class, 'forgotPassword']);
Route::post('/reset-password', [SessionController::class, 'resetPassword']);

//protected Routes
Route::middleware('auth:sanctum')->group(function () {

    // get authenticated user
    Route::get('/user', function (Request $request) {
        return response()->json($request->user()->loadCount(['posts', 'followers', 'following']));
    });


    // Post Routes
    Route::controller(PostController::class)->group(function () {
        // create new post
        Route::post('/post', 'store');

        // get all posts
        Route::get('/posts', 'index');

        // get post by id
        Route::get('/posts/{postId}', 'show');

        // get user posts
        Route::get('/user/posts/{userId}', 'myPosts');

        // update post
        Route::put('/posts/{post}', 'update');

        // delete post
        Route::delete('/posts/{postId}', 'destroy');

        // get user feed
        Route::get('/feed', 'feed');
    });


    // Follow Routes
    Route::controller(FollowController::class)->group(function () {
        // follow a user
        Route::post('/follow/{user}', 'follow');

        // unfollow a user
        Route::delete('/follow/{user}', 'unfollow');

        // get user followers and following count
        Route::get('/user/{user}/following/count', 'followingCount');

        // get users followers
        Route::get('/user/{user}/followers', 'getFollowers');

        // get users following
        Route::get('/user/{user}/following', 'getFollowing');

        // get users i am not following
        Route::get('/users/not-following', 'notFollowing');
    });

    // User Routes
    Route::controller(UserController::class)->group(function () {
        // get user profile by id
        Route::get('/users/{user}', 'show');

        //update user
        Route::put('/user/update', 'update');
    });


    // Like Routes
    Route::controller(LikeController::class)->group(function () {
        // like a post
        Route::post('/like/{post}', 'likePost');

        // unlike a post
        Route::delete('/like/{post}', 'unlikePost');
    });


    // Comment Routes
    Route::controller(CommentController::class)->group(function () {
        // view comments on a post
        Route::get('/posts/{post}/comments', 'index');

        // create a comment on a post
        Route::post('/posts/{post}/comment', 'store');

        // delete a comment
        Route::delete('/posts/{post}/comments/{comment}', 'destroy');
    });

    // Bookmark Routes
    Route::controller(BookmarkController::class)->group(function () {
        // view bookmarked posts of a user
        Route::get('/bookmarks', 'index');

        // create a bookmark
        Route::post('/bookmark/{post}', 'store');

        // delete a bookmark
        Route::delete('/bookmark/{post}', 'destroy');
    });

    // Lookups Routes
    Route::controller(LookupController::class)->group(function () {
        // get categories
        Route::get('/lookups/categories', 'categories');
    });
});
