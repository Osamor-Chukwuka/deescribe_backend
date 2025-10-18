<?php

namespace App\Http\Controllers;

use App\Models\Bookmark;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BookmarkController extends Controller
{
    //get bookmark
    public function index(Request $request)
    {
        $user = $request->user();
        $bookmarks = $user->bookmarks()
            ->with(['post' => function ($query) {
                $query->withCount('bookmarks'); // Count how many times the post is bookmarked
            }])
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Bookmarks retrieved successfully',
            'bookmarks' => $bookmarks
        ]);
    }

    //create bookmark
    public function store(Request $request, Post $post)
    {

        $user = $request->user();

        // Check if the post is already bookmarked
        if ($user->bookmarks()->where('post_id', $post->id)->exists()) {
            return response()->json([
                'status' => false,
                'message' => 'Post already bookmarked',
            ], 409);
        }

        // Create the bookmark
        $bookmark = $user->bookmarks()->create([
            'post_id' => $post->id,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Bookmark created successfully',
            'bookmark' => $bookmark
        ]);
    }

    //delete bookmark
    public function destroy(Request $request, Post $post)
    {   
        $user = $request->user();
        $bookmark = $user->bookmarks()->where('post_id', $post->id)->first();

        // Check if the user can delete the bookmark
        Gate::authorize('delete', $bookmark);
        
        $bookmark->delete();

        return response()->json([
            'status' => true,
            'message' => 'Bookmark deleted successfully',
        ]);
    }
}
