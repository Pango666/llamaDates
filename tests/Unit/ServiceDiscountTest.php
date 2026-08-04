<?php

namespace Tests\Unit;

use App\Models\Service;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class ServiceDiscountTest extends TestCase
{
    public function test_it_calculates_percent_and_fixed_discounts_during_the_active_period(): void
    {
        $now = Carbon::parse('2026-08-04 12:00:00');
        $service = new Service([
            'price' => 1000,
            'discount_active' => true,
            'discount_type' => 'percent',
            'discount_amount' => 10,
            'discount_start_at' => $now->copy()->subDay(),
            'discount_end_at' => $now->copy()->addDay(),
        ]);

        self::assertSame(900.0, $service->priceEffective($now));

        $service->discount_type = 'fixed';
        $service->discount_amount = 150;

        self::assertSame(850.0, $service->priceEffective($now));
    }

    public function test_it_uses_the_base_price_outside_the_discount_period(): void
    {
        $now = Carbon::parse('2026-08-04 12:00:00');
        $service = new Service([
            'price' => 1000,
            'discount_active' => true,
            'discount_type' => 'fixed',
            'discount_amount' => 150,
            'discount_start_at' => $now->copy()->addDay(),
            'discount_end_at' => $now->copy()->addDays(2),
        ]);

        self::assertSame(1000.0, $service->priceEffective($now));
    }
}
