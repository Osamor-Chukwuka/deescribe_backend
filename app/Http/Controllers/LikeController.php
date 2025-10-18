<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    //
    public function likePost(Request $request, Post $post)
    {
        // // Check if the user is trying to like their own post
        // if ($request->user()->id === $post->user_id) {
        //     return response()->json([
        //         'message' => 'You cannot like your own post.'
        //     ], 400);
        // }

        // Check if the user has already liked the post
        $alreadyLiked = $post->likes()->where('user_id', $request->user()->id)->exists();

        if ($alreadyLiked) {
            return response()->json([
                'message' => 'You have already liked this post.'
            ], 409);
        }

        // Create a new like for the post
        $post->likes()->create(['user_id' => $request->user()->id]);

        // Refresh the post to get the updated likes count
        $post->refresh();

        return response()->json([
            'message' => 'Post liked successfully.',
            'likes_count' => $post->likes()->count(),
            'status' => true,
            'post' => $post
        ], 201);
    }

    public function unlikePost(Request $request, Post $post)
    {
        // Check if the user has liked the post
        $like = $post->likes()->where('user_id', $request->user()->id)->first();

        if (!$like) {
            return response()->json([
                'message' => 'You have not liked this post.'
            ], 404);
        }

        // Delete the like
        $like->delete();

        // Refresh the post to get the updated likes count
        $post->refresh();

        return response()->json([
            'message' => 'Post unliked successfully.',
            'likes_count' => $post->likes()->count(),
            'status' => true,
            'post' => $post
        ]);
    }
}
