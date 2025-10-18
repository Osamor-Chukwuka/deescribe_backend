<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bookmark extends Model
{
    //
    protected $table = 'bookmarks';
    protected $fillable = [
        'user_id',
        'post_id',
    ];

    // Get the user who created the bookmark.
    public function user(){
        return $this->belongsTo(User::class);
    }

    // Get the post that the bookmark belongs to.
    public function post(){
        return $this->belongsTo(Post::class);
    }
}
