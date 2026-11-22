<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

class CreateAdmin extends Command
{
    protected $signature = 'admin:create
        {--name= : Display name}
        {--email= : Login email}
        {--password= : Password (prompted securely when omitted)}';

    protected $description = 'Create (or reset) an admin account for the /admin lead management panel';

    public function handle(): int
    {
        $name = $this->option('name') ?: text('Name', default: 'Administrator', required: true);
        $email = $this->option('email') ?: text('Email', required: true);
        $plain = $this->option('password') ?: password('Password (min 10 characters, letters, numbers & symbols)', required: true);

        $validator = Validator::make(
            ['name' => $name, 'email' => $email, 'password' => $plain],
            [
                'name' => ['required', 'string', 'max:120'],
                'email' => ['required', 'email', 'max:190'],
                'password' => ['required', Password::min(10)->letters()->numbers()->symbols()],
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        // is_admin is deliberately not mass-assignable on User, so set it explicitly.
        $user = User::firstOrNew(['email' => strtolower($email)]);
        $user->forceFill([
            'name' => $name,
            'password' => $plain,
            'is_admin' => true,
            'email_verified_at' => now(),
        ])->save();

        $this->info(($user->wasRecentlyCreated ? 'Created' : 'Updated')." admin account {$user->email}.");

        return self::SUCCESS;
    }
}
