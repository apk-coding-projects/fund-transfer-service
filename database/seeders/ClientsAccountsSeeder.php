<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use src\Accounts\Models\Account;
use src\Clients\Models\Client;

class ClientsAccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Client::factory()
            ->count(50)
            ->has(Account::factory()->count(rand(1, 5)))
            ->create();
    }
}
