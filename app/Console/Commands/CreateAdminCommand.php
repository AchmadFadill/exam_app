<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminCommand extends Command
{
    protected $signature = 'app:create-admin
        {email? : Email akun admin}
        {--name= : Nama admin}
        {--password= : Password admin}
        {--no-force-change : Jangan wajibkan ganti password di login pertama}';

    protected $description = 'Buat atau perbarui akun admin dengan aman via CLI';

    public function handle(): int
    {
        $email = $this->argument('email') ?: $this->ask('Email admin');
        $name = $this->option('name') ?: $this->ask('Nama admin', 'Administrator');
        $password = $this->option('password') ?: $this->secret('Password admin (min. 8 karakter)');

        $validator = Validator::make([
            'email' => $email,
            'name' => $name,
            'password' => $password,
        ], [
            'email' => ['required', 'email'],
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        if (!$this->option('password')) {
            $confirmPassword = $this->secret('Konfirmasi password');
            if ($password !== $confirmPassword) {
                $this->error('Konfirmasi password tidak sama.');
                return self::FAILURE;
            }
        }

        $forceChange = !$this->option('no-force-change');
        $existing = User::where('email', $email)->first();

        if ($existing) {
            $existing->update([
                'name' => $name,
                'role' => 'admin',
                'password' => Hash::make($password),
                'must_change_password' => $forceChange,
            ]);

            $this->info('Akun admin berhasil diperbarui.');
            $this->line("Email: {$existing->email}");
            $this->line('Wajib ganti password: ' . ($forceChange ? 'YA' : 'TIDAK'));
            return self::SUCCESS;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'role' => 'admin',
            'password' => Hash::make($password),
            'must_change_password' => $forceChange,
        ]);

        $this->info('Akun admin berhasil dibuat.');
        $this->line("Email: {$user->email}");
        $this->line('Wajib ganti password: ' . ($forceChange ? 'YA' : 'TIDAK'));

        return self::SUCCESS;
    }
}
