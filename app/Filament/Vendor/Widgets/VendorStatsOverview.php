<?php

namespace App\Filament\Vendor\Widgets;

use App\Models\Order;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class VendorStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalCategories = \App\Models\Category::count();
        $totalOrders = Order::count();
        $totalProducts = Product::count();

        return [
            Stat::make('Total Category', $totalCategories)
                ->description('All categories')
                ->descriptionIcon('heroicon-m-tag')
                ->color('success'),

            Stat::make('Total Orders', $totalOrders)
                ->description('All orders received')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('primary'),

            Stat::make('Total Products', $totalProducts)
                ->description('Products in catalog')
                ->descriptionIcon('heroicon-m-squares-2x2')
                ->color('info'),
        ];
    }
}
