<?php

namespace Cooper\FilamentDcatFilters\Filters;

use Cooper\FilamentDcatFilters\Concerns\HasFilterState;
use Cooper\FilamentDcatFilters\Concerns\HasInlineLabel;
use Cooper\FilamentDcatFilters\Concerns\HasLabelResolver;
use Cooper\FilamentDcatFilters\Concerns\HasSelectRadioDisplay;
use Cooper\FilamentDcatFilters\State\FilterStateDescriptor;
use Cooper\FilamentDcatFilters\State\StateType;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;

class SoftDeleteFilter extends Filter
{
    use HasFilterState;
    use HasInlineLabel;
    use HasLabelResolver;
    use HasSelectRadioDisplay;

    protected string $withoutTrashedLabel = 'Without Trashed';

    protected string $onlyTrashedLabel = 'Only Trashed';

    protected string $withTrashedLabel = 'With Trashed';

    protected string $allLabel = 'All';

    protected bool $toggleMode = false;

    protected function describeState(): FilterStateDescriptor
    {
        if ($this->toggleMode) {
            return FilterStateDescriptor::make()
                ->fields(['trashed'])
                ->type(StateType::Toggle)
                ->emptyWhen(fn (array $data) => empty($data['trashed']))
                ->capabilities(['indicator'])
                ->databaseSupport(['mysql', 'pgsql', 'sqlite']);
        }

        return FilterStateDescriptor::make()
            ->fields(['trashed'])
            ->type(StateType::Keyed)
            ->emptyWhen(fn (array $data) => ($data['trashed'] ?? '') === '')
            ->capabilities(['indicator'])
            ->databaseSupport(['mysql', 'pgsql', 'sqlite']);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutTrashedLabel = __('filament-dcat-filters::filament-dcat-filters.soft_delete.without_trashed');
        $this->onlyTrashedLabel = __('filament-dcat-filters::filament-dcat-filters.soft_delete.only_trashed');
        $this->withTrashedLabel = __('filament-dcat-filters::filament-dcat-filters.soft_delete.with_trashed');
        $this->allLabel = __('filament-dcat-filters::filament-dcat-filters.soft_delete.all');

        $this->columnSpan(1);
        $this->configureForm();
    }

    public function withoutTrashedLabel(string $label): static
    {
        $this->withoutTrashedLabel = $label;
        $this->configureForm();

        return $this;
    }

    public function onlyTrashedLabel(string $label): static
    {
        $this->onlyTrashedLabel = $label;
        $this->configureForm();

        return $this;
    }

    public function withTrashedLabel(string $label): static
    {
        $this->withTrashedLabel = $label;
        $this->configureForm();

        return $this;
    }

    public function toggle(bool $toggle = true): static
    {
        $this->toggleMode = $toggle;
        $this->configureForm();

        return $this;
    }

    protected function configureForm(): void
    {
        if ($this->toggleMode) {
            $label = $this->withTrashedLabel;
            $component = Toggle::make('trashed')
                ->label($label)
                ->columnSpanFull();

            $this->form([$component]);
            $this->configureQuery();

            return;
        }

        $options = [
            '' => $this->allLabel,
            'without' => $this->withoutTrashedLabel,
            'with' => $this->withTrashedLabel,
            'only' => $this->onlyTrashedLabel,
        ];

        $labelResolver = $this->labelResolver();
        $placeholder = __('filament-dcat-filters::filament-dcat-filters.soft_delete.all');
        $component = $this->buildFormComponent('trashed', $labelResolver, $options, $placeholder);
        $this->form([$component]);
        $this->configureQuery();
    }

    protected function configureQuery(): void
    {
        $this->query(function (Builder $query, array $data): Builder {
            $value = $data['trashed'] ?? '';

            if ($this->toggleMode) {
                if (! empty($value)) {
                    return $query->withTrashed();
                }

                return $query;
            }

            return match ($value) {
                'with' => $query->withTrashed(),
                'only' => $query->onlyTrashed(),
                default => $query,
            };
        });

        $this->indicateUsing(function (array $data): array {
            $value = $data['trashed'] ?? '';

            if ($value === '' || ($this->toggleMode && empty($value))) {
                return [];
            }

            $label = $this->resolveLabel();

            if ($this->toggleMode) {
                return [
                    Indicator::make("{$label}: {$this->withTrashedLabel}")
                        ->removeField('trashed'),
                ];
            }

            $displayLabel = match ($value) {
                'with' => $this->withTrashedLabel,
                'only' => $this->onlyTrashedLabel,
                'without' => $this->withoutTrashedLabel,
                default => $value,
            };

            return [
                Indicator::make("{$label}: {$displayLabel}")
                    ->removeField('trashed'),
            ];
        });
    }
}
