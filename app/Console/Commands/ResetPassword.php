<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ResetPassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'myresume:resetpass';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset admin password';

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
     * @return int
     */
    public function handle()
    {
        $user = $this->searchForUser();
        if($user)
            $this->resetPassword($user);
        else
            $this->error("User not found!");
        return Command::SUCCESS;

    }
    private function searchForUser()
    {
        $email = $this->ask("Enter admin email");
        $user = User::where('email',$email)->first();
        return $user;
    }
    private function resetPassword($user)
    {
        $password = $this->secret("Enter your password");
        $passwordConfirmation = $this->secret("Confirm your password");

        if($password==$passwordConfirmation)
        {
            $user->password = $password;
            $user->save();
            $this->info("Password reset successfully!");
        }
        else
        {
            $this->error("password confirmation doesn't match password!");
        }
    }
}
