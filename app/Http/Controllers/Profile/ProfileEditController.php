<?php

namespace App\Http\Controllers\Profile;

use App\Models\User;
use App\Models\Image;
use ImageKit\ImageKit;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProfileEditController extends Controller
{
    public function update($id, Request $request)
    {
        $validated = $request->validate([
            "name" => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'birthday' => ['required', 'string'],
            'profile_photo_path' => ['nullable', 'file', 'max:1024'],
        ]);

        // Get The user Data
        $user = User::find($id);


        $imageKit = new ImageKit(
            env('IMAGEKIT_PUBLIC_KEY'),
            env('IMAGEKIT_PRIVATE_KEY'),
            env('IMAGEKIT_URL_ENDPOINT')
        );
        //to check the there have user upload file , it update the profile_photo_path of the user
        if ($request->file('profile_photo_path')) {
            //Check the imagekit file id , i must use to delete the image in imagekit
            if ($user->file_id) {
                $imageKit->deleteFile($user->file_id);
            }

            // After Delete the old image , update the new image
            $upload = $imageKit->uploadFile([
                'file' => fopen($request->file('profile_photo_path')->getPathname(), 'r'),
                'fileName' => $request->file('profile_photo_path')->getClientOriginalName(),
                'folder' => '/Profile/' // 👈 store in your folder
            ]);

            // Hold the changed data in temp 
            // Thant against the databse data and check if there is changing update the temp data to database
            $user->fill([
                'name' => $validated['name'],
                'second_name' => null,
                'email' => $validated['email'],
                'birthday' => $validated['birthday'],
                'profile_photo_path' => $upload->result->url,
                'file_id' => $upload->result->fileId
            ]);
        } else {
            // update when there have no user upload file 
            $user->fill([
                'name' => $validated['name'],
                'second_name' => null,
                'email' => $validated['email'],
                'birthday' => $validated['birthday'],
                'profile_photo_path' => $user->profile_photo_path
            ]);
        }

        // Thant against the databse data and check if there is changing update the temp data to database
        if ($user->isDirty()) {
            $user->save();
            return response()->json([
                "message" => "updated",
                "users" => $user
            ]);
        }
    }
}
