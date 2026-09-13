<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Auth\Models\User;

class AuthSeeder extends Seeder
{
    public function run(): void
    {
          /*
             * 1. Generate the data using UserFactory
             * 2. Apply the admin state
             * 3. Override name and email below
             * 4. Save the User into the database
             */
        User::factory()
            ->admin()   //because at user factory default role is employee, we need to set it to admin`
            ->create([
                'name' => 'HRFlow Administrator',
                'email' => 'admin@hrflow.test',
            ]);
    }
}
