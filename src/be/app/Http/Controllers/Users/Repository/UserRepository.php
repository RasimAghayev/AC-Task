<?php
namespace App\Http\Controllers\Users\Repository;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserRepository implements UserRepositoryInterface
{
    /**
     * UserRepository constructor.
     *
     * @param User $model
     */
    public function __construct(
        protected readonly User $model
    ){}

    /**
     * @param array $data
     * @return User
     */
    public function create(array $data): User
    {
        return DB::transaction(function () use ($data) {
                return $this->model->create($data);
        });
    }

    /**
     * @param int $id
     * @return User|null
     */
    public function find(int $id): ?User
    {
        return $this->model->find($id);
    }

    /**
     * @param User $user
     * @param array $data
     * @return bool
     */
    public function update(User $user, array $data): User
    {
        return DB::transaction(function () use ($user,$data) {
            return tap($user)->update([
                'name' => $data['name'],
            ]);
        });
    }
}