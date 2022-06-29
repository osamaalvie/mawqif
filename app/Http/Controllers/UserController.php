<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(Request $request)
    {
        //validate user via email and password
        $this->validate($request, [
            'email' => 'required',
            'password' => 'required'
        ]);

        //find user with email from database
        $user = User::where('email', $request->input('email'))->first();

        //check if password matches
        if (Hash::check($request->input('password'), $user->password)) {
            $api_token = base64_encode(Str::random(40));
            User::where('email', $request->input('email'))->update(['api_token' => $api_token]);
            return response()->json(['status' => 'success', 'api_token' => $api_token]);
        } else {
            return response()->json(['status' => 'fail'], 401);
        }
    }
}
