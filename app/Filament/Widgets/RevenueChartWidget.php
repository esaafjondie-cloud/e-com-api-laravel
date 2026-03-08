<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class RevenueChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Monthly Revenue (SYP)';
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $months = collect(range(5, 0))->map(function ($monthsAgo) {
            $date = Carbon::now()->subMonths($monthsAgo);
            return [
                'label'   => $date->format('M Y'),
                'revenue' => Order::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->where('status', '!=', 'unpaid')
                    ->sum('total_amount'),
            ];
        });

        return [
            'datasets' => [
                [
                    'label'           => 'Revenue (SYP)',
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
