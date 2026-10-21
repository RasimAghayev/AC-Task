<?php

namespace App\Http\Controllers\Auth\Services;

use App\Http\Controllers\Users\Repository\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {}

    public function register(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $data['password'] = Hash::make($data['password']);

            return $this->userRepository->create($data);
        });
    }

    /**
     * @param  int  $userId
     * @return bool
     */
    public function updateUser(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            return $this->userRepository->update($user, $data);
        });
    }
}
