<?php

namespace App\Http\Controllers;

use App\Http\Repository\CategoryRepository;
use App\Http\Repository\WishRepository;
use App\Models\User;
use App\Models\Wish;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\PersonalAccessToken;

class WishController extends Controller
{


    public function __construct(private WishRepository $wishRepository, private CategoryRepository $categoryRepo)
    {
    }

    public function saveWish(Request $request, int $userId)
    {
        $validated = $request->validate([
            'name' => 'required',
            'price' => 'required|integer',
            'link' => 'required|string',
            'quantity' => 'required|integer',
            'is_favorite' => 'boolean',
            'categories' => "array",
            'image' => 'file'
        ]);

        $wish = $this->wishRepository->save([
            'user_id' => $userId,
            ...$validated
        ]);

        return response()->json([
            "status" => 200,
            "data" => [
                "message" => "Your wish has been created successfully",
                "data" => $wish
            ],
            "errors" => []
        ]);
    }

    public function wishes(Request $request, int $userId)
    {
        $sorters = [];
        $filters = [];

        if ($request->query->has('sort_by')) {
            $sorters['sorters'] = $request->query->get('sort_by');
            $sorters['type'] = $request->query->get('sort_type');
        }

        if ($request->query->has('category')) {
            $filters['category'] = $request->query->get('category');
        }

        $wishes = $this->wishRepository->findWishesFor($userId, $filters, $sorters);
        $token = trim($request->bearerToken(), '\"');
        $accessToken = PersonalAccessToken::findToken($token);

        return response()->json([
            'status' => 200,
            'data' => [
                'wishes' => $wishes,
                'categories' => $this->categoryRepo->findAll(),
                'is_owner' => $accessToken !== null && $accessToken->tokenable(),
                'owner' => User::find($userId)
            ],
            'error' => []
        ]);
    }

    public function deleteWish(Request $request, int $userId)
    {
        $request->validate([
            'wish_id' => 'required|integer'
        ]);

        try {
            $wish = Wish::where('id', $request->get('wish_id', 0))->first();

            $wish->delete();
        } catch (\Exception $e) {
            return response()->json([
                "status" => 500,
                "data" => [
                    "message" => "Something went wrong"
                ],
                "errors" => []
            ]);
        }

        return response()->json([
            "status" => 200,
            "data" => [
                "message" => "Your wish has been deleted successfully"
            ],
            "errors" => []
        ]);
    }

    public function claimWish(Request $request, int $userId)
    {
        $validated = $request->validate([
            'wish_id' => 'required|integer',
        ]);

        try {
            $this->wishRepository->claim($validated['wish_id'], auth('sanctum')->user()->id, $userId);
        } catch (\Exception $e) {
            return response()->json([
                "status" => 500,
                "data" => [
                    "message" => $e->getMessage()
                ],
                "errors" => []
            ]);
        }

        return response()->json([
            "status" => 200,
            "data" => [
                "message" => "Your wish has been claimed successfully"
            ],
            "errors" => []
        ]);
    }

    public function unclaimWish(Request $request, int $userId)
    {
        $validated = $request->validate([
            'wish_id' => 'required|integer',
        ]);

        try {
            $this->wishRepository->unclaim($validated['wish_id'], auth('sanctum')->user()->id, $userId);
        } catch (\Exception $e) {
            return response()->json([
                "status" => 500,
                "data" => [
                    "message" => $e->getMessage()
                ],
                "errors" => []
            ]);
        }

        return response()->json([
            "status" => 200,
            "data" => [
                "message" => "Your wish has been unclaimed successfully"
            ],
            "errors" => []
        ]);
    }

    public function toggleFavorite(Request $request, int $userId)
    {
        $validated = $request->validate([
            'wish_id' => 'required|integer',
        ]);

        try {
            $this->wishRepository->toggleFavorite($validated['wish_id'], $userId);
        } catch (\Exception $e) {
            return response()->json([
                "status" => 500,
                "data" => [
                    "message" => $e->getMessage()
                ],
                "errors" => []
            ]);
        }

        return response()->json([
            "status" => 200,
            "data" => [
                "message" => "Your wish has been favorited successfully"
            ],
            "errors" => []
        ]);
    }


    public function update(Request $request, int $userId)
    {
        $validated = $request->validate([
            'wish_id' => 'required|integer',
            'name' => 'required|string',
            'is_favorite' => "required",
            'quantity' => "required|integer",
            'link' => "required",
            'price' => "required|integer",
            'image' => 'file'
        ]);

        try {
            $this->wishRepository->toggleFavorite($validated['wish_id'], $userId, $validated);
        } catch (\Exception $e) {
            return response()->json([
                "status" => 500,
                "data" => [
                    "message" => $e->getMessage()
                ],
                "errors" => []
            ]);
        }

        return response()->json([
            "status" => 200,
            "data" => [
                "message" => "Your wish has been favorited successfully",
            ],
            "errors" => []
        ]);
    }

    public function getWishImagePath(string $path)
    {
        $image = Storage::get($path);

        return response($image, 200)
               ->header("Content-Type", Storage::mimeType($path));
    }
}
