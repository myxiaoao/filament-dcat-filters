<?php

use Cooper\FilamentDcatFilters\Concerns\HasRangeQuery;

// Create a test class that uses the trait
class HasRangeQueryTestClass
{
    use HasRangeQuery;

    public function testIsRangeValueEmpty(mixed $value): bool
    {
        return $this->isRangeValueEmpty($value);
    }

    public function testGenerateRangeIndicators(array $data, string $label): array
    {
        return $this->generateRangeIndicators($data, $label);
    }
}

beforeEach(function () {
    $this->testClass = new HasRangeQueryTestClass;
});

describe('isRangeValueEmpty', function () {
    it('returns true for null', function () {
        expect($this->testClass->testIsRangeValueEmpty(null))->toBeTrue();
    });

    it('returns true for empty string', function () {
        expect($this->testClass->testIsRangeValueEmpty(''))->toBeTrue();
    });

    it('returns false for zero', function () {
        expect($this->testClass->testIsRangeValueEmpty(0))->toBeFalse();
    });

    it('returns false for string zero', function () {
        expect($this->testClass->testIsRangeValueEmpty('0'))->toBeFalse();
    });

    it('returns false for non-empty values', function () {
        expect($this->testClass->testIsRangeValueEmpty(1))->toBeFalse();
        expect($this->testClass->testIsRangeValueEmpty('value'))->toBeFalse();
        expect($this->testClass->testIsRangeValueEmpty(1.5))->toBeFalse();
    });
});

describe('generateRangeIndicators', function () {
    it('returns empty array when both from and to are empty', function () {
        $indicators = $this->testClass->testGenerateRangeIndicators(
            ['from' => null, 'to' => null],
            'Price'
        );

        expect($indicators)->toBe([]);
    });

    it('returns from indicator when only from is set', function () {
        $indicators = $this->testClass->testGenerateRangeIndicators(
            ['from' => 100, 'to' => null],
            'Price'
        );

        expect($indicators)->toHaveCount(1);
        expect($indicators[0])->toContain('Price');
        expect($indicators[0])->toContain('100');
    });

    it('returns to indicator when only to is set', function () {
        $indicators = $this->testClass->testGenerateRangeIndicators(
            ['from' => null, 'to' => 200],
            'Price'
        );

        expect($indicators)->toHaveCount(1);
        expect($indicators[0])->toContain('Price');
        expect($indicators[0])->toContain('200');
    });

    it('returns both indicators when both are set', function () {
        $indicators = $this->testClass->testGenerateRangeIndicators(
            ['from' => 100, 'to' => 200],
            'Price'
        );

        expect($indicators)->toHaveCount(2);
        expect($indicators[0])->toContain('100');
        expect($indicators[1])->toContain('200');
    });

    it('handles string values', function () {
        $indicators = $this->testClass->testGenerateRangeIndicators(
            ['from' => '2024-01-01', 'to' => '2024-12-31'],
            'Date'
        );

        expect($indicators)->toHaveCount(2);
        expect($indicators[0])->toContain('2024-01-01');
        expect($indicators[1])->toContain('2024-12-31');
    });
});
