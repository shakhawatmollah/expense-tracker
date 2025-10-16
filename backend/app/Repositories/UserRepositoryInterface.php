<?php

namespace App\Repositories;

use App\Models\User;

interface UserRepositoryInterface
{
    /**
     * Create a new user
     */
    public function create(array $data): User;

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?User;

    /**
     * Find user by ID
     */
    public function find(int $id): ?User;

    /**
     * Update a user
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete a user
     */
    public function delete(int $id): bool;
}