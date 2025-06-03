<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Verification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationEmail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    public function profile(Request $request)
    {
        $user = $request->user();
        return response()->json(['Name' => 'Profile', 'Users' => $user]);
    }

    public function resetLink(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $user = User::where('email', $request->email)->first();

        if ($user) {
            $uniqueString = Str::random(40);
            $expireAt = now()->addHours(6);

            Verification::create([
                'user_id' => $user->id,
                'unique_string' => $uniqueString,
                'expire_at' => $expireAt,
            ]);

            $resetUrl = URL::to('/User/ResetPassword/' . $user->id . '/' . $uniqueString);

            Mail::to($user->email)->send(new VerificationEmail(
                $user,
                'Password Reset',
                'Reset Your Password',
                $resetUrl
            ));

            return response()->json([
                'status' => 200,
                'msg' => 'Password Reset Link Sent Successfully',
            ], 200);
        }

        return response()->json([
            'status' => 400,
            'msg' => 'Failed To Send Password Reset Link',
        ], 400);
    }

    public function resetLogic(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        try {
            $user = User::find($id);

            if ($user) {
                $user->password = Hash::make($request->password);
                $user->save();

                return response()->json([
                    'status' => 200,
                    'message' => 'Password reset successful',
                ], 200);
            }

            return response()->json([
                'status' => 400,
                'error' => 'User Not Found',
            ], 400);
        } catch (\Exception $error) {
            return response()->json([
                'status' => 500,
                'error' => 'Internal Server Error',
            ], 500);
        }
    }

    public function emailUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $user = $request->user();
        $newEmail = $request->email;

        $user->email = $newEmail;
        $user->verified = false;
        $user->save();

        $uniqueString = Str::random(40);
        $expireAt = now()->addHours(6);

        Verification::create([
            'user_id' => $user->id,
            'unique_string' => $uniqueString,
            'expire_at' => $expireAt,
        ]);

        $verificationUrl = URL::to('/User/verify/' . $user->id . '/' . $uniqueString);

        Mail::to($user->email)->send(new VerificationEmail(
            $user,
            'Account Verification',
            'Please Verify your account',
            $verificationUrl
        ));

        $token = JWTAuth::fromUser($user, [
            'id' => $user->id,
            'email' => $user->email,
            'role' => $user->role,
            'username' => $user->username,
            'verified' => $user->verified,
        ]);

        return response()->json([
            'token' => $token,
            'msg' => $newEmail . ' updated successfully',
        ], 200);
    }

    // Admin methods
    public function deleteUser($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'Status' => 400,
                'error' => 'User Not Found',
            ], 400);
        }

        $user->delete();

        return response()->json([
            'Status' => 200,
            'message' => 'User Deleted',
        ], 200);
    }

    public function getUsers()
    {
        $users = User::where('role', 'user')->get();

        if ($users->isEmpty()) {
            return response()->json([
                'Status' => 400,
                'error' => 'No Users Found',
            ], 400);
        }

        return response()->json([
            'Status' => 200,
            'Users' => $users,
        ], 200);
    }

    public function getUser($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'Status' => 400,
                'error' => 'User Not Found',
            ], 400);
        }

        return response()->json([
            'Status' => 200,
            'User' => $user,
        ], 200);
    }

    public function countUsers()
    {
        $count = User::where('role', 'user')->count();

        return response()->json(['count' => $count]);
    }
}