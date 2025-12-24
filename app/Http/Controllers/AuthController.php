<?php
namespace App\Http\Controllers;
//backend\app\Http\Controllers\AuthController.php
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Registrace uživatele
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'], // očekává i password_confirmation
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // vytvoření tokenu
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user'  => $user,
            'token' => $token,
        ], 201);
    }

    /**
     * Přihlášení uživatele
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Zadané přihlašovací údaje jsou neplatné.'],
            ]);
        }

        // volitelně zneplatníme staré tokeny:
        // $user->tokens()->delete();

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user'  => $user,
            'token' => $token,
        ]);
    }

    /**
     * Odhlášení – zneplatnění aktuálního tokenu
     */
    public function logout(Request $request)
    {
        // smaže jen aktuální token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Byl jste odhlášen.',
        ]);
    }

    /**
     * Vrátí aktuálně přihlášeného uživatele
     */
    public function me(Request $request)
    {
        return response()->json($request->user());
    }
    /**
     * Smazání aktuálně přihlášeného uživatele
     */
    public function destroy(Request $request)
    {
        $user = $request->user();

        // ❗ Doporučeno: ověření hesla před smazáním účtu
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        if (! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['Zadané heslo není správné.'],
            ]);
        }

        // zneplatní všechny tokeny
        $user->tokens()->delete();

        // smaže uživatele
        $user->delete();

        return response()->json([
            'message' => 'Uživatel byl úspěšně smazán.',
        ]);
    }
    /**
     * Aktualizace profilu aktuálně přihlášeného uživatele
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'email',
                'max:255',
                'unique:users,email,' . $user->id,
            ],
            'password' => ['sometimes', 'string', 'min:8', 'confirmed'],
        ]);

        // změna hesla (pokud je posláno)
        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'message' => 'Uživatel byl úspěšně aktualizován.',
            'user' => $user->fresh(),
        ]);
    }
}
