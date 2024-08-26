<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use App\Models\UserHistory;

class AdminController extends Controller
{
    public function dashboard()
    {
        if (Auth::user()->role !== 'admin') {
            return redirect('/home')->with('error', 'Unauthorized access');
        }

        // Użytkownicy, którzy nie są usunięci
        $totalUsers = User::where('is_deleted', false)->count();
        $activeUsers = User::where('is_deleted', false)->where('isActive', true)->count();
        $inactiveUsers = User::where('is_deleted', false)->where('isActive', false)->count();
        $deletedUsers = User::where('is_deleted', true)->count();

        // Produkty
        $totalProducts = Product::count();

        return view('admin.dashboard', compact('totalUsers', 'activeUsers', 'inactiveUsers', 'deletedUsers', 'totalProducts'));
    }

    public function manageUsers()
    {
        if (Auth::user()->role !== 'admin') {
            return redirect('/home')->with('error', 'Unauthorized access');
        }

        $users = User::where('is_deleted', false)->get();
        return view('admin.manage-users', compact('users'));
    }

    public function updateUser(Request $request, $id)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        $user = User::findOrFail($id);

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'role' => 'required|string|in:admin,user',
            'isActive' => 'nullable|boolean',
        ]);

        $changes = [];
        foreach ($validatedData as $key => $value) {
            if ($user->$key != $value) {
                $changes[$key] = ['old' => $user->$key, 'new' => $value];
            }
        }

        if ($request->filled('password')) {
            $changes['password'] = ['old' => 'hidden', 'new' => 'hidden'];
            $validatedData['password'] = bcrypt($request->password);
        }

        if (empty($changes)) {
            return response()->json(['success' => true]);
        }

        $user->update($validatedData);

        foreach ($changes as $field => $change) {
            UserHistory::create([
                'admin_id' => Auth::user()->id,
                'admin_name' => Auth::user()->name,
                'admin_lastname' => Auth::user()->lastname,
                'action' => 'updated',
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_lastname' => $user->lastname,
                'field' => $field,
                'old_value' => json_encode($change['old']),
                'new_value' => json_encode($change['new']),
            ]);
        }

        session()->flash('success', 'User updated successfully');
        return response()->json(['success' => true]);
    }

    public function storeUser(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|string|in:admin,user',
            'isActive' => 'nullable|boolean',
        ]);

        $isActive = $request->has('isActive') ? true : false;

        $user = User::create([
            'name' => $validatedData['name'],
            'lastname' => $validatedData['lastname'],
            'email' => $validatedData['email'],
            'password' => bcrypt($validatedData['password']),
            'role' => $validatedData['role'],
            'isActive' => $isActive,
        ]);

        UserHistory::create([
            'admin_id' => Auth::user()->id,
            'admin_name' => Auth::user()->name,
            'admin_lastname' => Auth::user()->lastname,
            'action' => 'added',
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_lastname' => $user->lastname,
            'new_value' => "Name: {$user->name}\nEmail: {$user->email}\nRole: {$user->role}",
        ]);

        return response()->json(['success' => true, 'message' => 'User added successfully']);
    }

    public function getUser($id)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        $user = User::findOrFail($id);
        return response()->json($user);
    }

    public function destroy($id)
    {
        if (Auth::user()->role !== 'admin') {
            return redirect('/home')->with('error', 'Unauthorized access');
        }

        $user = User::findOrFail($id);
        $oldData = 'Name: ' . $user->name . ', Email: ' . $user->email . ', Role: ' . $user->role;
        $user->update(['is_deleted' => true]);

        UserHistory::create([
            'admin_id' => Auth::user()->id,
            'admin_name' => Auth::user()->name,
            'admin_lastname' => Auth::user()->lastname,
            'action' => 'deleted',
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_lastname' => $user->lastname,
            'old_value' => $oldData,
        ]);

        session()->flash('success', 'User deleted successfully');
        return response()->json(['success' => true]);
    }

    public function showHistory($id)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        $histories = UserHistory::where('user_id', $id)->orderBy('created_at', 'desc')->get();
        return response()->json($histories);
    }
}
