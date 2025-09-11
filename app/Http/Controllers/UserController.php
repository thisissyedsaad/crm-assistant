<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $users = User::all(); // ya pagination
        if (auth()->user()->role !== 'super-admin') {
            $users = User::where('role', '!=', 'super-admin')->get();
        } else {
            $users = User::all(); // Super-admin sab dekh sakta hai
        }
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'role' => ['required', 'in:admin,staff,super-admin'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->back()->with('success', 'User created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    // public function update(Request $request, $id)
    // {
    //     $user = User::findOrFail($id);

    //     $request->validate([
    //         'name' => 'required|string|max:255',
    //         // 'email' => "required|email|unique:users,email,{$user->id}",
    //         'role' => 'required|in:admin,staff,super-admin',
    //         'password' => 'nullable|min:6|confirmed',
    //     ]);

    //     $user->name = $request->name;
    //     // $user->email = $request->email;
    //     $user->role = $request->role;

    //     if ($request->password) {
    //         $user->password = bcrypt($request->password);
    //     }

    //     $user->save();

    //     return redirect()->route('admin.users.index')->with('success', 'User updated successfully!');
    // }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validationRules = [
            'name' => 'required|string|max:255',
            'password' => 'nullable|min:6|confirmed',
        ];

        // Super-admin nahi hai to role validation add karo
        if ($user->role !== 'super-admin') {
            $validationRules['role'] = 'required|in:admin,staff';
        }

        $request->validate($validationRules);

        $user->name = $request->name;
        
        // Super-admin ka role update nahi karo
        if ($user->role !== 'super-admin') {
            $user->role = $request->role;
        }

        if ($request->password) {
            $user->password = bcrypt($request->password);
        }

        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully!');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);  

        if (auth()->id() == $user->id) {
            return redirect()->back()->withErrors(['delete' => 'You cannot delete your own account.']);
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully!');
    }
}
