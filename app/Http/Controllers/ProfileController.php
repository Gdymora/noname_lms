<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        
        // Заполняем данными из формы (name, email)
        $user->fill($request->validated());

        // Если email изменился — сбрасываем верификацию
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        // --- ЛОГИКА АВАТАРКИ ---
        if ($request->hasFile('avatar')) {
            // 1. Удаляем старую, если была (чтобы не засорять диск)
            if ($user->avatar_url) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar_url);
            }
            
            // 2. Сохраняем новую
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar_url = $path;
        }
        // -----------------------

        $user->save();

        return Redirect::route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
