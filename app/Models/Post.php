<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'content',
        'is_anonymous',
        'is_featured',
        'is_hidden',
        'reported_at',
    ];

    protected function casts(): array
    {
        return [
            'is_anonymous' => 'boolean',
            'is_featured' => 'boolean',
            'is_hidden' => 'boolean',
            'reported_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)->withTimestamps();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(Reaction::class)->where('type', 'like');
    }

    public function stars(): HasMany
    {
        return $this->hasMany(Reaction::class)->where('type', 'star');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeBest($query)
    {
        return $query->withCount(['likes', 'stars', 'reactions'])
            ->orderByDesc('reactions_count');
    }

    public function scopeForUserCategories($query, User $user)
    {
        $categoryIds = $user->categories()->pluck('categories.id');

        return $query->whereHas('categories', function ($q) use ($categoryIds) {
            $q->whereIn('categories.id', $categoryIds);
        });
    }
}
