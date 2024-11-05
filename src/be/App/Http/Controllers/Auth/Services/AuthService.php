<?php

namespace App\Http\Controllers\Auth\Services;


use App\Http\Controllers\Users\Repository\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{

    /**
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ){}

    /**
     * @param array $data
     * @return User
     */
    public function register(array $data): User
    {
        $data['password'] = Hash::make($data['password']);
        return $this->userRepository->create($data);
    }

    /**
     * @param int $userId
     * @param array $data
     * @return bool
     */
    public function updateUser(User $user, array $data): User
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        return $this->userRepository->update($user, $data);
    }
}
