<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Seed HRFlow roles and permissions based on the access matrix.
     */
    public function run(): void
    {
        // Clear Spatie's cached permissions before seeding.
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Organization
            'departments.create',
            'departments.update',
            'departments.delete',

            'job-titles.create',
            'job-titles.update',
            'job-titles.delete',

            // Employees
            'employees.create',
            'employees.update',
            'employees.terminate',
            'employees.salary-history.view',
            'employees.documents.create',

            // Leave
            'leave-requests.create',
            'leave-requests.approve',
            'leave-requests.reject',
            'leave-requests.delete',
            'leave-requests.balance.view',

            // Attendance
            'attendance.create',
            'attendance.update',

            // Payroll - Salary Structures
            'salary-structures.view',
            'salary-structures.create',
            'salary-structures.update',

            // Payroll - Payroll Runs
            'payroll-runs.view',
            'payroll-runs.create',
            'payroll-runs.process',
            'payroll-runs.finalize',
            'payroll-runs.payslips.view',

            // Payroll - Payslips & Deductions
            'payslips.view',
            'payslips.my.view',
            'payslips.deductions.create',

            // Performance
            'performance-cycles.create',
            'performance-cycles.activate',
            'performance-cycles.close',

            'performance-reviews.create',
            'performance-reviews.update',

            // Documents
            'viewDocuments',
            'uploadDocument',
        ];

        // Create all permissions with 'api' guard
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'api');
        }

        /*
         * HR Admin
         * Has full access to all permissions.
         */
        $hrAdmin = Role::findOrCreate('hr-admin', 'api');
        $hrAdmin->syncPermissions(
            Permission::where('guard_name', 'api')->get()
        );

        /*
         * Department Manager
         */
        $manager = Role::findOrCreate('department-manager', 'api');
        $manager->syncPermissions([
            'leave-requests.approve',
            'leave-requests.reject',
            'performance-reviews.create',
            'performance-reviews.update',
            'viewDocuments',
            'uploadDocument',
        ]);

        /*
         * Employee
         * Can submit/delete own leaves, view balance, and view own payslips.
         */
        $employee = Role::findOrCreate('employee', 'api');
        $employee->syncPermissions([
            'leave-requests.create',
            'leave-requests.delete',
            'leave-requests.balance.view',
            'payslips.my.view',
            'payslips.view', // Ownership checked via Policy for own payslip view
        ]);

        // Clear the cache again after creating/assigning permissions.
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
