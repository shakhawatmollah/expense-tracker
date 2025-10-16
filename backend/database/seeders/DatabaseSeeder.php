<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting database seeding...');

        // Seed users first
        $this->call([
            UserSeeder::class,
        ]);

        // Seed categories after users are created
        $this->call([
            CategorySeeder::class,
        ]);

        // Seed expenses after categories are created
        $this->call([
            ExpenseSeeder::class,
        ]);

        $this->command->info('✅ Database seeding completed successfully!');
        $this->command->info('📊 You can now login with:');
        $this->command->info('   Email: demo@example.com | Password: demo123');
        $this->command->info('   Email: john@example.com | Password: password123');
        $this->command->info('   Email: jane@example.com | Password: password123');
        $this->command->info('   Email: admin@example.com | Password: admin123');
    }
}
