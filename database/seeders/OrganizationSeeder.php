<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Organization\Models\Department;
use Modules\Organization\Models\JobTitle;

class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        //we don`t use department factory because we want a specific departments
        $departments = [
            ['name' => 'Human Resources', 'code' => 'HR'],
            ['name' => 'Engineering', 'code' => 'ENG'],
            ['name' => 'Finance', 'code' => 'FIN'],
            ['name' => 'Marketing', 'code' => 'MKT'],
            ['name' => 'Sales', 'code' => 'SAL'],
            ['name' => 'Operations', 'code' => 'OPS'],
            ['name' => 'Customer Support', 'code' => 'SUP'],
            ['name' => 'Information Technology', 'code' => 'IT'],
            ['name' => 'Legal', 'code' => 'LEG'],
            ['name' => 'Administration', 'code' => 'ADM'],
        ];

        $departments = collect($departments)
            ->map(fn (array $department) => Department::create($department));

        $titles = [
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
            'Operations Specialist',
            'Operations Manager',
            'Support Specialist',
            'Support Manager',
            'System Administrator',
            'IT Specialist',
            'Project Manager',
            'Business Analyst',
            'Legal Advisor',
        ];

          /*
         * For every Department... create every job title for that department
         */
        foreach ($departments as $department) {
            foreach ($titles as $title) {
                JobTitle::create([
                    'department_id' => $department->id,
                    'name' => $title,
                    'grade' => $this->gradeForTitle($title),
                ]);
            }
        }
    }

    //Determine the  job grade based on the title
    private function gradeForTitle(string $title): string
    {
        return match (true) {
            str_contains($title, 'Junior') => 'Junior',
            str_contains($title, 'Senior') => 'Senior',
            str_contains($title, 'Lead') => 'Lead',
            str_contains($title, 'Manager') => 'Manager',
            default => 'Mid',
        };
    }
}
