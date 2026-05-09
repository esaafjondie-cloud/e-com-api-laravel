<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalRevenue = Order::where('status', '!=', 'unpaid')->sum('total_amount');
        $totalUsers   = User::where('role', 'user')->count();
        $totalOrders  = Order::count();
        $pendingOrders = Order::where('status', 'unpaid')->count();

        return [
            Stat::make('إجمالي الإيرادات', number_format($totalRevenue, 0) . ' ل.س')
                ->description('من جميع الطلبات المدفوعة')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->icon('heroicon-o-banknotes'),

            Stat::make('إجمالي المستخدمين', $totalUsers)
                ->description('العملاء المسجلين')
                ->descriptionIcon('heroicon-m-users')
                ->color('info')
                ->icon('heroicon-o-users'),

            Stat::make('إجمالي الطلبات', $totalOrders)
                ->description("{$pendingOrders} بانتظار الدفع")
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('warning')
                ->icon('heroicon-o-shopping-cart'),
        ];
    }
}
