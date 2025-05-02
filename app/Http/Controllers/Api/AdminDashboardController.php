<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\User;
use App\Models\Junkshop;
use App\Models\Bid;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class AdminDashboardController extends BaseController
{
    /**
     * Get dashboard statistics
     */
    public function getStatistics(): JsonResponse
    {
        try {            // Get total users and active users
            $totalUsers = User::count();
            
            // For now, consider users active if they were created in the last 30 days
            // TODO: Update this when last_login_at tracking is implemented
            $activeUsers = User::where('created_at', '>=', Carbon::now()->subDays(30))->count();

            // Get total junkshops
            $totalJunkshops = Junkshop::count();

            // Get bid statistics
            $bidStats = [
                'total' => Bid::count(),
                'pending' => Bid::where('status', 'pending')->count(),
                'accepted' => Bid::where('status', 'accepted')->count(),
                'rejected' => Bid::where('status', 'rejected')->count(),
            ];

            // Get recent activities
            $recentActivities = collect();

            // Add recent user registrations
            User::orderBy('created_at', 'desc')
                ->take(5)
                ->get()
                ->each(function ($user) use ($recentActivities) {
                    $recentActivities->push([
                        'type' => 'user',
                        'description' => "New user registered: {$user->name}",
                        'timestamp' => $user->created_at->diffForHumans(),
                        'created_at' => $user->created_at
                    ]);
                });

            // Add recent bids
            Bid::with(['junkshop', 'item'])
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get()
                ->each(function ($bid) use ($recentActivities) {
                    $recentActivities->push([
                        'type' => 'bid',
                        'description' => "New bid from {$bid->junkshop->name} for {$bid->item->name}",
                        'timestamp' => $bid->created_at->diffForHumans(),
                        'created_at' => $bid->created_at
                    ]);
                });

            // Sort activities by timestamp and take 15 most recent
            $recentActivities = $recentActivities
                ->sortByDesc('created_at')
                ->take(15)
                ->values()
                ->map(function ($activity) {
                    unset($activity['created_at']);
                    return $activity;
                });

            // User growth data (last 6 months)
            $userGrowth = collect();
            for ($i = 5; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $userGrowth->push([
                    'month' => $date->format('M'),
                    'count' => User::whereYear('created_at', $date->year)
                        ->whereMonth('created_at', $date->month)
                        ->count()
                ]);
            }

            // System health (using server statistics)
            $systemHealth = [
                'status' => $this->getSystemHealthStatus(),
                'uptime' => $this->getSystemUptime(),
            ];

            return $this->sendResponse([
                'statistics' => [
                    'users' => [
                        'total' => $totalUsers,
                        'active' => $activeUsers,
                    ],
                    'junkshops' => [
                        'total' => $totalJunkshops,
                    ],
                    'bids' => $bidStats,
                ],
                'recentActivities' => $recentActivities,
                'userGrowth' => $userGrowth,
                'systemHealth' => $systemHealth,
            ], 'Dashboard statistics retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendError('Error retrieving dashboard statistics', $e->getMessage(), 500);
        }
    }

    /**
     * Get all activities
     */    public function getActivities(): JsonResponse
    {
        try {
            $activities = collect();

            // User activities (registrations and logins)
            User::orderBy('created_at', 'desc')
                ->get()
                ->each(function ($user) use ($activities) {
                    // Registration
                    $activities->push([
                        'type' => 'user',
                        'description' => "New user registered: {$user->name}",
                        'timestamp' => $user->created_at->diffForHumans(),
                        'created_at' => $user->created_at
                    ]);

                    // Last login if available
                    if ($user->last_login_at) {
                        $activities->push([
                            'type' => 'user',
                            'description' => "User {$user->name} logged in",
                            'timestamp' => $user->last_login_at->diffForHumans(),
                            'created_at' => $user->last_login_at
                        ]);
                    }
                });

            // Junkshop activities
            Junkshop::orderBy('created_at', 'desc')
                ->get()
                ->each(function ($junkshop) use ($activities) {
                    // Registration
                    $activities->push([
                        'type' => 'junkshop',
                        'description' => "New junkshop registered: {$junkshop->name}",
                        'timestamp' => $junkshop->created_at->diffForHumans(),
                        'created_at' => $junkshop->created_at
                    ]);
                });

            // Bid activities
            Bid::with(['junkshop', 'item'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->each(function ($bid) use ($activities) {
                    // New bid
                    $activities->push([
                        'type' => 'transaction',
                        'description' => "New bid from {$bid->junkshop->name} for {$bid->item->name}",
                        'timestamp' => $bid->created_at->diffForHumans(),
                        'created_at' => $bid->created_at
                    ]);

                    // Bid status changes
                    if ($bid->updated_at->gt($bid->created_at)) {
                        $activities->push([
                            'type' => 'transaction',
                            'description' => "Bid status updated to {$bid->status} for {$bid->item->name}",
                            'timestamp' => $bid->updated_at->diffForHumans(),
                            'created_at' => $bid->updated_at
                        ]);
                    }
                });

            // Sort all activities by timestamp
            $activities = $activities
                ->sortByDesc('created_at')
                ->values()
                ->map(function ($activity) {
                    unset($activity['created_at']);
                    return $activity;
                });

            return $this->sendResponse($activities, 'Activities retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendError('Error retrieving activities', $e->getMessage(), 500);
        }
    }

    /**
     * Get system health status
     */
    private function getSystemHealthStatus(): string
    {
        // You can implement more sophisticated health checks here
        return 'Excellent';
    }

    /**
     * Get system uptime
     */
    private function getSystemUptime(): string
    {
        // For now, return a placeholder. You can implement actual uptime tracking later
        return '99.9%';
    }
}
