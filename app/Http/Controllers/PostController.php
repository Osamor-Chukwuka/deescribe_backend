<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;

class PostController extends Controller
{
    // create new post
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'subtitle' => 'required|string',
            'content' => 'required|string',
            'images' => 'nullable|array',
            'images.*' => 'string',
            'categories' => 'required|array|exists:categories,id',
        ]);
        $data['user_id'] = $request->user()->id;


        // create post
        $post = Post::create([
            'title' => $data['title'],
            'subtitle' => $data['subtitle'],
            'content' => $data['content'],
            'user_id' => $data['user_id'],
            'images' => $data['images'],
        ]);

        // attach categories to post
        $post->categories()->attach($data['categories']);

        return response()->json([
            'message' => 'Post created successfully',
            'post' => $post
        ]);
    }


    // view all posts
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $searchTerm = $request->query('searchterm');
        $category = $request->query('category');

        $query = Post::with(['user', 'categories'])->withCount(['likes', 'comments'])->withExists([
            'likes as liked' => function ($q) use ($userId) {
                $q->where('user_id', $userId);
            },
            'authorFollow as following' => fn($q) => $q->where('follower_id', $userId),
        ])->latest();


        if ($searchTerm && $searchTerm !== '' && $searchTerm !== 'null') {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                    ->orWhere('content', 'like', "%{$searchTerm}%");
            });
        }

        if ($category && $category !== 'all') {
            $query->whereHas('categories', function ($q) use ($category) {
                $q->where('id', $category);
            });
        }

        $posts = $query->get();

        // Cast the numeric liked column and others to boolean (optional)
        $posts->transform(function ($post) {
            $post->liked = (bool) $post->liked;
            $post->following = (bool) $post->following;
            return $post;
        });

        return response()->json([
            'status' => true,
            'posts' => $posts,
        ]);
    }

    // view single post
    public function show($id)
    {
        $userId = request()->user()->id;

        $post = Post::with(['user', 'categories'])->withCount('likes', 'comments', 'bookmarks')->withExists(
            [
                'likes as liked' => function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                },

                'bookmarks as bookmarked' => function ($q) use ($userId, $id) {
                    $q->where('post_id', $id)->where('user_id', $userId);
                },

                'authorFollow as following' => fn($q) => $q->where('follower_id', $userId)
            ]
        )->findOrFail($id);

        // Cast the numeric liked column and others to boolean (optional)
        $post->liked = (bool) $post->liked;
        $post->following = (bool) $post->following;

        return response()->json([
            'message' => 'Post details',
            'post' => $post,
        ]);
    }

    //view your posts
    public function myPosts(Request $request, $id)
    {
        $userId = request()->user()->id;
        $sort = $request->query('sort');

        $data = Post::where('user_id', $id)->withCount(['likes', 'comments'])->withExists(['likes as liked' => function ($q) use ($userId) {
            $q->where('user_id', $userId);
        }])->with('categories');

        if($sort == 'likes'){
            $data->orderBy('likes_count', 'desc')->limit(3); // highest likes first
        }else{
            $data->latest();
        }

        $posts = $data->get();

        // Cast the numeric liked column to boolean (optional)
        $posts->transform(function ($post) {
            $post->liked = (bool) $post->liked;
            return $post;
        });

        return response()->json([
            'status' => true,
            'message' => 'Your posts',
            'posts' => $posts,
        ]);
    }

    // update post
    public function update(Request $request, Post $post)
    {
        //check if user is authorized to update the post
        Gate::authorize('update', $post);

        $data = $request->validate([
            'title' => 'required|string',
            'subtitle' => 'required|string',
            'content' => 'required|string',
            'images' => 'nullable|array',
            'images.*' => 'string',
        ]);

        // update post
        $post->update($data);

        $post = $post->load('user')->loadCount(['likes', 'comments']);

        return response()->json([
            'message' => 'Post updated successfully',
            'post' => $post,
        ]);
    }

    // delete post
    public function destroy(Post $postId)
    {
        //check if user is authorized to delete the post
        Gate::authorize('delete', $postId);

        $post = Post::findOrFail($postId->id);

        // delete images
        if ($post->images && is_array($post->images)) {
            foreach ($post->images as $imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
        }

        $post->delete();

        return response()->json([
            'status' => true,
            'message' => 'Post deleted successfully',
        ]);
    }


    // return user feed
    public function feed(Request $request)
    {
        $user = $request->user();
        $category = $request->query('category');

        $userId = $user->id;

        $followedIds = $user->following()->pluck('users.id')->toArray();

        $query = Post::with(['user', 'categories'])->whereIn('user_id', $followedIds)->withCount(['likes', 'comments'])->withExists([
            'likes as liked' => function ($q) use ($userId) {
                $q->where('user_id', $userId);
            },
            'authorFollow as following' => fn($q) => $q->where('follower_id', $userId),
        ])->latest();

        if ($category && $category !== 'all') {
            $query->whereHas('categories', function ($q) use ($category) {
                $q->where('id', $category);
            });
        }

        $posts = $query->get();

        // Cast the numeric liked column and others to boolean (optional)
        $posts->transform(function ($post) {
            $post->liked = (bool) $post->liked;
            $post->following = (bool) $post->following;
            return $post;
        });

        return response()->json([
            'message' => 'User feed',
            'status' => true,
            'posts' => $posts,
        ]);
    }
}
