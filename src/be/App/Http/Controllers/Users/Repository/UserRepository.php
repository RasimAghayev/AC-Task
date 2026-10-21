<?php

namespace App\Http\Controllers\Users\Repository;

use App\Models\User;

class UserRepository implements UserRepositoryInterface
{
    /**
     * UserRepository constructor.
     */
    public function __construct(
        protected readonly User $model
    ) {}

    public function create(array $data): User
    {
        return $this->model->create($data);
    }

    public function find(int $id): ?User
    {
        return $this->model->find($id);
    }

    /**
     * @return bool
     */
    public function update(User $user, array $data): User
    {
        return tap($user)->update([
            'name' => $data['name'],
        ]);
    }
}
