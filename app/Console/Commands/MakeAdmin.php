<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Console\Commands;

use App\Models\User;
use App\Support\Audit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class MakeAdmin extends Command
{
    protected $signature = 'okulands:admin
                            {email : Email address of the admin account}
                            {--name= : Display name (only used when creating)}
                            {--generate : Generate a strong random password and print it once}
                            {--reset-2fa : Only switch off two-factor for this existing admin (lost phone and recovery codes); they set it up again at next sign-in}';

    protected $description = 'Create an admin account, or reset the password of an existing one, without ever using a default password.';

    public function handle(): int
    {
        $email = strtolower(trim($this->argument('email')));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('That is not a valid email address.');

            return self::FAILURE;
        }

        if ($this->option('reset-2fa')) {
            $admin = User::where('email', $email)->where('role', 'admin')->first();

            if (! $admin) {
                $this->error('No admin account uses that email address.');

                return self::FAILURE;
            }

            $admin->forceFill(['two_factor_secret' => null, 'two_factor_recovery_codes' => null, 'two_factor_confirmed_at' => null])->save();
            Audit::log('admin.two_factor_reset', $admin, 'Via the okulands:admin command', [], $admin->id);

            $this->info('Two-factor authentication was reset for '.$email.'. They must set it up again the next time they sign in.');

            return self::SUCCESS;
        }

        $generated = null;

        if ($this->option('generate')) {
            $generated = Str::password(16, symbols: false);
            $password = $generated;
        } else {
            $password = (string) $this->secret('Password (min 8 characters, upper + lower case and a number)');
            if ($password !== (string) $this->secret('Confirm password')) {
                $this->error('The passwords do not match.');

                return self::FAILURE;
            }
        }

        $check = Validator::make(['password' => $password], ['password' => [Password::min(8)->mixedCase()->numbers()]]);
        if ($check->fails()) {
            $this->error($check->errors()->first('password'));

            return self::FAILURE;
        }

        $user = User::where('email', $email)->first();
        $creating = ! $user;

        $user ??= new User(['name' => $this->option('name') ?: 'Administrator', 'email' => $email]);
        $user->password = $password;
        $user->forceFill(['role' => 'admin', 'status' => 'active', 'email_verified_at' => $user->email_verified_at ?? now()])->save();

        Audit::log($creating ? 'admin.created' : 'admin.password_reset', $user, 'Via the okulands:admin command', [], $user->id);

        $this->info(($creating ? 'Admin account created: ' : 'Admin password updated: ').$email);

        if ($generated) {
            $this->warn('Generated password (shown once, store it safely): '.$generated);
        }

        $this->line('Two-factor authentication is optional; they can turn it on from Security whenever they like.');

        return self::SUCCESS;
    }
}
