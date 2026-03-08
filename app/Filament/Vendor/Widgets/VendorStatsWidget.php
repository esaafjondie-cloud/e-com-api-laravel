<?php

namespace App\Filament\Vendor\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class VendorStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $pendingOrders  = Order::where('status', 'unpaid')->count();
        $paidOrders     = Order::where('status', 'paid')->count();
        $shippedOrders  = Order::where('status', 'shipped')->count();
        $totalOrders    = Order::count();

        return [
            Stat::make('Pending Orders', $pendingOrders)
                ->description('Awaiting payment verification')
                ->descriptionIcon('heroicon-m-clock')
                ->color('danger')
                ->icon('heroicon-o-clock'),

            Stat::make('Paid Orders', $paidOrders)
                ->description('Ready to ship')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->icon('heroicon-o-check-circle'),

            Stat::make('Shipped Orders', $shippedOrders)
                ->description("Out of {$totalOrders} total")
                ->descriptionIcon('heroicon-m-truck')
                ->color('info')
                ->icon('heroicon-o-truck'),
        ];
    }
}
