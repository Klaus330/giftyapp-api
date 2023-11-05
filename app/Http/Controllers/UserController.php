<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Repository\WishRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{

    public function __construct(private WishRepository $wishRepository)
    {
    }

    public function userClaimedWishes()
    {
        $userId = auth()->user()->id;
        $wishes = $this->wishRepository->findUserClaimedWishes($userId);

        return response()->json([
            'status' => 200,
            'data' => [
                'wishes' => $wishes,
            ],
            'error' => []
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'avatar' => 'file',
            'birth_date' => 'date'
        ]);
        $user = auth()->user();

        $dataToUpdate = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'birth_date' => new \DateTime($validated['birth_date'])
        ];

        if (isset($validated['avatar'])) {
            $filename = uniqid() . '.' . File::extension($validated['avatar']->getClientOriginalName());
            $success = Storage::disk('local')->put("images/{$filename}", $validated['avatar']);

            if ($success) {
                $dataToUpdate['avatar'] = $filename;
            }
        }

        $success = $user->update($dataToUpdate);

        return response()->json([
            "status" => 200,
            "data" => [
                'success' => $success
            ],
            "errors" => []
        ]);
    }
}
