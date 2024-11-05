<?php
namespace App\Http\Controllers\Users\Repository;

use App\Models\User;

interface UserRepositoryInterface
{
    /**
     * @param array $data
     * @return User
     */
    public function create(array $data): User;

    /**
     * @param int $id
     * @return User|null
     */
    public function find(int $id): ?User;

    /**
     * @param User $user
     * @param array $data
     * @return bool
     */
    public function update(User $user, array $data): User;
}