<?php

namespace Modules\Performance\Policies;

use Illuminate\Auth\Access\Response;
use Modules\Auth\Models\User;
use Modules\Employees\Models\Employee;
use Modules\Performance\Models\PerformanceCycle;
use Modules\Performance\Models\PerformanceReview;

class PerformanceReviewPolicy
{

public function viewAny(User $user): bool
    {
        return $user->hasRole('hr-admin') || $user->employee?->manager_id== null;//it allows only for hr-admin or manager to see reviews
        //note:when the employee is manager then he has a manager_id with null value
    }
   /**
     * Determine if the user (Manager) can create a review for a specific employee.
     */
    public function create(User $user, Employee $targetEmployee, PerformanceCycle $cycle)
    {
        $managerEmployee = $user->employee;

        if (! $managerEmployee) {
            return false;
        }
        if ($managerEmployee->manager_id !== null) {
            return false;
        }
         if ($cycle->status === 'draft') {
             return Response::deny('You cannot create a performance review for a draft cycle.');
        }


        if ($cycle->status !== 'active') {
             return Response::deny('You cannot create a performance review for a closed cycle.');
        }

        if ($targetEmployee->id === $managerEmployee->id) {
            return Response::deny('You cannot create a performance review for yourself.');
        }

        if ($targetEmployee->department_id !== $managerEmployee->department_id) {
            return Response::deny('You can only review employees within your department.');
        }

        return true;
    }

    /**
     * Manager can update review ONLY if the cycle is still active.
     */
    public function update(User $user, PerformanceReview $review)
    {  //  var_dump($review->performanceCycle);

        $managerEmployee = $user->employee;

        if (! $managerEmployee ) {
            return false;
        }


        if ($review->performanceCycle->status !== 'active') {
             return Response::deny('You cannot create a performance review for a closed cycle.');
        }

        if($review->manager_id !== $managerEmployee->id){

            return Response::deny('You can only update reviews that you have created.');
        }



        if ($review->performanceCycle->status !== 'active') {
             return Response::deny('You cannot update a performance review for a closed cycle.');
        }

        return true;
    }





    public function viewPerformanceHistory(User $user,Employee $employee){

      $ManagerEmployee=$user->employee;

       if($user->hasRole('hr-admin'))
        return true;

     if( $ManagerEmployee &&  $ManagerEmployee->manager_id==null&&  $ManagerEmployee->department_id==$employee->department->id)

        return true;



    }
}
