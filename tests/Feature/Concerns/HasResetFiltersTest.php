<?php

use Cooper\FilamentDcatFilters\Actions\ResetFiltersAction;
use Cooper\FilamentDcatFilters\Concerns\HasResetFilters;

// Create a test class that uses the trait
class HasResetFiltersTestClass
{
    use HasResetFilters;

    public function testGetResetFiltersAction(): ResetFiltersAction
    {
        return $this->getResetFiltersAction();
    }
}

beforeEach(function () {
    $this->testClass = new HasResetFiltersTestClass;
});

describe('HasResetFilters Trait', function () {
    it('provides getResetFiltersAction method', function () {
        $action = $this->testClass->testGetResetFiltersAction();

        expect($action)->toBeInstanceOf(ResetFiltersAction::class);
    });

    it('returns a fresh action instance each time', function () {
        $action1 = $this->testClass->testGetResetFiltersAction();
        $action2 = $this->testClass->testGetResetFiltersAction();

        expect($action1)->not->toBe($action2);
    });

    it('has withResetFiltersAction method', function () {
        expect(method_exists($this->testClass, 'withResetFiltersAction'))->toBeTrue();
    });
});
