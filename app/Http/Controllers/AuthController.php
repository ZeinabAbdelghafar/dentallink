<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Verification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationEmail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    protected function createToken($user)
    {
        return JWTAuth::fromUser($user, [
            'id' => $user->id,
            'email' => $user->email,
            'role' => $user->role,
            'username' => $user->username,
            'verified' => $user->verified,
        ]);
    }

    public function signup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users',
            'username' => 'required|min:3|unique:users|alpha_num|regex:/^\S*$/u',
            'password' => 'required|min:6',
            'gender' => 'required|in:male,female',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $user = User::create([
            'email' => $request->email,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'gender' => $request->gender,
            'role' => $request->role ?? 'user',
            'verified' => false,
        ]);

        $uniqueString = Str::random(40);
        $expireAt = now()->addHours(6);

        Verification::create([
            'user_id' => $user->id,
            'unique_string' => $uniqueString,
            'expire_at' => $expireAt,
        ]);

        $verificationUrl = URL::to('/verify/' . $user->id . '/' . $uniqueString);

        Mail::to($user->email)->send(new VerificationEmail(
            $user,
            'Account Verification',
            'Please Verify your account',
            $verificationUrl
        ));

        $token = $this->createToken($user);

        return response()->json([
            'token' => $token,
            'msg' => $user->username . ' Registered Successfully, A Verification Email Sent to your inbox',
        ], 200);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        try {
            $user = User::login($request->email, $request->password);
            $token = $this->createToken($user);

            return response()->json([
                'token' => $token,
                'msg' => 'Login Success',
            ], 200);
        } catch (\Exception $error) {
            return response()->json(['Error' => $error->getMessage()], 400);
        }
    }

    public function verify($id, $uuid)
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return response()->json(['Status' => 400, 'Error' => 'Invalid Link'], 400);
            }

            $verification = Verification::where('user_id', $user->id)
                ->where('unique_string', $uuid)
                ->first();

            if (!$verification) {
                return response()->json(['Status' => 400, 'Error' => 'Invalid Link'], 400);
            }

            if (now()->gt($verification->expire_at)) {
                return response()->json(['status' => 400, 'Error' => 'Link Expired'], 400);
            }

            $user->verified = true;
            $user->save();

            $verification->delete();

            $baseUrl = config('app.base_url', 'http://localhost:4200');
            return redirect($baseUrl . '/verify?status=verified');
        } catch (\Exception $err) {
            return response()->json(['Status' => 400, 'Error' => 'Error Occurred'], 400);
        }
    }
}