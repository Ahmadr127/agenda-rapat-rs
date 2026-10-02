<?php

namespace App\Policies;

use App\Models\Agenda;
use App\Models\User;

class AgendaPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Agenda $agenda): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->can('agendas.manage') || $user->can('agendas.manage-all');
    }

    public function update(User $user, Agenda $agenda): bool
    {
        if ($user->can('agendas.manage-all')) {
            return true;
        }

        return $user->can('agendas.manage')
            && $user->unitId() !== null
            && $agenda->unit_id === $user->unitId();
    }

    public function delete(User $user, Agenda $agenda): bool
    {
        return $this->update($user, $agenda);
    }
}
