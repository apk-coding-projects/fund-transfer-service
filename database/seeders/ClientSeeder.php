<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use src\Clients\Models\Client;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Client::factory()
            ->count(15)
            ->create();
    }
}
