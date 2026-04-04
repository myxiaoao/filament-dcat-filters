<?php

namespace Cooper\FilamentDcatFilters\Filters;

use Closure;
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

class ExistsFilter extends Filter
{
    use HasFilterState;
    use HasInlineLabel;
    use HasLabelResolver;
    use HasSelectRadioDisplay;

    protected ?string $relationshipName = null;

    protected bool $negate = false;

    protected ?Closure $constraint = null;

    protected bool $toggleMode = true;

    protected string $existsLabel = 'Exists';

    protected string $notExistsLabel = 'Does Not Exist';

    protected string $allLabel = 'All';

    protected function describeState(): FilterStateDescriptor
    {
        if ($this->toggleMode) {
            return FilterStateDescriptor::make()
                ->fields(['value'])
                ->type(StateType::Toggle)
                ->emptyWhen(fn (array $data) => empty($data['value']))
                ->capabilities(['indicator'])
                ->databaseSupport(['mysql', 'pgsql', 'sqlite']);
        }

        return FilterStateDescriptor::make()
            ->fields(['value'])
            ->type(StateType::Single)
            ->emptyWhen(fn (array $data) => ($data['value'] ?? '') === '')
            ->capabilities(['indicator'])
            ->databaseSupport(['mysql', 'pgsql', 'sqlite']);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->existsLabel = __('filament-dcat-filters::filament-dcat-filters.exists.exists');
        $this->notExistsLabel = __('filament-dcat-filters::filament-dcat-filters.exists.not_exists');
        $this->allLabel = __('filament-dcat-filters::filament-dcat-filters.exists.all');

        $this->columnSpan(1);
    }

    public function relationship(string $name): static
    {
        $this->relationshipName = $name;
        $this->configureForm();

        return $this;
    }

    public function notExists(bool $negate = true): static
    {
        $this->negate = $negate;
        $this->configureForm();

        return $this;
    }

    public function constrainedBy(?Closure $constraint): static
    {
        $this->constraint = $constraint;

        return $this;
    }

    public function toggle(bool $toggle = true): static
    {
        $this->toggleMode = $toggle;
        $this->configureForm();

        return $this;
    }

    public function select(): static
    {
        $this->toggleMode = false;
        $this->displayStyle = 'select';
        $this->configureForm();

        return $this;
    }

    public function radio(): static
    {
        $this->toggleMode = false;
        $this->displayStyle = 'radio';
        $this->configureForm();

        return $this;
    }

    public function existsLabel(string $label): static
    {
        $this->existsLabel = $label;
        $this->configureForm();

        return $this;
    }

    public function notExistsLabel(string $label): static
    {
        $this->notExistsLabel = $label;
        $this->configureForm();

        return $this;
    }

    public function allLabel(string $label): static
    {
        $this->allLabel = $label;
        $this->configureForm();

        return $this;
    }

    public static function forExists(string $relationship): static
    {
        return static::make("has_{$relationship}")
            ->relationship($relationship);
    }

    public static function forNotExists(string $relationship): static
    {
        return static::make("no_{$relationship}")
            ->relationship($relationship)
            ->notExists();
    }

    protected function configureForm(): void
    {
        if (! $this->relationshipName) {
            return;
        }

        if ($this->toggleMode) {
            $label = $this->negate ? $this->notExistsLabel : $this->existsLabel;
            $component = Toggle::make('value')
                ->label($label)
                ->columnSpanFull();

            $this->form([$component]);
            $this->configureQuery();

            return;
        }

        $options = [
            '' => $this->allLabel,
            'exists' => $this->existsLabel,
            'not_exists' => $this->notExistsLabel,
        ];

        $labelResolver = $this->labelResolver();
        $placeholder = __('filament-dcat-filters::filament-dcat-filters.exists.all');
        $component = $this->buildFormComponent('value', $labelResolver, $options, $placeholder);
        $this->form([$component]);
        $this->configureQuery();
    }

    protected function configureQuery(): void
    {
        $this->query(function (Builder $query, array $data): Builder {
            $value = $data['value'] ?? '';

            if ($this->toggleMode) {
                if (empty($value)) {
                    return $query;
                }

                // Toggle on: apply based on negate flag
                return $this->negate
                    ? $query->whereDoesntHave($this->relationshipName, $this->constraint)
                    : $query->whereHas($this->relationshipName, $this->constraint);
            }

            return match ($value) {
                'exists' => $query->whereHas($this->relationshipName, $this->constraint),
                'not_exists' => $query->whereDoesntHave($this->relationshipName, $this->constraint),
                default => $query,
            };
        });

        $this->indicateUsing(function (array $data): array {
            $value = $data['value'] ?? '';

            if ($value === '' || ($this->toggleMode && empty($value))) {
                return [];
            }

            $label = $this->resolveLabel();

            if ($this->toggleMode) {
                $displayLabel = $this->negate ? $this->notExistsLabel : $this->existsLabel;

                return [
                    Indicator::make("{$label}: {$displayLabel}")
                        ->removeField('value'),
                ];
            }

            $displayLabel = match ($value) {
                'exists' => $this->existsLabel,
                'not_exists' => $this->notExistsLabel,
                default => $value,
            };

            return [
                Indicator::make("{$label}: {$displayLabel}")
                    ->removeField('value'),
            ];
        });
    }

    public function getRelationshipName(): ?string
    {
        return $this->relationshipName;
    }
}
