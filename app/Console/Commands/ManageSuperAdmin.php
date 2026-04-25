<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ManageSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'legidash:super-admin 
                            {action : The action to perform (grant, revoke, list)}
                            {email? : The user email (required for grant/revoke)}';

    /**
     * The console command description.
     */
    protected $description = 'Manage LegiDash platform super admin access';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $action = $this->argument('action');
        $email = $this->argument('email');

        return match ($action) {
            'grant' => $this->grantAccess($email),
            'revoke' => $this->revokeAccess($email),
            'list' => $this->listAdmins(),
            default => $this->invalidAction($action),
        };
    }

    protected function grantAccess(?string $email): int
    {
        if (!$email) {
            $this->error('Email is required for grant action.');
            return 1;
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email '{$email}' not found.");
            return 1;
        }

        if ($user->is_super_admin) {
            $this->warn("{$user->name} is already a super admin.");
            return 0;
        }

        $user->update(['is_super_admin' => true]);
        $this->info("✅ Granted super admin access to {$user->name} ({$email})");
        $this->line("   They can now access the Platform Admin panel at /platform-admin");

        return 0;
    }

    protected function revokeAccess(?string $email): int
    {
        if (!$email) {
            $this->error('Email is required for revoke action.');
            return 1;
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email '{$email}' not found.");
            return 1;
        }

        if (!$user->is_super_admin) {
            $this->warn("{$user->name} is not a super admin.");
            return 0;
        }

        $user->update(['is_super_admin' => false]);
        $this->info("❌ Revoked super admin access from {$user->name} ({$email})");

        return 0;
    }

    protected function listAdmins(): int
    {
        $admins = User::where('is_super_admin', true)->get();

        if ($admins->isEmpty()) {
            $this->warn('No super admins found.');
            $this->line('');
            $this->line('To grant super admin access, run:');
            $this->line('  php artisan legidash:super-admin grant <email>');
            return 0;
        }

        $this->info('LegiDash Platform Super Admins:');
        $this->table(
            ['Name', 'Email', 'Created'],
            $admins->map(fn($u) => [
                $u->name,
                $u->email,
                $u->created_at->format('M j, Y'),
            ])
        );

        return 0;
    }

    protected function invalidAction(string $action): int
    {
        $this->error("Invalid action: {$action}");
        $this->line('');
        $this->line('Available actions:');
        $this->line('  grant <email>  - Grant super admin access to a user');
        $this->line('  revoke <email> - Revoke super admin access from a user');
        $this->line('  list           - List all super admins');

        return 1;
    }
}

