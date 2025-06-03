<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class ProfileController extends Controller
{
    public function get(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['message' => 'User Not Found'], 404);
        }

        $userInfo = [
            'image' => $user->image,
            'full_name' => $user->full_name,
            'birth_date' => $user->birth_date,
        ];

        return response()->json($user);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['message' => 'User Not Found'], 404);
        }

        if ($request->hasFile('image')) {
            $uploadedFile = $request->file('image');
            $result = Cloudinary::upload($uploadedFile->getRealPath());
            $user->image = $result->getSecurePath();
        }

        $user->fill($request->except('image'));
        $user->save();

        return response()->json([
            'message' => 'User Updated Successfully',
            'updatedUser' => $user,
        ], 201);
    }

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