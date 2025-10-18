<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

// Check if test user already exists
$existingUser = User::where('email', 'test@example.com')->first();

if ($existingUser) {
    echo "Test user already exists!\n";
    echo "Email: " . $existingUser->email . "\n";
    echo "Name: " . $existingUser->name . "\n";
} else {
    // Create test user
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);
    
    echo "Test user created successfully!\n";
    echo "Email: test@example.com\n";
    echo "Password: password123\n";
    echo "User ID: " . $user->id . "\n";
}