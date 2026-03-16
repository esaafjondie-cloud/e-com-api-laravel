<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class AdminOrdersChart extends ChartWidget
{
    protected static ?string $heading = 'Orders Overview';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $orders = \App\Models\Order::select('created_at')
            ->whereYear('created_at', date('Y'))
            ->get()
            ->groupBy(function ($date) {
                return \Carbon\Carbon::parse($date->created_at)->format('M');
            });

        $counts = [];
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        
        foreach ($months as $month) {
            $counts[] = isset($orders[$month]) ? $orders[$month]->count() : 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Orders',
                    'data' => $counts,
                    'borderColor' => '#f59e0b',
                ],
            ],
            'labels' => $months,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
