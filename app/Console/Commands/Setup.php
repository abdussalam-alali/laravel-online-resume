<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\Fluent\Concerns\Has;

class Setup extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'myresume:setup {--dummy-data} {--fresh}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'My Online Resume Setup';

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
        $options = $this->options();
        $functions = ['callMigrate','createAdminUser','callSeeders'];
        foreach($functions as $func)
        {
            if(!$this->$func($options))
                return Command::FAILURE;
        }
        $this->info("success");
        return Command::SUCCESS;
    }
    private function callMigrate($options) {
        if($options['fresh'])
        {
            $this->error('Warning: your data will be erased!');
            $confirm = $this->confirm('Are you sure?',false);
            if(!$confirm) {
                $this->info('Canceled');
                return false;
            }
        }
        $this->info('Calling migrations');
        try {
            if($options['fresh']){

                Artisan::call('migrate:fresh');
            }
            else
                Artisan::call('migrate');
        }catch (\Exception $e) {
            $this->error('Error');
            $this->error($e);
            return false;
        }
        return true;
    }
    private function callSeeders($options){
        try{
            if($options['dummy-data'])
            {
                $this->info('Seeding...');
                Artisan::call('db:seed');
            }
        }catch(\Exception $e)
        {
            $this->error($e);
            return false;
        }
        return true;

    }
    private function getMainInfo() {

        $this->info('Enter Your Info');
        $name = $this->ask('your name');
        $title = $this->ask('Web Site Title');
        $username = $this->ask('admin email: ');
        $password = $this->secret('admin password: ');

        return [
            'name' =>$name,
            'title'=>$title,
            'email'=>$username,
            'password'=>$password,
        ];
    }

    private function createAdminUser()
    {
        try{

            $info = $this->getMainInfo();
            $user = new User();
            $user->name = $info['name'];
            $user->password = Hash::make($info['password']);
            $user->email = $info['email'];
            $user->save();

            $settings = Setting::where('name','site_name')
                ->get()
                ->first();

            if($settings)
                $settings->value = $info['name'];
            else
            {
                $settings = new Setting();
                $settings->name = 'site_name';
                $settings->value = $info['title'];
            }
            $settings->save();
        }catch(\Exception $e)
        {
            $this->error($e);
            return false;
        }
        return true;
    }
}
