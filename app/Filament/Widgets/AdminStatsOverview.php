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
            Stat::make('إجمالي الإيرادات', number_format($totalRevenue, 2) . ' ل.س')
                ->description('إجمالي القيمة المسلمة')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('إجمالي الطلبات', $totalOrders)
                ->description('كل الطلبات المقدمة')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('primary'),

            Stat::make('إجمالي المنتجات', $totalProducts)
                ->description('المنتجات في الكتالوج')
                ->descriptionIcon('heroicon-m-squares-2x2')
                ->color('info'),

            Stat::make('إجمالي الأقسام', $totalCategories)
                ->description('الأقسام المتاحة')
                ->descriptionIcon('heroicon-m-tag')
                ->color('warning'),

            Stat::make('إجمالي المستخدمين', $totalUsers)
                ->description('الحسابات المسجلة')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),
        ];
    }
}
