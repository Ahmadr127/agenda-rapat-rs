<?php

namespace App\Console\Commands;

use App\Helpers\NameConverter;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SyncEmployeeUsers extends Command
{
    protected $signature   = 'employees:sync-users
                                {--force : Also re-sync employees that already have a user_id}';

    protected $description = 'Create a User account for every Employee that does not have one yet.';

    public function handle(): int
    {
        $force = $this->option('force');

        $query = Employee::query();

        if (! $force) {
            $query->whereNull('user_id');
        }

        $employees = $query->orderBy('id')->get();

        if ($employees->isEmpty()) {
            $this->info('All employees already have an account. Use --force to re-sync.');
            return self::SUCCESS;
        }

        $this->info("Processing {$employees->count()} employee(s)…");

        $bar     = $this->output->createProgressBar($employees->count());
        $created = 0;
        $updated = 0;

        DB::transaction(function () use ($employees, $bar, &$created, &$updated, $force) {
            foreach ($employees as $employee) {
                $isNew = is_null($employee->user_id);

                $user = $this->syncUser($employee->user_id, $employee->full_name);

                $employee->user_id = $user->id;
                $employee->save();

                $isNew ? $created++ : $updated++;

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['Metric', 'Count'],
            [
                ['Accounts created', $created],
                ['Accounts updated', $updated],
            ]
        );

        $this->info('Done.');

        return self::SUCCESS;
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Create or update the linked User record using NameConverter for
     * consistent name/email derivation.
     */
    private function syncUser(?int $userId, string $fullName): User
    {
        $converted = NameConverter::convert($fullName, 'rsazra.co.id');

        $name      = $converted['name'] ?: 'user';
        $baseEmail = $converted['email'];
        $email     = $baseEmail;
        $counter   = 1;

        // Ensure email uniqueness among other users
        while (
            User::where('email', $email)
                ->when($userId, fn ($q) => $q->where('id', '!=', $userId))
                ->exists()
        ) {
            $local = substr($baseEmail, 0, strrpos($baseEmail, '@'));
            $email = $local . $counter . '@rsazra.co.id';
            $counter++;
        }

        if ($userId) {
            $user = User::findOrFail($userId);
            $user->update(['name' => $name, 'email' => $email]);

            return $user;
        }

        return User::create([
            'name'     => $name,
            'email'    => $email,
            'password' => Hash::make('rsazra2026'),
        ]);
    }
}
