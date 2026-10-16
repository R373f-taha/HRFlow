<?php

namespace  Modules\Performance\Policies;

use Modules\Auth\Models\User;
use Modules\Employees\Models\Employee;

class PerformanceCyclePolicy
{
    public function create(User $user): bool{


    return $user->can('performance-cycles.create');
    }

    public function activate(User $user){

    return $user->can('performance-cycles.activate');
    }

    public function close(User $user){

    return $user->can('performance-cycles.close');

    }


    public function viewPerformanceHistory(User $user,Employee $employee){

      $ManagerEmployee=$user->employee;

       if($user->hasRole($user)=='hr-admin')
        return true;

     if( $ManagerEmployee &&  $ManagerEmployee->manager_id==null&&  $ManagerEmployee->department_id==$employee->department->id)

        return true;



    }
}
