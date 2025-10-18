<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    //follow a user
    public function follow(Request $request, User $user)
    {
        // check if user wants to follow themselves
        if ($request->user()->id === $user->id) {
            return response()->json([
                'message' => 'You cannot follow yourself.'
            ], 400);
        }

        // check if user is already following
        $alreadyFollowing = $request->user()->following()->where('users.id', $user->id)->exists();

        if ($alreadyFollowing) {
            return response()->json([
                'message' => 'Already following this user.'
            ], 409);
        }

        // follow the user
        $request->user()->following()->attach($user->id);

        return response()->json([
            'status' => true,
            'followers_count' => $user->followers()->count(),
            'following_count' => $user->following()->count(),
            'is_following' => true,
            'user' => $user
        ]);
    }


    // unfollow a user
    public function unfollow(Request $request, User $user)
    {
        // check if user wants to unfollow themselves
        if ($request->user()->id === $user->id) {
            return response()->json([
                'message' => 'You cannot unfollow yourself.'
            ], 400);
        }

        // check if user is actually following
        $isFollowing = $request->user()->following()->where('users.id', $user->id)->exists();

        if (!$isFollowing) {
            return response()->json([
                'message' => 'You are not following this user.'
            ], 404);
        }

        // unfollow the user
        $request->user()->following()->detach($user->id);


        return response()->json([
            'status' => true,
            'message' => "You have unfollowed the user",
            'followers_count' => $user->followers()->count(),
            'following_count' => $user->following()->count(),
            'is_following' => false,
            'user' => $user
        ]);
    }

    // get following and followers count
    public function followingCount(Request $request, User $user)
    {

        return response()->json([
            'followers_count' => $user->followers()->count(),
            'following_count' => $user->following()->count(),
            'status' => true,
        ], 200);
    }

    // get followers of this user
    public function getFollowers(Request $request, User $user)
    {
        $followers = $user->followers()->get();

        return response()->json([
            'message' => 'User followers list retrieved successfully.',
            'followers' => $followers,
            'followers_count' => $user->followers()->count(),
            'status' => true,
        ], 200);
    }

    // get users that this user is following
    public function getFollowing(Request $request, User $user)
    {
        $following = $user->following()->get();

        return response()->json([
            'message' => 'User following list retrieved successfully.',
            'following' => $following,
            'following_count' => $user->following()->count(),
            'status' => true,
        ], 200);
    }

    //get users this user is not following
    public function notFollowing(Request $request)
    {
        $me = $request->user();
        $followingIds = $me->following()->pluck('users.id')->toArray();

        $notFollowing = User::where('id', '!=', $me->id)
            ->whereNotIn('id', $followingIds)->withCount('followers')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $notFollowing,
        ]);
    }
}
