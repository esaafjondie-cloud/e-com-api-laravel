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
            Stat::make('إجمالي الأقسام', $totalCategories)
                ->description('جميع الأقسام')
                ->descriptionIcon('heroicon-m-tag')
                ->color('success'),

            Stat::make('إجمالي الطلبات', $totalOrders)
                ->description('كل الطلبات المستلمة')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('primary'),

            Stat::make('إجمالي المنتجات', $totalProducts)
                ->description('المنتجات في الكتالوج')
                ->descriptionIcon('heroicon-m-squares-2x2')
                ->color('info'),
        ];
    }
}
