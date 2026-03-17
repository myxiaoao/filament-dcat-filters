<?php

use Cooper\FilamentDcatFilters\Concerns\HasInlineLabel;
use Cooper\FilamentDcatFilters\Filters\LikeFilter;
use Filament\Forms\Components\TextInput;

// Expose protected methods for testing
class HasInlineLabelTestClass
{
    use HasInlineLabel;

    public function testApplyInlineLabel(TextInput $component, string $label): TextInput
    {
        return $this->applyInlineLabel($component, $label);
    }

    public function testApplyRangeInlineLabels(TextInput $from, TextInput $to, string $label): void
    {
        $this->applyRangeInlineLabels($from, $to, $label);
    }
}

describe('HasInlineLabel', function () {
    it('enables inline label by default', function () {
        $filter = LikeFilter::make('test');

        $reflection = new ReflectionClass($filter);
        $method = $reflection->getMethod('shouldInlineLabel');
        $method->setAccessible(true);

        expect($method->invoke($filter))->toBeTrue();
    });

    it('can disable inline label', function () {
        $filter = LikeFilter::make('test')->inlineLabel(false);

        $reflection = new ReflectionClass($filter);
        $method = $reflection->getMethod('shouldInlineLabel');
        $method->setAccessible(true);

        expect($method->invoke($filter))->toBeFalse();
    });

    it('can enable placeholder from label', function () {
        $filter = LikeFilter::make('test')->placeholderFromLabel(true);

        $reflection = new ReflectionClass($filter);
        $method = $reflection->getMethod('shouldPlaceholderFromLabel');
        $method->setAccessible(true);

        expect($method->invoke($filter))->toBeTrue();
    });

    it('can disable placeholder from label', function () {
        $filter = LikeFilter::make('test')->placeholderFromLabel(false);

        $reflection = new ReflectionClass($filter);
        $method = $reflection->getMethod('shouldPlaceholderFromLabel');
        $method->setAccessible(true);

        expect($method->invoke($filter))->toBeFalse();
    });
});

describe('applyInlineLabel', function () {
    it('hides the label and sets prefix when inline is enabled', function () {
        $obj = new HasInlineLabelTestClass;
        $obj->inlineLabel(true);

        $input = TextInput::make('value')->label('Search');
        $obj->testApplyInlineLabel($input, 'Search');

        expect($input->isLabelHidden())->toBeTrue();
        expect($input->getPrefixLabel())->toBe('Search');
    });

    it('does nothing when inline label is disabled', function () {
        $obj = new HasInlineLabelTestClass;
        $obj->inlineLabel(false);

        $input = TextInput::make('value')->label('Search');
        $obj->testApplyInlineLabel($input, 'Search');

        expect($input->isLabelHidden())->toBeFalse();
        expect($input->getPrefixLabel())->toBeNull();
    });
});

describe('applyRangeInlineLabels', function () {
    it('hides both labels and sets prefix on from field when inline is enabled', function () {
        $obj = new HasInlineLabelTestClass;
        $obj->inlineLabel(true);

        $from = TextInput::make('from')->label('Price');
        $to = TextInput::make('to')->label('Price');

        $obj->testApplyRangeInlineLabels($from, $to, 'Price');

        expect($from->isLabelHidden())->toBeTrue();
        expect($from->getPrefixLabel())->toBe('Price');
        expect($to->isLabelHidden())->toBeTrue();
    });
});
