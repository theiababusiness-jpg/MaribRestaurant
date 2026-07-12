<?php

namespace App\Services\Orders;

use App\Models\Branch;
use RuntimeException;

class DeliveryQuoteService
{
    public const MAX_DISTANCE_KM = 4.0;
    public const FIXED_DELIVERY_FEE = 5.0;

    public function quote(Branch $branch, float $customerLat, float $customerLng, float $itemsSubtotal): array
    {
        if (! $branch->is_active || ! $branch->delivery_enabled) {
            throw new RuntimeException('Delivery is not enabled for the selected branch.');
        }

        if ($branch->lat === null || $branch->lng === null) {
            throw new RuntimeException('The selected branch does not have delivery coordinates configured.');
        }

        $distanceKm = $this->haversineDistanceKm(
            (float) $branch->lat,
            (float) $branch->lng,
            $customerLat,
            $customerLng
        );

        if ($distanceKm > self::MAX_DISTANCE_KM + 0.000001) {
            throw new RuntimeException('Delivery is unavailable for locations farther than 4 km from the selected branch.');
        }

        $itemsSubtotal = round($itemsSubtotal, 2);
        $deliveryFee = self::FIXED_DELIVERY_FEE;

        return [
            'distance_km' => round($distanceKm, 2),
            'delivery_fee' => round($deliveryFee, 2),
            'items_subtotal' => $itemsSubtotal,
            'total' => round($itemsSubtotal + $deliveryFee, 2),
            'max_distance_km' => self::MAX_DISTANCE_KM,
            'is_deliverable' => true,
        ];
    }

    protected function haversineDistanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadiusKm = 6371;
        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLng = deg2rad($lng2 - $lng1);

        $a = sin($deltaLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($deltaLng / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadiusKm * $c;
    }
}
