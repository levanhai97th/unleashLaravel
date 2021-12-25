<?php

namespace Unleash\Client\Stickiness;

use lastguest\Murmur;

final class MurmurHashCalculator implements StickinessCalculator
{
    public function calculate(string $id, string $groupId, int $normalizer = 100): int
    {
        return Murmur::hash3_int("{$groupId}:{$id}") % $normalizer + 1;
    }
}
