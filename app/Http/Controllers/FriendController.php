<?php

namespace App\Http\Controllers;

use App\Models\Friend;
use Illuminate\Http\Request;

class FriendController extends Controller
{
    public function saveFriend(Request $request): void
    {
       $validated = $request->validate([
            'owner_id' => 'integer|required',
            'friend_id' => 'integer|required',
        ]);

        $friendship1 = new Friend();
        $friendship1->owner_id = $validated['owner_id'];
        $friendship1->friend_id = $validated['friend_id'];

    }

    public function getFriendRequests(): void
    {

    }
}
