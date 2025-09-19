<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\BusinessStore;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Auth\Events\Registered;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        return Inertia::render('auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'business_name' => 'required|string|max:255|unique:' . Business::class,
            'store_name' => 'required|string|max:255|unique:' . BusinessStore::class,
            'email' => 'required|string|lowercase|email|max:255|unique:' . User::class,
            'phone' => ['nullable', 'regex:/^(01)[0-9]{9}$/'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        $business = Business::create([
            'user_id'  => $user->id,
            'business_name' => $request->business_name,
            'email'    => $request->email,
            'phone'    => $request->phone,
            'is_active' => true
        ]);

        $store = BusinessStore::create([
            'user_id'    => $user->id,
            'business_id' => $business->id,
            'store_name'  => $request->store_name,
            'email'      => $request->email,
            'address'    => $request->address,
            'phone'      => $request->phone,
            'is_active'  => true
        ]);

        return to_route('dashboard');
    }
}
