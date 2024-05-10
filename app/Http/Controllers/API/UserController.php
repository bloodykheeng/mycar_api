<?php

namespace App\Http\Controllers\API;

use App\Models\User;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;


class UserController extends Controller
{
    public function index(Request $request)
    {
        // if (!Auth::user()->can('view users')) {
        //     return response()->json(['message' => 'Unauthorized'], 403);
        // }

        $query = User::query();

        // Order the results by the created_at column in descending order (latest first)
        // $query->latest();

        // Check if vendor_id is provided and not null
        if ($request->has('vendor_id') && $request->vendor_id !== null) {
            // Filter users by the provided vendor_id
            $query->whereHas('vendors', function ($query) use ($request) {
                $query->where('vendor_id', $request->vendor_id);
            });
        }

        // Filter by role if provided
        if ($request->has('role') && $request->role !== null) {
            $query->role($request->role); // This uses the role scope provided by Spatie's permission package
        }

        // Retrieve all users with their one-to-one relationships
        $users = $query->with(["vendors.vendor"])->get();

        // Adding role names to each user
        $users->transform(function ($user) {
            $user->role = $user->getRoleNames()->first() ?? "";
             // Adding permissions
        $user->permissions = $user->getAllPermissions()->pluck('name');
            return $user;
        });

        return response()->json($users);
    }


    public function show($id)
    {
        // if (!Auth::user()->can('view user')) {
        //     return response()->json(['message' => 'Unauthorized'], 403);
        // }

        $user = User::with(["vendors.vendor"])->findOrFail($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Adding role name
        $user->role = $user->getRoleNames()->first() ?? "";

        // Adding permissions
        // $user_perissions = $user->getAllPermissions()->pluck('name');
        // $user->permissions = $user_perissions;
        $user->permissions = $user->getPermissionsViaRoles()->pluck('name');
   

       
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
            'vendor_id' => 'nullable|exists:vendors,id', // validate vendor_id
            'photo' => 'nullable|file|mimes:jpg,jpeg,png|max:2048', // Expect a file for the photo
        ]);

        $photoUrl = null;
        if ($request->hasFile('photo')) {
            $photoUrl = $this->uploadPhoto($request->file('photo'), 'user_photos'); // Save the photo in a specific folder
        }

        DB::beginTransaction();

        try {

            // Create user
            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'status' => $validatedData['status'],
                'lastlogin' => $validatedData['lastlogin'] ?? now(),
                'password' => Hash::make($validatedData['password']),
                'photo_url' => $photoUrl,
            ]);



            // Sync the user's role
            $user->syncRoles([$validatedData['role']]);

            // Optionally get permissions associated with the user's role
            // $permissions = Permission::whereIn('id', $user->roles->first()->permissions->pluck('id'))->pluck('name');
            // $user->permissions = $permissions;

            // Handle UserVendor relationship
            if (isset($validatedData['vendor_id'])) {
                $user->vendors()->create(['vendor_id' => $validatedData['vendor_id']]);
            }


            DB::commit();
            return response()->json(['message' => 'User created successfully!', 'user' => $user], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'User creation failed: ' . $e->getMessage()], 500);
        }
    }

    private function uploadPhoto($photo, $folderPath)
    {
        $publicPath = public_path($folderPath);
        if (!File::exists($publicPath)) {
            File::makeDirectory($publicPath, 0777, true, true);
        }

        $fileName = time() . '_' . $photo->getClientOriginalName();
        $photo->move($publicPath, $fileName);

        return '/' . $folderPath . '/' . $fileName;
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
            'photo' => 'nullable|file|mimes:jpg,jpeg,png|max:2048', // Validation for photo
            'role' => 'sometimes|exists:roles,name',
            'vendor_id' => 'nullable|exists:vendors,id',
        ]);


        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $photoUrl = $user->photo_url;
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($photoUrl) {
                $photoPath = parse_url($photoUrl, PHP_URL_PATH);
                $photoPath = ltrim($photoPath, '/');
                if (file_exists(public_path($photoPath))) {
                    unlink(public_path($photoPath));
                }
            }
            $photoUrl = $this->uploadPhoto($request->file('photo'), 'user_photos');
        }

        DB::beginTransaction();

        try {
            $user->update([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'status' => $validatedData['status'],
                'lastlogin' => $validatedData['lastlogin'] ?? now(),
                'photo_url' => $photoUrl,
            ]);

            if (isset($validatedData['role'])) {
                $user->syncRoles([$validatedData['role']]);
            }

            if (isset($validatedData['vendor_id'])) {
                $user->vendors()->updateOrCreate(
                    ['user_id' => $user->id],
                    ['vendor_id' => $validatedData['vendor_id']]
                );
            }
            // else {
            //     $user->vendors()->delete();
            // }

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
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Delete user photo if exists
        if ($user->photo_url) {
            $photoPath = parse_url($user->photo_url, PHP_URL_PATH);
            $photoPath = ltrim($photoPath, '/');
            if (file_exists(public_path($photoPath))) {
                unlink(public_path($photoPath));
            }
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }
}