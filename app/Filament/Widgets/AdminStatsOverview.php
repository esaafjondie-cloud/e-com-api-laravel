<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Category;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalRevenue = Order::where('status', 'delivered')->sum('total_amount');
        $totalOrders = Order::count();
        $totalProducts = Product::count();
        $totalCategories = Category::count();
        $totalUsers = User::count();

        return [
            Stat::make('Total Revenue', number_format($totalRevenue, 2) . ' SYP')
                ->description('Total delivered value')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Total Orders', $totalOrders)
                ->description('All orders placed')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('primary'),

            Stat::make('Total Products', $totalProducts)
                ->description('Products in catalog')
                ->descriptionIcon('heroicon-m-squares-2x2')
                ->color('info'),

            Stat::make('Total Categories', $totalCategories)
                ->description('Available categories')
                ->descriptionIcon('heroicon-m-tag')
                ->color('warning'),

            Stat::make('Total Users', $totalUsers)
                ->description('Registered accounts')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),
        ];
    }
}
