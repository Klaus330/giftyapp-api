<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Wish extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'is_favorite',
        'is_claimed',
        'quantity',
        'price',
        'image',
        'link',
        'user_id',
        'claimer_id'
    ];

    public $with = ['claimer'];


    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function claimer()
    {
        return $this->belongsTo(User::class, 'claimer_id')->select('id', 'avatar');
    }

    public function isClaimed(): bool
    {
        return (bool)$this->is_claimed;
    }

    public function isFavorited(): bool
    {
        return (bool)$this->is_favorite;
    }

    public function isUnclaimable(int $userId): bool
    {
        return ($this->owner->id !== $userId && $this->claimer->id !== $userId) || !$this->isClaimed();
    }

    public function getImageAttribute($key)
    {
        if (empty($key)) {
            return $key;
        }
        return asset(Storage::url('images/' . $key));
    }

    public function isClaimable(int $userId): bool
    {
        return $this->owner->id === $userId || $this->isClaimed();
    }

    public function isFavouritable(int $userId): bool
    {
        return $this->owner->id === $userId;
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'wishes_categories')
            ->select(['category_id as id', 'name']);
    }
}
