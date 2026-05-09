<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class AdminOrdersChart extends ChartWidget
{
    protected static ?string $heading = 'نظرة عامة على الطلبات';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $orders = \App\Models\Order::select('created_at')
            ->whereYear('created_at', date('Y'))
            ->get()
            ->groupBy(function ($date) {
                return \Carbon\Carbon::parse($date->created_at)->format('m');
            });

        $months = ['01','02','03','04','05','06','07','08','09','10','11','12'];
        $labels = ['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];
        $counts = [];

        foreach ($months as $month) {
            $counts[] = isset($orders[$month]) ? $orders[$month]->count() : 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'الطلبات',
                    'data' => $counts,
                    'borderColor' => '#f59e0b',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
