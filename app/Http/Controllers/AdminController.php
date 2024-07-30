<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserHistory;

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

        $users = User::where('is_deleted', false)->get();
        return view('admin.manage-users', compact('users'));
    }

    public function updateUser(Request $request, $id)
{
    if (auth()->user()->role !== 'admin') {
        return response()->json(['error' => 'Unauthorized access'], 403);
    }

    $user = User::findOrFail($id);

    try {
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

        $user->update($validatedData);

        foreach ($changes as $field => $change) {
            UserHistory::create([
                'admin_id' => auth()->user()->id,
                'admin_name' => auth()->user()->name,
                'admin_lastname' => auth()->user()->lastname,
                'action' => 'updated',
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_lastname' => $user->lastname,
                'old_value' => json_encode($change['old']),
                'new_value' => json_encode($change['new']),
            ]);
        }

        session()->flash('success', 'User updated successfully');
        return response()->json(['success' => true]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'error' => $e->getMessage()]);
    }
}




public function storeUser(Request $request)
{
    if (auth()->user()->role !== 'admin') {
        return response()->json(['error' => 'Unauthorized access'], 403);
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

    UserHistory::create([
        'admin_id' => auth()->user()->id,
        'admin_name' => auth()->user()->name,
        'admin_lastname' => auth()->user()->lastname,
        'action' => 'added',
        'user_id' => $user->id,
        'user_name' => $user->name,
        'user_lastname' => $user->lastname,
        'new_value' => 'Name: ' . $user->name . ', Email: ' . $user->email . ', Role: ' . $user->role,
    ]);

    return response()->json(['success' => true, 'message' => 'User added successfully']);
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
    $oldData = 'Name: ' . $user->name . ', Email: ' . $user->email . ', Role: ' . $user->role;
    $user->update(['is_deleted' => true]);

    UserHistory::create([
        'admin_id' => auth()->user()->id,
        'admin_name' => auth()->user()->name,
        'admin_lastname' => auth()->user()->lastname,
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
        if (auth()->user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        $histories = UserHistory::where('user_id', $id)->orderBy('created_at', 'desc')->get();
        return response()->json($histories);
    }
}
