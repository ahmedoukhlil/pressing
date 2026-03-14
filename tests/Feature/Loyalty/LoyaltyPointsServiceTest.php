<?php

namespace Tests\Feature\Loyalty;

use App\Support\LoyaltyPointsService;
use Tests\TestCase;

class LoyaltyPointsServiceTest extends TestCase
{
    public function test_it_calculates_points_and_discount(): void
    {
        $settings = [
            'enabled' => true,
            'mru_per_point' => 10.0,
            'mru_discount_per_point' => 8.0,
        ];

        $this->assertSame(12, LoyaltyPointsService::pointsFromAmount(125, $settings));
        $this->assertSame(80.0, LoyaltyPointsService::amountFromPoints(10, $settings));
        $this->assertSame(15, LoyaltyPointsService::maxPointsForAmount(120, $settings));
    }

    public function test_it_disables_points_when_feature_is_off(): void
    {
        $settings = [
            'enabled' => false,
            'mru_per_point' => 10.0,
            'mru_discount_per_point' => 10.0,
        ];

        $this->assertSame(0, LoyaltyPointsService::pointsFromAmount(500, $settings));
        $this->assertSame(0.0, LoyaltyPointsService::amountFromPoints(100, $settings));
        $this->assertSame(0, LoyaltyPointsService::maxPointsForAmount(200, $settings));
    }

    public function test_it_normalizes_points_input(): void
    {
        $this->assertSame(15, LoyaltyPointsService::normalizePointsInput('15'));
        $this->assertSame(0, LoyaltyPointsService::normalizePointsInput('-7'));
        $this->assertSame(0, LoyaltyPointsService::normalizePointsInput(''));
    }
}

