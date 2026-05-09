<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class RevenueChartWidget extends ChartWidget
{
    protected static ?string $heading = 'الإيرادات الشهرية (ل.س)';
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $arabicMonths = ['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];

        $months = collect(range(5, 0))->map(function ($monthsAgo) use ($arabicMonths) {
            $date = Carbon::now()->subMonths($monthsAgo);
            return [
                'label'   => $arabicMonths[$date->month - 1] . ' ' . $date->year,
                'revenue' => Order::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->where('status', '!=', 'unpaid')
                    ->sum('total_amount'),
            ];
        });

        return [
            'datasets' => [
                [
                    'label'           => 'الإيرادات (ل.س)',
                    'data'            => $months->pluck('revenue')->toArray(),
                    'borderColor'     => '#f59e0b',
                    'backgroundColor' => 'rgba(245,158,11,0.1)',
                    'fill'            => true,
                    'tension'         => 0.4,
                ],
            ],
            'labels' => $months->pluck('label')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
