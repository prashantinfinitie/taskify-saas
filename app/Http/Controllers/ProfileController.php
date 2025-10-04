<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Client;
use App\Models\Profile;
use Illuminate\Http\Request;
use App\Services\DeletionService;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show()
    {
        $roles = Role::all();
        return view('users.account', ['user' => getAuthenticatedUser(), 'roles' => $roles]);
    }

    public function update(Request $request, $id)
    {

        $rules = [
            'first_name' => ['required'],
            'last_name' => ['required'],
            'phone' => 'required',
            'role' => 'required',
            'address' => 'nullable',
            'city' => 'nullable',
            'state' => 'nullable',
            'country' => 'nullable',
            'country_code' => 'nullable',
            'country_iso_code' => 'nullable',
            'zip' => 'nullable',
            'password' => 'nullable|min:6',
            'password_confirmation' => 'nullable|required_with:password|same:password',
        ];

        if (getAuthenticatedUser()->hasRole('admin')) {
            $rules['email'] = [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($id),
            ];
        }

        $formFields = $request->validate($rules);
        if (isset($formFields['password']) && !empty($formFields['password'])) {
            $formFields['password'] = bcrypt($formFields['password']);
        } else {
            unset($formFields['password']);
        }


        $user = isUser() ? User::findOrFail($id) : Client::findOrFail($id);
        $user->update($formFields);
        $role = Role::find($request->input('role'));
        $user->syncRoles($role);


        return response()->json(['error' => false, 'message' => 'Profile details updated successfully']);
    }

    public function update_photo(Request $request, $id)
    {
        // Validate the image file
        $request->validate([
            'upload' => ['required', 'image', 'mimes:jpeg,jpg,png', 'max:2048'], // max:2048 = 2MB
        ], [
            'upload.required' => 'Please select a profile picture to upload.',
            'upload.image' => 'The file must be an image.',
            'upload.mimes' => 'Only PNG, JPEG, and JPG formats are allowed.',
            'upload.max' => 'The image size should not exceed 2MB.',
        ]);

        // Handle the file upload
        if ($request->hasFile('upload')) {
            $user = isUser() ? User::findOrFail($id) : Client::findOrFail($id);

            // Delete the old photo if it exists and is not the default
            if ($user->photo != 'photos/no-image.jpg' && $user->photo !== null) {
                Storage::disk('public')->delete($user->photo);
            }

            // Store the new photo
            $formFields['photo'] = $request->file('upload')->store('photos', 'public');

            // Update the user's photo
            $user->update($formFields);

            return response()->json(['error' => false, 'message' => 'Profile picture updated successfully']);
        }

        return response()->json(['error' => true, 'message' => 'No profile picture selected!']);
    }


    public function destroy($id)
    {
        $user = isUser() ? User::findOrFail($id) : Client::findOrFail($id);
        isUser() ? DeletionService::delete(User::class, $id, 'Account') : DeletionService::delete(Client::class, $id, 'Account');
        $user->todos()->delete();
        return redirect('/');
    }
}