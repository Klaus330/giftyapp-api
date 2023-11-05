<?php

declare(strict_types=1);

namespace App\Http\Repository;

use App\Models\Category;
use App\Models\User;
use App\Models\Wish;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class WishRepository
{

    /**
     * @throws \Exception
     */
    public function claim(int $wishId, int $claimerId, int $userId): bool
    {
        $wish = $this->findWishBy(['id' => $wishId, 'user_id' => $userId]);

        if ($wish->isClaimable($claimerId)) {
            throw new \Exception('Unauthorized action');
        }

        return $wish->update([
            'is_claimed' => true,
            'claimer_id' => $claimerId
        ]);
    }

    /**
     * @throws \Exception
     */
    public function unclaim(int $wishId, int $authUserId, int $userId): bool
    {
        $wish = $this->findWishBy(['id' => $wishId, 'user_id' => $userId]);

        if ($wish->isUnclaimable($authUserId)) {
            throw new \Exception('Unauthorized action');
        }

        return $wish->update([
            'is_claimed' => false,
            'claimer_id' => null
        ]);
    }

    /**
     * @throws \Exception
     */
    public function toggleFavorite(int $wishId, int $userId): bool
    {
        $wish = $this->findWishBy(['id' => $wishId, 'user_id' => $userId]);

        if (!$wish->isFavouritable(auth()->user()->id)) {
            throw new \Exception('Unauthorized action');
        }

        if ($wish->isFavorited()) {
            return $wish->update([
                'is_favorite' => false,
            ]);
        }

        return $wish->update([
            'is_favorite' => true,
        ]);
    }

    public function findWishBy($criteria): Wish
    {
        return Wish::where($criteria)->firstOrFail();
    }

    public function findWishesFor(int $userId, array $filters = [], array $sorters = [])
    {
        $query = Wish::where('user_id', $userId)
            ->with('categories');

        if (!empty($filters)) {
            $query = Category::where('id', $filters['category'])->first()->wishes();
        }


        if (!empty($sorters)) {
            $query->orderBy($sorters['sorters'], $sorters['type'] ?? "asc");
        } else {
            $query
                ->orderBy('is_favorite', 'DESC')
                ->orderBy('is_claimed');
        }

        return $query
            ->paginate(10);
    }

    /**
     * @throws \Exception
     */
    public function update(int $wishId, int $userId, array $data): bool
    {
        $wish = $this->findWishBy(['id' => $wishId, 'user_id' => $userId]);

        if (!$wish->owner->id === auth()->user()->id) {
            throw new \Exception('Unauthorized action');
        }

        $dataToUpdate = [
            'name' => $data['name'],
            'is_favorite' => $data['is_favorite'],
            'quantity' => $data['quantity'],
            'link' => $data['link'],
            'price' => $data['price'],
        ];

        if (isset($data['image'])) {
            Storage::delete('images/' . $wish->image);
            $filename = uniqid() . '.' . File::extension($data['image']->getClientOriginalName());
            $success = Storage::disk('local')->put("images/{$filename}", $data['image']);

            if ($success) {
                $dataToUpdate['image'] = $filename;
            }
        }

        return $wish->update($dataToUpdate);
    }

    public function save(array $data)
    {
        $wish = Wish::create([
            'name' => $data['name'],
            'user_id' => $data['user_id'],
            'is_favorite' => $data['is_favorite'] ?? false,
            'link' => $data['link'],
            'quantity' => $data['quantity'],
            'price' => $data['price'],
        ]);

        if (isset($data['categories'])) {
            $wish->categories()->attach($data['categories']);
        }

        if (isset($data['image'])) {
            $filename = uniqid() . '.' . File::extension($data['image']->getClientOriginalName());
            $success = Storage::disk('local')->put("images/{$filename}", $data['image']);

            if ($success) {
                $wish->image = $filename;
                $wish->save();
            }
        }

        return $wish;
    }

    public function findUserClaimedWishes(int $userId)
    {
        return Wish::where('claimer_id', $userId)->paginate(10);
    }
}
