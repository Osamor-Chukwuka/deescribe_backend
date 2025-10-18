<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    //

    protected $table = 'comments';
    protected $fillable = [
        'content',
        'user_id',
        'post_id',
    ];

    // Get the user who created the comment.
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    
    // Get the post that the comment belongs to.
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
