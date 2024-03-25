<?php

namespace App\Http\Controllers\API;

use App\Models\User;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;


class UserController extends Controller
{
    public function index()
    {
        // if (!Auth::user()->can('view users')) {
        //     return response()->json(['message' => 'Unauthorized'], 403);
        // }

        // Retrieve all users with their one-to-one relationships
        $users = User::get();

        // Adding role names to each user
        $users->transform(function ($user) {
            $user->role = $user->getRoleNames()->first() ?? "";
            return $user;
        });

        return response()->json($users);
    }

    public function show($id)
    {
        // if (!Auth::user()->can('view user')) {
        //     return response()->json(['message' => 'Unauthorized'], 403);
        // }

        $user = User::findOrFail($id);

        // Adding role name
        $user->role = $user->getRoleNames()->first() ?? "";

        return response()->json($user);
    }


    public function store(Request $request)
    {
        // Check permission
        // if (!Auth::user()->can('create user')) {
        //     return response()->json(['message' => 'Unauthorized'], 403);
        // }

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'status' => 'required|string|max:255',
            'lastlogin' => 'nullable|date',
            'password' => 'required|string|min:8',
            'role' => 'required|exists:roles,name',
        ]);


        DB::beginTransaction();

        try {

            // Create user
            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'status' => $validatedData['status'],
                'lastlogin' => $validatedData['lastlogin'] ?? now(),
                'password' => Hash::make($validatedData['password']),
            ]);



            // Sync the user's role
            $user->syncRoles([$validatedData['role']]);

            // Optionally get permissions associated with the user's role
            // $permissions = Permission::whereIn('id', $user->roles->first()->permissions->pluck('id'))->pluck('name');
            // $user->permissions = $permissions;


            DB::commit();
            return response()->json(['message' => 'User created successfully!', 'user' => $user], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'User creation failed: ' . $e->getMessage()], 500);
        }
    }



    public function update(Request $request, $id)
    {
        // Check permission
        // if (!Auth::user()->can('update user')) {
        //     return response()->json(['message' => 'Unauthorized'], 403);
        // }

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'status' => 'required|string|max:255',
            'lastlogin' => 'nullable|date',
            'password' => 'sometimes|string|min:8',
            'role' => 'sometimes|exists:roles,name', // Add validation for role
        ]);

        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }



        // $programId = $validatedData['program_id'];

        DB::beginTransaction();

        try {
            $user->update([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'status' => $validatedData['status'],
                'lastlogin' => $validatedData['lastlogin'] ?? now(),
                'password' => isset($validatedData['password']) ? Hash::make($validatedData['password']) : $user->password,
            ]);




            // Sync role if provided
            if (isset($validatedData['role'])) {
                $user->syncRoles([$validatedData['role']]);
            }

            DB::commit();
            return response()->json(['message' => 'User updated successfully!', 'user' => $user], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Update failed: ' . $e->getMessage()], 500);
        }
    }




    // ========================== destroy ====================
    public function destroy($id)
    {
        // if (!Auth::user()->can('delete user')) {
        //     return response()->json(['message' => 'Unauthorized'], 403);
        // }

        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }
}