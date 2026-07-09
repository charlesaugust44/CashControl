<?php

namespace App\Console\Commands;

use App\Models\Unity;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class CreateAdminCommand extends Command
{
    protected $signature = 'admin:create';

    protected $description = 'Create the first admin user';

    public function handle(): int
    {
        $this->info('Creating admin user...');

        $name = $this->ask('Name');
        $email = $this->ask('Email');
        $password = $this->secret('Password');
        $passwordConfirmation = $this->secret('Confirm password');

        if ($password !== $passwordConfirmation) {
            $this->error('Passwords do not match.');
            return self::FAILURE;
        }

        $validator = validator([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', Password::defaults()],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return self::FAILURE;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'admin',
            'approved_at' => now(),
        ]);

        $unity = Unity::firstOrCreate(
            ['name' => 'Default'],
            ['description' => 'Default unity']
        );

        $user->unities()->attach($unity->id);

        $this->info("Admin user '{$user->name}' created successfully!");
        $this->info("Email: {$user->email}");
        $this->info("Assigned to unity: {$unity->name}");

        return self::SUCCESS;
    }
}
