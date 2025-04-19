<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
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
        DB::table('m_user')->insert([
            'id' => '9ad1d6ab-e234-433c-871b-73a8b7ff3a61',
            'm_user_roles_id' => 'f9e49521-4a4a-4b3b-b0ca-73f36c8aef47',
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'password' => Hash::make('password'),
            'updated_security' => date('Y-m-d H:i:s'),
        ]);

        DB::table('m_user')->insert([
            'id' => '375dfe0f-6ccf-4b78-b38f-ed17eb50d0c3',
            'm_user_roles_id' => '75d055eb-f4a4-4f47-acbd-d202b19a71fc',
            'name' => 'Jane Doe',
            'email' => 'janedoe@example.com',
            'password' => Hash::make('password'),
            'updated_security' => date('Y-m-d H:i:s'),
        ]);

        DB::table('m_user_roles')->insert([
            'id' => 'f9e49521-4a4a-4b3b-b0ca-73f36c8aef47',
            'name' => 'Admin',
            'access' => json_encode([
                'user' => [
                    'view' => true,
                    'create' => true,
                    'update' => true,
                    'delete' => true,
                ],
                'roles' => [
                    'view' => true,
                    'create' => true,
                    'update' => true,
                    'delete' => true,
                ],
            ]),
        ]);

        DB::table('m_user_roles')->insert([
            'id' => '75d055eb-f4a4-4f47-acbd-d202b19a71fc',
            'name' => 'Staff',
            'access' => json_encode([
                'user' => [
                    'view' => true,
                    'create' => true,
                    'update' => false,
                    'delete' => false,
                ],
                'roles' => [
                    'view' => true,
                    'create' => false,
                    'update' => false,
                    'delete' => false,
                ],
            ]),
        ]);
    }
}
