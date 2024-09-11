<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserExerciseSelection;

class ResetDailyExerciseSelections extends Command
{
    protected $signature = 'reset:daily-exercise-selections';
    protected $description = 'Reset daily exercise selections for all users';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        UserExerciseSelection::query()->delete();
        $this->info('Daily exercise selections have been reset.');
    }
}
