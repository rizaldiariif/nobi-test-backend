<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function add(Request $request)
    {
        $body = $request->validate([
            'name' => 'required',
            'username' => 'required'
        ]);

        $existing_member = Member::where('username', $body['username']);

        if ($existing_member->first()) {
            return [
                'errors' => [
                    [
                        'username' => [
                            'Username is already taken'
                        ]
                    ]
                ]
            ];
        }

        $created_user = Member::create($body);

        return ['id' => $created_user->id];
    }
}
