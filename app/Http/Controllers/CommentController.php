<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    //view comments on a post
    public function index(Post $post)
    {
        $comments = $post->comments()->with('user')->latest()->get();

        return response()->json([
            'message' => 'Comments retrieved successfully.',
            'comments' => $comments,
            'comments_count' => $post->comments()->count(),
            'status' => true,
        ], 200);
    }


    // create a comment on a post
    public function store(Request $request, Post $post)
    {

        //validate the request
        $data = $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        // creare the comment
        $comment = $post->comments()->create([
            'content' => $data["content"],
            'user_id' => $request->user()->id,
        ]);

        // refresh the post to get the updated comments count
        $post->refresh();

        return response()->json([
            'message' => 'Comment added successfully.',
            // 'comment' => $comment,
            'comments_count' => $post->comments()->count(),
            'status' => true,
        ], 201);
    }


    // delete a comment
    public function destroy(Request $request, Post $post,  Comment $comment)
    {

        //check if the user is authorized to delete the comment
        Gate::authorize('delete', $comment);

        // delete the comment
        $comment->delete();

        // refresh the post to get the updated comments count
        $post->refresh();

        return response()->json([
            'message' => 'Comment deleted successfully.',
            'comments_count' => $post->comments()->count(),
            'status' => true
        ], 200);
    }
}
