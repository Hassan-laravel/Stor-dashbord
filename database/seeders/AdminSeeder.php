<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run()
    {
        User::updateOrCreate(
            ['email' => 'hassanabokotebarahhal@gmail.com'], // البريد الإلكتروني
            [
                'name' => 'مدير النظام',
                'password' => Hash::make('password123'), // كلمة المرور
            ]
        );
    }
}