<?php

require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$app->boot();

echo "=== Budget Database Check ===\n";
echo "Budget count: " . App\Models\Budget::count() . "\n";

$budgets = App\Models\Budget::with('user', 'category')->get();

foreach ($budgets as $budget) {
    echo "ID: {$budget->id}\n";
    echo "Name: {$budget->name}\n";
    echo "Amount: {$budget->amount}\n";
    echo "Period: {$budget->period}\n";
    echo "User: {$budget->user->email}\n";
    echo "Category: " . ($budget->category ? $budget->category->name : 'None') . "\n";
    echo "Active: " . ($budget->is_active ? 'Yes' : 'No') . "\n";
    echo "---\n";
}