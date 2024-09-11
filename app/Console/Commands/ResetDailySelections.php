<?php

namespace App\Console\Commands;

use App\Models\UserMealSelection;
use Illuminate\Console\Command;

class ResetDailySelections extends Command
{
    protected $signature = 'reset:daily-selections';
    protected $description = 'Reset daily meal selections for all users';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        UserMealSelection::query()->delete();
        $this->info('Daily meal selections have been reset.');
    }
}
