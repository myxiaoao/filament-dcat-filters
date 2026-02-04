<?php

use Cooper\FilamentDcatFilters\Filters\EnumFilter;
use Filament\Forms\Components\Select;

// Test enum for testing purposes
enum TestStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'Pending Order',
            self::Processing => 'In Progress',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }
}

// Unit enum for testing (no backing type)
enum TestPriority
{
    case Low;
    case Medium;
    case High;
    case Critical;

    public function label(): string
    {
        return match ($this) {
            self::Low => 'Low Priority',
            self::Medium => 'Medium Priority',
            self::High => 'High Priority',
            self::Critical => 'Critical Priority',
        };
    }
}

it('can be instantiated', function () {
    $filter = EnumFilter::make('status')
        ->enum(TestStatus::class);

    expect($filter)->toBeInstanceOf(EnumFilter::class);
});

it('has correct default column span', function () {
    $filter = EnumFilter::make('status')
        ->enum(TestStatus::class);

    expect($filter->getColumnSpan())->toBe(1);
});

describe('Form Component', function () {
    it('uses select component by default', function () {
        $filter = EnumFilter::make('status')
            ->enum(TestStatus::class);

        $form = $filter->getFormSchema();

        expect($form)->toHaveCount(1);
        expect($form[0])->toBeInstanceOf(Select::class);
    });

    it('uses multiple select when multiple is enabled', function () {
        $filter = EnumFilter::make('status')
            ->enum(TestStatus::class)
            ->multiple();

        $form = $filter->getFormSchema();

        expect($form[0])->toBeInstanceOf(Select::class);
        expect($form[0]->isMultiple())->toBeTrue();
    });
});

describe('Enum Options', function () {
    it('generates options from backed enum', function () {
        $filter = EnumFilter::make('status')
            ->enum(TestStatus::class);

        expect($filter)->toBeInstanceOf(EnumFilter::class);
    });

    it('generates options from unit enum', function () {
        $filter = EnumFilter::make('priority')
            ->enum(TestPriority::class);

        expect($filter)->toBeInstanceOf(EnumFilter::class);
    });

    it('can exclude specific enum cases', function () {
        $filter = EnumFilter::make('status')
            ->enum(TestStatus::class)
            ->exclude([TestStatus::Cancelled]);

        expect($filter)->toBeInstanceOf(EnumFilter::class);
    });
});

describe('Multiple Selection', function () {
    it('can enable multiple selection', function () {
        $filter = EnumFilter::make('status')
            ->enum(TestStatus::class)
            ->multiple();

        $form = $filter->getFormSchema();

        expect($form[0]->isMultiple())->toBeTrue();
    });

    it('can disable multiple selection', function () {
        $filter = EnumFilter::make('status')
            ->enum(TestStatus::class)
            ->multiple(false);

        $form = $filter->getFormSchema();

        expect($form[0]->isMultiple())->toBeFalse();
    });
});

describe('Searchable', function () {
    it('can enable searchable', function () {
        $filter = EnumFilter::make('status')
            ->enum(TestStatus::class)
            ->searchable();

        expect($filter)->toBeInstanceOf(EnumFilter::class);
    });

    it('can disable searchable', function () {
        $filter = EnumFilter::make('status')
            ->enum(TestStatus::class)
            ->searchable(false);

        expect($filter)->toBeInstanceOf(EnumFilter::class);
    });
});

describe('Custom Label Resolver', function () {
    it('can use custom label method', function () {
        $filter = EnumFilter::make('priority')
            ->enum(TestPriority::class)
            ->labelUsing('label');

        expect($filter)->toBeInstanceOf(EnumFilter::class);
    });

    it('can use closure for label', function () {
        $filter = EnumFilter::make('status')
            ->enum(TestStatus::class)
            ->labelUsing(fn ($case) => "Custom: {$case->name}");

        expect($filter)->toBeInstanceOf(EnumFilter::class);
    });
});

describe('Custom Value Resolver', function () {
    it('can use custom value method', function () {
        $filter = EnumFilter::make('priority')
            ->enum(TestPriority::class)
            ->valueUsing(fn ($case) => strtolower($case->name));

        expect($filter)->toBeInstanceOf(EnumFilter::class);
    });

    it('can use closure for value', function () {
        $filter = EnumFilter::make('status')
            ->enum(TestStatus::class)
            ->valueUsing(fn ($case) => "status_{$case->value}");

        expect($filter)->toBeInstanceOf(EnumFilter::class);
    });
});

describe('Combined Features', function () {
    it('can combine multiple, searchable, and exclude', function () {
        $filter = EnumFilter::make('status')
            ->enum(TestStatus::class)
            ->multiple()
            ->searchable()
            ->exclude([TestStatus::Cancelled]);

        $form = $filter->getFormSchema();

        expect($form[0]->isMultiple())->toBeTrue();
    });

    it('can combine custom label and value resolvers', function () {
        $filter = EnumFilter::make('priority')
            ->enum(TestPriority::class)
            ->labelUsing(fn ($case) => "Priority: {$case->name}")
            ->valueUsing(fn ($case) => strtolower($case->name));

        expect($filter)->toBeInstanceOf(EnumFilter::class);
    });
});

describe('Column Name', function () {
    it('uses filter name as column by default', function () {
        $filter = EnumFilter::make('status')
            ->enum(TestStatus::class);

        expect($filter)->toBeInstanceOf(EnumFilter::class);
    });

    it('can set custom column name', function () {
        $filter = EnumFilter::make('order_status')
            ->enum(TestStatus::class)
            ->column('status');

        expect($filter)->toBeInstanceOf(EnumFilter::class);
    });

    it('can combine column with other features', function () {
        $filter = EnumFilter::make('status_filter')
            ->enum(TestStatus::class)
            ->column('order_status')
            ->multiple()
            ->searchable();

        expect($filter)->toBeInstanceOf(EnumFilter::class);
    });

    it('stores column name correctly', function () {
        $filter = EnumFilter::make('status_filter')
            ->enum(TestStatus::class)
            ->column('order_status');

        $reflection = new ReflectionClass($filter);
        $prop = $reflection->getProperty('columnName');
        $prop->setAccessible(true);

        expect($prop->getValue($filter))->toBe('order_status');
    });
});
