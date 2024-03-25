<?php

namespace App\Http\Controllers;


use App\Models\User;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use Spatie\Permission\Models\Role;

/**
 * @OA\Tag(
 *     name="Authentication",
 *     description="Endpoints for user authentication"
 * )
 */
class AuthController extends Controller
{


    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|exists:roles,name', // Validate that the role exists
        ]);

        try {
            // Check if the role exists before creating the user
            if (!Role::where('name', $request->role)->exists()) {
                return response()->json(['message' => 'Role does not exist'], 400);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                // Assuming 'status' is a required field, add it if needed
            ]);

            // Sync the user's role
            $user->syncRoles([$request->role]);


            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'data' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }






    public function checkLoginStatus()
    {
        // Check if the user is logged in
        if (!Auth::check()) {
            return response()->json(['message' => 'User is not logged in'], 401);
        }

        $user = User::where('id', Auth::user()->id)
            ->firstOrFail();

        // Retrieve the token
        $token = $user->tokens->first()->token; // Assuming each user has only one token

        return response()->json([
            'message' => 'Hi ' . $user->name . ', welcome to home',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'name' => $user->name,
            'lastlogin' => $user->lastlogin,
            'email' => $user->email,
            'permissions' => $user->getAllPermissions()->map(function ($item) {
                return $item->name;
            }),
            'role' => $user->getRoleNames()->first() ?? "",

        ]);
    }






    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid Email Or Password'], 404);
        }

        $user = User::where('email', $request['email'])->firstOrFail();

        // Check if the user's status is active
        if ($user->status !== 'active') {
            return response()->json(['message' => 'Account is not active'], 403); // or another appropriate status code
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Hi ' . $user->name . ', welcome to home',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'name' => $user->name,
            'lastlogin' => $user->lastlogin,
            'email' => $user->email,
            'permissions' => $user->getAllPermissions()->map(function ($item) {
                return $item->name;
            }),
            'role' => $user->getRoleNames()->first() ?? "",

        ]);
    }






    // method for user logout and delete token
    public function logout()
    {
        auth()->user()->tokens()->delete();

        return [
            'message' => 'You have successfully logged out and the token was successfully deleted'
        ];
    }

    /**
     * @OA\Post(
     *      path="/logout",
     *      operationId="logout",
     *      tags={"Authentication"},
     *      summary="Logout",
     *      description="Log out the currently authenticated user",
     *      security={
     *          {"bearerAuth": {}}
     *      },
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="integer", example=200),
     *              @OA\Property(property="message", type="string", example="You have successfully logged out and your token has been deleted"),
     *          ),
     *      )
     * )
     */
    // public function logout()
    // {
    //     Auth::user()->currentAccessToken()->delete();

    //     return $this->success(['message' => 'You have successfully logged out and your token has been deleted']);
    // }
}