<?php

namespace Modules\Organization\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Organization\Models\Department;

/**
 * @extends Factory<Department>
 */
class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company() . ' Department',

            'code' => fake()->unique()->regexify('[A-Z]{3}'),

            'parent_id' => null,

            'manager_id' => null,//when create department we don`t kow who is the manager => but in employee seeder it will update it to manager_id
        ];
    }
}
