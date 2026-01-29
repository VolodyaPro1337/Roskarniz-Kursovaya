<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Регистрация пользователя (вызывается из Telegram бота)
     */
    public function register(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|unique:users,phone',
            'password' => 'required|string|min:6',
            'telegram_id' => 'required|integer|unique:users,telegram_id',
            'name' => 'nullable|string|max:255',
        ]);

        $user = User::create([
            'name' => $request->name ?? 'Пользователь',
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'telegram_id' => $request->telegram_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Регистрация успешна',
            'user' => $user->only(['id', 'name', 'phone']),
        ], 201);
    }

    /**
     * Вход по телефону + пароль
     */
    public function login(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'phone' => ['Неверный номер телефона или пароль'],
            ]);
        }

        // Логиним пользователя через сессию
        Auth::login($user, $request->boolean('remember'));

        $request->session()->regenerate();

        return response()->json([
            'success' => true,
            'user' => $user->only(['id', 'name', 'phone', 'is_admin']),
        ]);
    }

    /**
     * Выход
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'message' => 'Вы вышли из системы',
        ]);
    }

    /**
     * Получить текущего пользователя
     */
    public function user(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'authenticated' => false,
            ]);
        }

        return response()->json([
            'authenticated' => true,
            'user' => $user->only(['id', 'name', 'phone', 'is_admin']),
        ]);
    }
}
