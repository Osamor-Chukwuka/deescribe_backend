<?php

namespace App\Http\Controllers;

use App\Models\Follow;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    //get user profile by id
    public function show(Request $request, User $user){
        return response()->json([
            'status' => true,
            'data' => [
                'user' => $user,
                'resources_count' => $user->loadCount(['posts', 'followers', 'following']),
                'is_following' => Follow::where('follower_id', $request->user()->id)->where('followed_id', $user->id)->exists(),
            ]        
        ]);
    }

    //update user
    public function update(Request $request){
        $user = $request->user();

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'bio' => 'sometimes|string',
            'profile_image' => 'sometimes|string',
            'cover_image' => 'sometimes|string'
        ]);

        $user->update($data);

        return response()->json([
            'status' => true,
            'user' => $user->fresh(),
            'message' => 'user updated successfully'
        ]);
    }
}
