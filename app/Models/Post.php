<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Post extends Model
{
    //
    protected $fillable = [
        'title',
        'subtitle',
        'content',
        'user_id',
        'images',
    ];

    protected $casts = [
        'images' => 'array', // Automatically casts JSON to array
    ];

    // Get the user who created the post.
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Get the likes for the post.
    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    // Get the comments for the post.
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    // Get the bookmarks for the post.
    public function bookmarks(){
        return $this->hasMany(Bookmark::class);
    }

    // get the categories
    public function categories(){
        return $this->belongsToMany(Category::class);
    }

    // Get the author of the post in the follow table.
    public function authorFollow(): HasOne
    {
        return $this->hasOne(Follow::class, 'followed_id', 'user_id');
    }
}
