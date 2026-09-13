<?php

namespace Modules\Organization\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Organization\Models\JobTitle;

/**
 * @extends Factory<JobTitle>
 */
class JobTitleFactory extends Factory
{
    protected $model = JobTitle::class;

    public function definition(): array
    {
        return [
            'department_id' => null,

            'name' => fake()->randomElement([
                'Junior Developer',
                'Software Developer',
                'Senior Developer',
                'Team Lead',
                'HR Specialist',
                'HR Manager',
                'Accountant',
                'Senior Accountant',
                'Financial Analyst',
                'Marketing Specialist',
                'Marketing Manager',
                'Sales Representative',
                'Sales Manager',
                'Product Manager',
                'Project Manager',
                'Business Analyst',
                'Data Analyst',
                'System Administrator',
                'Support Specialist',
                'Operations Specialist',
            ]),

            'grade' => fake()->randomElement([
                'Junior',
                'Mid',
                'Senior',
                'Lead',
                'Manager',
            ]),
        ];
    }
}
