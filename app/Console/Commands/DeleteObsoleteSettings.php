<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class deleteObsoleteSettings extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete-obsolete-settings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove site settings that are no longer in use.';

    /**
     * Create a new command instance.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $this->info('*********************');
        $this->info('* DELETE OBSOLETE SITE SETTINGS *');
        $this->info('*********************'."\n");

        $this->line("Removing obsoleted site settings...non-existing entries will be skipped.\n");

        $this->deleteSiteSetting('free_myos_max_number', 0, 'Optional limit to the number of free MYOs a user can create. Enter "0" to allow users infinite free MYOs.');

        $this->deleteSiteSetting('free_myos_rarity', 0, 'ID of the max rarity a free MYO allows. Enter "0" for no limitations.');

        $this->deleteSiteSetting('free_myos_require_subtype', 0, '0: Subtypes are optional for free MYOs, 1: Subtypes are mandatory for free MYOs. ');

        $this->deleteSiteSetting('free_myos_is_giftable', 1, '0: MYOs cannot be gifted, 1: MYOs can be gifted. ');

        $this->deleteSiteSetting('free_myos_is_tradeable', 1, '0: MYOs cannot be traded, 1: MYOs can be traded. ');

        $this->deleteSiteSetting('free_myos_is_resellable', 0, '0: MYOs cannot be resold, 1: MYOs can be resold. ');

        $this->line("\nObsoleted settings have been removed!");
    }

    /**
     * Add a site setting.
     *
     * Example usage:
     * $this->deleteSiteSetting("site_setting_key");
     *
     * @param string $key
     */
    private function deleteSiteSetting($key) {
        if (DB::table('site_settings')->where('key', $key)->exists()) {
            DB::table('site_settings')->where('key', $key)->delete();
            $this->info('Deleted:   '.$key);
        } else {
            $this->line('Skipped: '.$key);
        }
    }
}