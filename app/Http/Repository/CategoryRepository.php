<?php

declare(strict_types=1);

namespace App\Http\Repository;

use App\Models\Category;

class CategoryRepository
{
    public function findAll()
    {
        return Category::select(['id', 'name'])->get();
    }
}
