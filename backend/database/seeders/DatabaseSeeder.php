<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $now = now()->toDateString();
        $faker = \Faker\Factory::create("fr_FR");

        // Add roles data
        $rolesArray = ['admin', 'client', 'producteur'];
        for ($i = 0; $i < count($rolesArray); $i++) {
            DB::table('roles')->insert([
                'label' => $rolesArray[$i]
            ]);
        }

        // Add Users data
        DB::table('users')->insert([
            'identity'       => "admin",
            'name'       => "Dexter Morgan",
            'email'          => "admin@miel-pei.com",
            'password'       => bcrypt("Admin974,"), // password
            'remember_token' => Str::random(10),
            'role_id'        => 1,
            'created_at'     => $now,
            'updated_at'     => $now
        ]);

        DB::table('users')->insert([
            'identity'       => "user",
            'name'       => "Peter BISHOP",
            'email'          => "user@miel-pei.com",
            'password'       => bcrypt("User974,"), // password
            'remember_token' => Str::random(10),
            'role_id'        => 1,
            'created_at'     => $now,
            'updated_at'     => $now
        ]);
        
        for($i = 2; $i < 4; $i++){
            DB::table('users')->insert([
                'identity'       => $faker->name,
                'email'          => $faker->unique()->safeEmail,
                'password'       => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
                'remember_token' => Str::random(10),
                'role_id'        => 2,
                'created_at'     => $now,
                'updated_at'     => $now
            ]);
        }

        for ($i = 4; $i < 6; $i++) {
            DB::table('users')->insert([
                'identity'       => $faker->name,
                'email'          => $faker->unique()->safeEmail,
                'password'       => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
                'remember_token' => Str::random(10),
                'role_id'        => 3,
                'created_at'     => $now,
                'updated_at'     => $now
            ]);
        }


        // Add product data
        for ($i = 1; $i < 11; $i++) {
            DB::table('products')->insert([
                "name"       => "Miel péi {$i}",
                "price"      => $faker->numberBetween(1, 15),
                "quantity"   => $faker->numberBetween(1, 15),
                "image"      => "default.jpg",
                "amountSell" => $faker->numberBetween(1, 15),
                'created_at' => $now,
                'updated_at' => $now
            ]);
        }

        for ($i = 1; $i < 6; $i++) {
            DB::table('producers')->insert([
                "user_id"    => 5,
                "product_id" => $i,
            ]);
        }

        for ($i = 6; $i < 11; $i++) {
            DB::table('producers')->insert([
                "user_id"    => 6,
                "product_id" => $i,
            ]);
        }
    }
}
