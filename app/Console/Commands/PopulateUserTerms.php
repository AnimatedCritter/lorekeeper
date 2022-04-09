<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App;
use DB;
use Carbon\Carbon;
use App\Services\UserService;
use App\Models\User\User;
use App\Models\User\UserTerms;

class PopulateUserTerms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'populate-user-terms';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates data for users\', who joined before the extension\'s instiallation, to the user_terms table in the database.';

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
        //
        $this->info('***********************');
        $this->info('* POPULATE USER TERMS *');
        $this->info('***********************'."\n");

        $this->line('Retriveing Users\' IDs...');
        $user = DB::table('users')->get();

        $this->line('Populating user_terms table...');
        $this->line(" ");

        foreach($user as $user)
        {
            if(!DB::table('user_terms')->where('user_id', $user->id)->exists()) {
                // Create a new row for the user
                $this->line('New row');
                DB::table('user_terms')->insert([
                    [
                        'user_id' => $user->id
                    ]
                ]);
            }
        }

        $this->info('Population Successful!');
        $this->line(" ");

        $count = DB::table('user_terms')->count();

        $this->line("{$count} users are now in the user_terms table.");
    }
}
