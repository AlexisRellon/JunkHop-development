<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DashboardStatistic;
use App\Models\User;
use App\Models\Junkshop;
use Carbon\Carbon;

class UpdateDashboardStatistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dashboard:update-statistics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update dashboard statistics daily';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $date = Carbon::today()->toDateString();
        $totalUsers = User::count();
        $onlineUsers = User::where('is_online', true)->count(); // Assuming you have an `is_online` field
        $totalJunkshops = Junkshop::count();

        DashboardStatistic::updateOrCreate(
            ['date' => $date],
            [
                'total_users' => $totalUsers,
                'online_users' => $onlineUsers,
                'total_junkshops' => $totalJunkshops,
            ]
        );

        $this->info('Dashboard statistics updated successfully.');
    }
}
