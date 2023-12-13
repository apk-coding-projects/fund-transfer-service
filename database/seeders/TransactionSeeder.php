<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use src\Accounts\Models\Account;
use src\Transactions\Models\Transaction;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (!Account::count()) {
            return;
        }

        Transaction::factory()
            ->count(500)
            ->create();
    }
}
