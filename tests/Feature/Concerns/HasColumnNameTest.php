<?php

use Cooper\FilamentDcatFilters\Concerns\HasColumnName;
use Cooper\FilamentDcatFilters\Filters\LikeFilter;

// Expose protected isValueEmpty for testing
class HasColumnNameTestClass
{
    use HasColumnName;

    public function testIsValueEmpty(mixed $value): bool
    {
        return $this->isValueEmpty($value);
    }
}

it('uses filter name as column by default', function () {
    $filter = LikeFilter::make('email');
    $method = new ReflectionMethod($filter, 'resolveColumnName');
    expect($method->invoke($filter))->toBe('email');
});

it('can set custom column name', function () {
    $filter = LikeFilter::make('search')->column('user_email');
    $method = new ReflectionMethod($filter, 'resolveColumnName');
    expect($method->invoke($filter))->toBe('user_email');
});

it('column method returns static for chaining', function () {
    $filter = LikeFilter::make('test');
    $result = $filter->column('custom_column');
    expect($result)->toBe($filter);
});

describe('isValueEmpty', function () {
    beforeEach(function () {
        $this->obj = new HasColumnNameTestClass;
    });

    it('returns true for null', function () {
        expect($this->obj->testIsValueEmpty(null))->toBeTrue();
    });

    it('returns true for empty string', function () {
        expect($this->obj->testIsValueEmpty(''))->toBeTrue();
    });

    it('returns true for empty array', function () {
        expect($this->obj->testIsValueEmpty([]))->toBeTrue();
    });

    it('returns false for zero integer', function () {
        expect($this->obj->testIsValueEmpty(0))->toBeFalse();
    });

    it('returns false for string zero', function () {
        expect($this->obj->testIsValueEmpty('0'))->toBeFalse();
    });

    it('returns false for non-empty string', function () {
        expect($this->obj->testIsValueEmpty('value'))->toBeFalse();
    });

    it('returns false for non-empty array', function () {
        expect($this->obj->testIsValueEmpty([1]))->toBeFalse();
    });
});
