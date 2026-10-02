<?php

namespace App\Policies;

use App\Models\BankSoal;
use App\Models\User;

class BankSoalPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, BankSoal $bankSoal): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->can('bank-soals.manage');
    }

    public function update(User $user, BankSoal $bankSoal): bool
    {
        return $user->can('bank-soals.manage');
    }

    public function delete(User $user, BankSoal $bankSoal): bool
    {
        return $user->can('bank-soals.manage');
    }
}
