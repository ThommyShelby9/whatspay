<?php

namespace App\Console\Commands;

use App\Consts\Util;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CloseEndedTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:close-ended';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ferme automatiquement les campagnes dont la date de fin est atteinte';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();

        $tasks = Task::where('status', '!=', 'CLOSED')
            ->where('enddate', '<', $now)
            ->get();

        foreach ($tasks as $task) {
            $task->update([
                'status' => Util::TASKS_STATUSES['CLOSED']
            ]);
        }

        $this->info("{$tasks->count()} campagne(s) clôturée(s).");
        return 0;
    }
}
