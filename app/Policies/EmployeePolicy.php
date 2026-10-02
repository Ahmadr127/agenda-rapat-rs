<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;

class EmployeePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Employee $employee): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->can('employees.manage') || $user->can('employees.manage-all');
    }

    public function update(User $user, Employee $employee): bool
    {
        if ($user->can('employees.manage-all')) {
            return true;
        }

        return $user->can('employees.manage')
            && $user->unitId() !== null
            && $employee->unit_id === $user->unitId();
    }

    public function delete(User $user, Employee $employee): bool
    {
        return $this->update($user, $employee);
    }
}
