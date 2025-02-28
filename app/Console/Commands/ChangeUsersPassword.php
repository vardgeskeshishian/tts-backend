<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ChangeUsersPassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:change-password {--email=} {--password=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Change user password to';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $email = $this->option('email');
        $password = $this->option('password');

        $user = User::where('email', $email)->first();
        if (!$user) {
            $this->output->error('user not found');

            return;
        }
        $user->password = bcrypt($password);
        $user->save();

        $this->output->text($email);
        $this->output->text($password);
        $this->output->text($user->password);
    }
}
