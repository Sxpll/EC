<?php

namespace App\Http\Controllers;
 
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function dashboard()
    {
        if (auth()->user()->role !== 'admin') {
            return redirect('/home')->with('error', 'Unauthorized access');
        }

        $totalUsers = User::count();
        $activeUsers = User::where('isActive', true)->count();
        $inactiveUsers = User::where('isActive', false)->count();

        return view('admin.dashboard', compact('totalUsers', 'activeUsers', 'inactiveUsers'));
    }

    public function manageUsers()
    {
        if (auth()->user()->role !== 'admin') {
            return redirect('/home')->with('error', 'Unauthorized access');
        }

        $users = User::all();
        return view('admin.manage-users', compact('users'));
    }

    public function updateUser(Request $request, $id)
    {
        if (auth()->user()->role !== 'admin') {
            return redirect('/home')->with('error', 'Unauthorized access');
        }

        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'role' => 'required|string|in:admin,user',
            'isActive' => 'nullable|boolean',
        ]);

        $changes = [];

        if ($request->name !== $user->name) {
            $changes['name'] = ['old' => $user->name, 'new' => $request->name];
        }

        if ($request->lastname !== $user->lastname) {
            $changes['lastname'] = ['old' => $user->lastname, 'new' => $request->lastname];
        }

        if ($request->email !== $user->email) {
            $changes['email'] = ['old' => $user->email, 'new' => $request->email];
        }

        if ($request->role !== $user->role) {
            $changes['role'] = ['old' => $user->role, 'new' => $request->role];
        }

        if ($request->has('password')) {
            $changes['password'] = ['old' => 'hidden', 'new' => 'hidden'];
        }

        if ($request->input('isActive') != $user->isActive) {
            $changes['isActive'] = ['old' => $user->isActive, 'new' => $request->input('isActive') ? true : false];
        }

        $user->update([
            'name' => $request->name,
            'lastname' => $request->lastname,
            'email' => $request->email,
            'role' => $request->role,
            'isActive' => $request->input('isActive') ? true : false,
        ]);

        if ($request->has('password')) {
            $user->update([
                'password' => bcrypt($request->password),
            ]);
        }

        return redirect()->route('admin.manageUsers')->with('success', 'User updated successfully');
    }

    public function storeUser(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            return redirect('/home')->with('error', 'Unauthorized access');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|string|in:admin,user',
            'isActive' => 'nullable|boolean',
        ]);

        $isActive = $request->has('isActive') ? true : false;

        $user = User::create([
            'name' => $request->name,
            'lastname' => $request->lastname,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role,
            'isActive' => $isActive,
        ]);

        return redirect()->route('admin.manageUsers')->with('success', 'User added successfully');
    }

    public function getUser($id)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        $user = User::findOrFail($id);
        return response()->json($user);
    }

    public function destroy($id)
    {
        if (auth()->user()->role !== 'admin') {
            return redirect('/home')->with('error', 'Unauthorized access');
        }

        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['success' => true]);
    }

    public function showHistory()
    {
        if (auth()->user()->role !== 'admin') {
            return redirect('/home')->with('error', 'Unauthorized access');
        }

        $histories = UserHistory::with(['admin', 'user'])->orderBy('created_at', 'desc')->get();
        return view('admin.history', compact('histories'));
    }
}
