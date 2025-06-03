<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class ProfileController extends Controller
{
    // Get authenticated user profile
    public function get(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'User Not Found'], 404);
        }

        // You can select only the fields you want to expose
        $userInfo = [
            'image' => $user->image,
            'full_name' => $user->full_name,
            'birth_date' => $user->birth_date,
            'email' => $user->email,
            'username' => $user->username,
            'gender' => $user->gender,
            'role' => $user->role,
        ];

        return response()->json($userInfo);
    }

    // Update authenticated user profile
    public function update(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'User Not Found'], 404);
        }

        // Validate incoming data (optional but recommended)
        $request->validate([
            'full_name' => 'sometimes|string|max:255',
            'birth_date' => 'sometimes|date',
            'image' => 'sometimes|file|image|max:2048', // max 2MB image
            'gender' => 'sometimes|string|in:male,female,other',
        ]);

        if ($request->hasFile('image')) {
            $uploadedFile = $request->file('image');
            $result = Cloudinary::upload($uploadedFile->getRealPath());
            $user->image = $result->getSecurePath();
        }

        // Update other user fields except 'image' and fields you want to protect like 'role', 'email', 'password'
        $user->fill($request->except(['image', 'role', 'email', 'password']));
        $user->save();

        return response()->json([
            'message' => 'User Updated Successfully',
            'updatedUser' => $user,
        ], 200);
    }

    // Delete authenticated user
    public function destroy(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'User Not Found'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'User Deleted Successfully'], 200);
    }
}
