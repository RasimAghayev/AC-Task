<?php

namespace App\Http\Controllers\Users\Repository;

use App\Models\User;

interface UserRepositoryInterface
{
    public function create(array $data): User;

    public function find(int $id): ?User;

    /**
     * @return bool
     */
    public function update(User $user, array $data): User;
}
