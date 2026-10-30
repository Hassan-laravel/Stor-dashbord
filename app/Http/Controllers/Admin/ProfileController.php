<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
     $user = $request->user();
        return view('admin.profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
      $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|min:8|confirmed', // confirmed تعني أنه يحتاج حقل password_confirmation
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        // تحديث كلمة المرور فقط إذا تم إدخال قيمة جديدة
        if ($request->filled('password')) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->back()->with('success', 'تم تحديث بيانات البروفايل بنجاح.');
    }
}