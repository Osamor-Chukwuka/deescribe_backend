<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'bio',
        'profile_image',
        'cover_image',
        'password',
    ];

    /**
     * The attributes that are defaults
     *
     * @var list<string>
     */

    protected $attributes = [
        'profile_image' => 'https://res.cloudinary.com/drmjq7src/image/upload/v1758702634/Deescribe/default_images/profile_vbvsji.png',
        'cover_image' => 'https://res.cloudinary.com/drmjq7src/image/upload/v1758702635/Deescribe/default_images/cover_fqnl4r.png',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Posts created by this user.
    // This method defines a one-to-many relationship between User and Post.
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    //  Users that this user is following.
    public function following()
    {
        return $this->belongsToMany(
            User::class,     // Related model
            'follows',       // Pivot table name
            'follower_id',   // FK on pivot pointing to this user
            'followed_id'    // FK on pivot pointing to the other user
        );
    }

    //  Users that are following this user.
    public function followers()
    {
        return $this->belongsToMany(
            User::class,
            'follows',
            'followed_id',
            'follower_id'
        );
    }


    // Likes given by this user.
    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    // Comments made by this user.
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    // Bookmarks created by this user.
    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class);
    }
}
