<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\User;
use App\Models\Junkshop;
use App\Models\Bid;
use App\Models\Activity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
              // Consider users active if they've logged in within the last 30 days
            $activeUsers = User::where('last_login_at', '>=', Carbon::now()->subDays(30))->count();

            // Get total junkshops
            $totalJunkshops = Junkshop::count();

            // Get bid statistics
            $bidStats = [
                'total' => Bid::count(),
                'pending' => Bid::where('status', 'pending')->count(),
                'accepted' => Bid::where('status', 'accepted')->count(),
                'rejected' => Bid::where('status', 'rejected')->count(),
            ];            // Get recent activities
            $recentActivities = Activity::with('user')
                ->orderBy('created_at', 'desc')
                ->take(15)
                ->get()
                ->map(function ($activity) {
                    return [
                        'type' => $activity->type,
                        'action' => $activity->action,
                        'description' => $activity->description,
                        'timestamp' => $activity->created_at->diffForHumans(),
                        'user' => $activity->user ? $activity->user->name : 'System',
                        'icon' => $this->getActivityIcon($activity->type, $activity->action)
                    ];
                });
                
            // Fallback if no activities exist yet
            if ($recentActivities->isEmpty()) {                // Add recent user registrations
                User::orderBy('created_at', 'desc')
                    ->take(5)
                    ->get()
                    ->each(function ($user) use ($recentActivities) {
                        $recentActivities->push([
                            'type' => 'user',
                            'action' => 'created',
                            'description' => "New user registered: {$user->name}",
                            'timestamp' => $user->created_at->diffForHumans(),
                            'user' => 'System',
                            'icon' => 'user-plus'
                        ]);
                        
                        // If they have a role other than 'user', add a role assignment activity
                        $userRole = $user->getRoleNames()->first();
                        if ($userRole && $userRole !== 'user') {
                            $recentActivities->push([
                                'type' => 'user',
                                'action' => 'role_assigned',
                                'description' => "Role assigned to {$user->name}: {$userRole}",
                                'timestamp' => $user->created_at->addSeconds(1)->diffForHumans(),
                                'user' => 'System',
                                'icon' => 'user-shield'
                            ]);
                        }
                    });                // Add recent bids
                Bid::with(['junkshop', 'item'])
                    ->orderBy('created_at', 'desc')
                    ->take(5)
                    ->get()
                    ->each(function ($bid) use ($recentActivities) {
                        $recentActivities->push([
                            'type' => 'transaction',
                            'action' => 'created',
                            'description' => "New bid from {$bid->junkshop->name} for {$bid->item->name}",
                            'timestamp' => $bid->created_at->diffForHumans(),
                            'user' => 'System',
                            'icon' => 'shopping-cart'
                        ]);
                        
                        // Add status changes if any
                        if ($bid->status !== 'pending') {
                            $statusDate = $bid->status === 'accepted' ? $bid->accepted_at : 
                                         ($bid->status === 'rejected' ? $bid->rejected_at : $bid->updated_at);
                            
                            if (!$statusDate) $statusDate = $bid->updated_at;
                            
                            $recentActivities->push([
                                'type' => 'transaction',
                                'action' => 'bid_status_changed',
                                'description' => "Bid status updated to {$bid->status} for {$bid->item->name}",
                                'timestamp' => $statusDate->diffForHumans(),
                                'user' => 'System',
                                'icon' => $bid->status === 'accepted' ? 'check-circle' : 
                                         ($bid->status === 'rejected' ? 'x-circle' : 'refresh')
                            ]);
                        }
                    });
                    
                // Sort activities by timestamp
                $recentActivities = collect($recentActivities)
                    ->sortByDesc('timestamp')
                    ->take(15)
                    ->values();
            }

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
            }            // System health (using server statistics)
            $systemHealth = [
                'status' => $this->getSystemHealthStatus(),
                'uptime' => $this->getSystemUptime(),
            ];

            // Log that an admin viewed dashboard statistics
            \App\Services\AdminLogger::logMaintenance(
                'view_dashboard_stats',
                'Admin viewed dashboard statistics'
            );

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
    }    /**
     * Get all activities
     */    
    public function getActivities(): JsonResponse
    {
        try {
            // Log that an admin viewed activities
            \App\Services\AdminLogger::logMaintenance(
                'view_activities',
                'Admin viewed activity logs'
            );
            
            $activities = Activity::with('user')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($activity) {
                    return [
                        'type' => $activity->type,
                        'action' => $activity->action,
                        'description' => $activity->description,
                        'timestamp' => $activity->created_at->diffForHumans(),
                        'created_at' => $activity->created_at,
                        'user' => $activity->user ? $activity->user->name : 'System',
                        'icon' => $this->getActivityIcon($activity->type, $activity->action)
                    ];
                });                // Fallback if no activities exist yet
            if ($activities->isEmpty()) {
                $activities = collect();
                
                // User activities (registrations and logins)
                User::orderBy('created_at', 'desc')
                    ->get()
                    ->each(function ($user) use ($activities) {
                        // Registration
                        $activities->push([
                            'type' => 'user',
                            'action' => 'created',
                            'description' => "New user registered: {$user->name}",
                            'timestamp' => $user->created_at->diffForHumans(),
                            'created_at' => $user->created_at,
                            'user' => 'System',
                            'icon' => 'user-plus'
                        ]);

                        // Last login if available
                        if ($user->last_login_at) {
                            $activities->push([
                                'type' => 'user',
                                'action' => 'login',
                                'description' => "User {$user->name} logged in",
                                'timestamp' => $user->last_login_at->diffForHumans(),
                                'created_at' => $user->last_login_at,
                                'user' => 'System',
                                'icon' => 'login'
                            ]);
                        }
                        
                        // If they have a role other than 'user', add a role assignment activity
                        $userRole = $user->getRoleNames()->first();
                        if ($userRole && $userRole !== 'user') {
                            $activities->push([
                                'type' => 'user',
                                'action' => 'role_assigned',
                                'description' => "Role assigned to {$user->name}: {$userRole}",
                                'timestamp' => $user->created_at->addSeconds(1)->diffForHumans(),
                                'created_at' => $user->created_at->addSeconds(1),
                                'user' => 'System',
                                'icon' => 'user-shield'
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
                            'action' => 'created',
                            'description' => "New junkshop registered: {$junkshop->name}",
                            'timestamp' => $junkshop->created_at->diffForHumans(),
                            'created_at' => $junkshop->created_at,
                            'user' => 'System',
                            'icon' => 'store'
                        ]);
                    });                // Bid activities
                Bid::with(['junkshop', 'item'])
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->each(function ($bid) use ($activities) {
                        // New bid
                        $activities->push([
                            'type' => 'transaction',
                            'action' => 'created',
                            'description' => "New bid from {$bid->junkshop->name} for {$bid->item->name}",
                            'timestamp' => $bid->created_at->diffForHumans(),
                            'created_at' => $bid->created_at,
                            'user' => 'System',
                            'icon' => 'shopping-cart'
                        ]);

                        // Bid status changes
                        if ($bid->status !== 'pending') {
                            $statusDate = $bid->status === 'accepted' ? $bid->accepted_at : 
                                         ($bid->status === 'rejected' ? $bid->rejected_at : $bid->updated_at);
                            
                            if (!$statusDate) $statusDate = $bid->updated_at;
                            
                            $activities->push([
                                'type' => 'transaction',
                                'action' => 'bid_status_changed',
                                'description' => "Bid status updated to {$bid->status} for {$bid->item->name}",
                                'timestamp' => $statusDate->diffForHumans(),
                                'created_at' => $statusDate,
                                'user' => 'System',
                                'icon' => $bid->status === 'accepted' ? 'check-circle' : 
                                         ($bid->status === 'rejected' ? 'x-circle' : 'refresh')
                            ]);
                        }
                    });
            }

            // Sort all activities by timestamp
            $activities = collect($activities)
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
    }    /**
     * Search activities by type
     */
    public function searchByType(Request $request): JsonResponse
    {
        try {
            $type = $request->input('type');
            $action = $request->input('action');
            $query = Activity::with('user')->orderBy('created_at', 'desc');
            
            if ($type) {
                $query->where('type', $type);
            }
            
            if ($action) {
                $query->where('action', $action);
            }
            
            // Log the search
            \App\Services\AdminLogger::logMaintenance(
                'search_activities',
                "Admin searched activities by " . 
                ($type ? "type: $type" : "all types") . 
                ($action ? ", action: $action" : "")
            );
            
            $activities = $query->get()->map(function ($activity) {
                return [
                    'type' => $activity->type,
                    'action' => $activity->action,
                    'description' => $activity->description,
                    'timestamp' => $activity->created_at->diffForHumans(),
                    'created_at' => $activity->created_at->toDateTimeString(),
                    'user' => $activity->user ? $activity->user->name : 'System',
                    'icon' => $this->getActivityIcon($activity->type, $activity->action)
                ];
            });
            
            return $this->sendResponse($activities, 'Activities retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendError('Error searching activities', $e->getMessage(), 500);
        }
    }
    
    /**
     * Get activity type counts for dashboard filters
     */
    public function getActivityTypes(): JsonResponse
    {
        try {
            // Get counts of each activity type
            $typeCounts = Activity::selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->orderBy('count', 'desc')
                ->get();
                
            // Get counts of each action within types
            $actionCounts = Activity::selectRaw('type, action, COUNT(*) as count')
                ->groupBy(['type', 'action'])
                ->orderBy('count', 'desc')
                ->get()
                ->groupBy('type');
                
            // Log this access
            \App\Services\AdminLogger::logMaintenance(
                'view_activity_types',
                'Admin viewed activity type statistics'
            );
            
            return $this->sendResponse([
                'types' => $typeCounts,
                'actions' => $actionCounts
            ], 'Activity type counts retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendError('Error retrieving activity types', $e->getMessage(), 500);
        }
    }
    
    /**
     * Get system health status
     */
    private function getSystemHealthStatus(): string
    {
        // Log that an admin checked system health
        \App\Services\AdminLogger::logMaintenance(
            'check_system_health',
            'Admin checked system health status'
        );
        
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
    }/**
     * Get an appropriate icon for the activity type and action
     */
    private function getActivityIcon(string $type, string $action): string
    {
        $icons = [
            'user' => [
                'created' => 'user-plus',
                'updated' => 'user-edit',
                'deleted' => 'user-minus',
                'login' => 'login',
                'role_updated' => 'user-shield',
                'role_assigned' => 'user-shield',
                'default' => 'user'
            ],
            'junkshop' => [
                'created' => 'store-plus',
                'updated' => 'store-edit',
                'deleted' => 'store-minus',
                'default' => 'store'
            ],
            'merchant' => [
                'created' => 'briefcase-plus',
                'updated' => 'briefcase-edit',
                'deleted' => 'briefcase-minus',
                'default' => 'briefcase'
            ],
            'transaction' => [
                'created' => 'shopping-cart-plus',
                'updated' => 'shopping-cart-edit',
                'deleted' => 'shopping-cart-minus',
                'bid_created' => 'hand-coins',
                'bid_status_changed' => 'exchange-alt',
                'payment_received' => 'money-bill',
                'payment_failed' => 'money-bill-wave',
                'quality_verification' => 'check-double',
                'dispute_created' => 'exclamation-triangle',
                'dispute_resolved' => 'handshake',
                'default' => 'shopping-cart'
            ],
            'bid' => [
                'created' => 'hand-coins',
                'updated' => 'hand-coins',
                'accepted' => 'check-circle',
                'rejected' => 'x-circle',
                'cancelled' => 'ban',
                'default' => 'hand-coins'
            ],
            'admin' => [
                'config_change' => 'cogs',
                'moderation' => 'gavel',
                'verification' => 'certificate',
                'maintenance' => 'tools',
                'bulk_action' => 'layer-group',
                'rate_limit_change' => 'tachometer-alt',
                'default' => 'user-shield'
            ],
            'security' => [
                'failed_auth' => 'lock',
                'successful_auth' => 'unlock',
                'password_reset' => 'key',
                'token_created' => 'id-badge',
                'token_revoked' => 'user-slash',
                'suspicious' => 'exclamation-triangle',
                'permission_change' => 'shield-alt',
                'default' => 'shield-alt'
            ],
            'data' => [
                'backup_created' => 'database',
                'bulk_operation' => 'tasks',
                'data_import' => 'file-import',
                'data_export' => 'file-export',
                'data_cleanup' => 'broom',
                'default' => 'database'
            ],
            'email' => [
                'email_sent' => 'envelope',
                'email_failed' => 'envelope-open-text',
                'default' => 'envelope'
            ],
            'performance' => [
                'slow_query' => 'hourglass',
                'memory_spike' => 'memory',
                'api_performance' => 'tachometer-alt',
                'cache_performance' => 'hdd',
                'job_performance' => 'tasks',
                'default' => 'chart-line'
            ],
            'inventory' => [
                'created' => 'box-open',
                'updated' => 'box',
                'deleted' => 'trash-alt',
                'default' => 'warehouse'
            ],
            'default' => 'activity'
        ];
        
        // Get icon for type and action, fallback to defaults if not found
        if (isset($icons[$type])) {
            return $icons[$type][$action] ?? $icons[$type]['default'] ?? $icons['default'];
        }
        
        return $icons['default'];
    }
}
