<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Seed HRFlow roles and permissions.
     *
     * The permissions below are derived from the access matrix
     * defined in HRFlow_Final_Spec.pdf.
     *
     * Spatie is responsible for HOW these permissions are stored
     * and assigned.
     */
    public function run(): void
    {
        // Clear Spatie's cached permissions before seeding.
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        /*
         * HRFlow permissions.
         *
         * We intentionally do NOT create permissions for every GET
         * endpoint. Some endpoints are available to any authenticated
         * user according to the specification.
         */
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

            // Payroll
            'salary-structures.create',
            'salary-structures.update',

            'payroll-runs.create',
            'payroll-runs.process',
            'payroll-runs.finalize',

            'payslips.deductions.create',

            // Performance
            'performance-cycles.create',
            'performance-cycles.activate',
            'performance-cycles.close',

            'performance-reviews.create',
            'performance-reviews.update',
        ];

        /*
         * Create permissions first.
         *
         * This is important because roles will receive these
         * existing permissions below.
         */
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'api');
        }

        /*
         * HR Admin
         *
         * The specification explicitly states that HR Admin
         * has full system access.
         *
         * Therefore this role receives all HRFlow permissions.
         */
        $hrAdmin = Role::findOrCreate('hr-admin', 'api');

        $hrAdmin->syncPermissions(
            Permission::where('guard_name', 'api')->get()
        );

        /*
         * Department Manager
         *
         * Manager can:
         * - approve/reject leave requests
         * - create/update performance reviews
         *
         * Department-level restrictions will be enforced
         * later through Policies.
         */
        $manager = Role::findOrCreate('department-manager', 'api');

        $manager->syncPermissions([
            'leave-requests.approve',
            'leave-requests.reject',
            'performance-reviews.create',
            'performance-reviews.update',
        ]);

        /*
         * Employee
         *
         * Employee can:
         * - submit leave requests
         * - cancel pending leave requests
         * - view their leave balance
         *
         * Ownership of employee/payslip/performance data will
         * be enforced through Policies.
         */
        $employee = Role::findOrCreate('employee', 'api');

        $employee->syncPermissions([
            'leave-requests.create',
            'leave-requests.delete',
            'leave-requests.balance.view',
        ]);

        // Clear the cache again after creating/assigning permissions.
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
