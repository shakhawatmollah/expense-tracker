<?php

namespace Database\Seeders;

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
        $this->command->info('📊 Demo users have been created.');
        $this->command->info('ℹ️  Check the UserSeeder class for login credentials.');
        $this->command->warn('⚠️  Remember to change default passwords in production!');
    }
}
