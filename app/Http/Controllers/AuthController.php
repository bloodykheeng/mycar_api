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
        $validatedData =  $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|exists:roles,name', // Validate that the role exists
            'vendor_id' => 'nullable|exists:vendors,id',
        ]);

        try {
            // Check if the role exists before creating the user
            if (!Role::where('name', $request->role)->exists()) {
                return response()->json(['message' => 'Role does not exist'], 400);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'status' => $request->status,
                'password' => Hash::make($request->password),
                // Assuming 'status' is a required field, add it if needed
            ]);

            // Sync the user's role
            $user->syncRoles([$request->role]);

            // Handle UserVendor relationship
            if (isset($validatedData['vendor_id'])) {
                $user->vendors()->create(['vendor_id' => $validatedData['vendor_id']]);
            }



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

        /** @var \App\Models\User */
        $user = Auth::user();

        // Retrieve the token
        $token = $user->tokens->first()->token ?? ''; // Adjusted to handle potential null value

        $response = [
            'message' => 'Hi ' . $user->name . ', welcome to home',
            'id' => $user->id,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'name' => $user->name,
            'lastlogin' => $user->lastlogin,
            'email' => $user->email,
            'status' => $user->status,
            'photo_url' => $user->photo_url,
            'permissions' => $user->getAllPermissions()->pluck('name'), // pluck for simplified array
            'role' => $user->getRoleNames()->first() ?? "",
        ];

        // Check if the user is a Vendor and include vendor details
        if ($user->hasRole('Vendor')) {
            $vendor = $user->vendors()->first(); // Assuming there's a vendors() relationship
            $response['vendor'] = [
                'id' => $vendor->vendor_id ?? null,
                'name' => $vendor->vendor->name ?? 'Unknown Vendor', // Assuming there's a name attribute on the vendor
            ];
        }

        return response()->json($response);
    }





    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid Email Or Password'], 401);
        }

        $user = User::where('email', $request['email'])->firstOrFail();

        // Check if the user's status is active
        if ($user->status !== 'active') {
            return response()->json(['message' => 'Account is not active'], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        $response = [
            'message' => 'Hi ' . $user->name . ', welcome to home',
            'id' => $user->id,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'name' => $user->name,
            'photo_url' => $user->photo_url,
            'lastlogin' => $user->lastlogin,
            'email' => $user->email,
            'status' => $user->status,
            'permissions' => $user->getAllPermissions()->pluck('name'),
            'role' => $user->getRoleNames()->first() ?? "",
        ];

        // Include vendor details if the user is a Vendor
        if ($user->hasRole('Vendor')) {
            $vendor = $user->vendors()->first(); // Assuming there's a vendors() relationship
            $response['vendor'] = [
                'id' => $vendor->vendor_id ?? null,
                'name' => $vendor->vendor->name ?? 'Unknown Vendor',
            ];
        }

        return response()->json($response);
    }






    // method for user logout and delete token
    public function logout()
    {
        /** @var \App\Models\User */
        $user = auth()->user(); // Get the authenticated user

        // Delete all tokens for the user
        $user->tokens()->delete();

        return response()->json(['message' => 'Logged out successfully']);
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
