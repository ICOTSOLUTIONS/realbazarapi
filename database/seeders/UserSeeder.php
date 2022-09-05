<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = new User();
        $admin->role_id = 1;
        $admin->username = 'admin';
        $admin->first_name = 'admin';
        $admin->email = 'admin@gmail.com';
        $admin->password = Hash::make(12345678);
        $admin->is_active = true;
        $admin->save();

        $admin = new User();
        $admin->role_id = 3;
        $admin->username = 'wholeseller';
        $admin->first_name = 'wholeseller';
        $admin->email = 'wholeseller@gmail.com';
        $admin->password = Hash::make(12345678);
        $admin->is_active = true;
        $admin->save();
    }
}
