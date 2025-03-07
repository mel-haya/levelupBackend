<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */

     
    public function store(Request $request): Response
    {
        error_log('Form Data: ' . $request->input('name'));

        // Check if the file exists
        if ($request->hasFile('profile_picture')) {
            $file = $request->file('profile_picture');
            error_log('File Name: ' . $file->getClientOriginalName());
            error_log('File Type: ' . $file->getMimeType());
        } else {
            error_log('No profile picture found in the request.');
        }
    
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'profile_picture' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        // Handle the image upload if it exists
        $profilePicturePath = null;
        if ($request->hasFile('profile_picture')) {
            $image = $request->file('profile_picture');
            $profilePicturePath = $image->store('profile_pictures', 'public');
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->string('password')),
            'profile_picture' => $profilePicturePath,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return response()->noContent();
    }
}
