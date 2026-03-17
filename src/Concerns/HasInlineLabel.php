<?php

namespace Cooper\FilamentDcatFilters\Concerns;

use Closure;
use Filament\Schemas\Components\Component;

trait HasInlineLabel
{
    protected ?bool $isInlineLabel = null;

    protected ?bool $isPlaceholderFromLabel = null;

    /**
     * Set whether the label should be displayed inline (as prefix) inside the input.
     */
    public function inlineLabel(bool $condition = true): static
    {
        $this->isInlineLabel = $condition;

        return $this;
    }

    /**
     * Set whether the placeholder should default to the label text.
     */
    public function placeholderFromLabel(bool $condition = true): static
    {
        $this->isPlaceholderFromLabel = $condition;

        return $this;
    }

    /**
     * Determine if inline label is enabled for this filter.
     */
    protected function shouldInlineLabel(): bool
    {
        return $this->isInlineLabel ?? config('filament-dcat-filters.inline_label', true);
    }

    /**
     * Determine if placeholder should use label text.
     */
    protected function shouldPlaceholderFromLabel(): bool
    {
        return $this->isPlaceholderFromLabel ?? config('filament-dcat-filters.placeholder_from_label', true);
    }

    /**
     * Apply dcat-style inline label to a form component.
     *
     * Hides the original label and moves it into the input's prefix.
     * Optionally sets the placeholder to match the label text.
     */
    protected function applyInlineLabel(Component $component, string|Closure $label): Component
    {
        if (! $this->shouldInlineLabel()) {
            return $component;
        }

        $component->hiddenLabel();
        $component->prefix($label);

        if ($this->shouldPlaceholderFromLabel() && method_exists($component, 'placeholder')) {
            $component->placeholder($label);
        }

        return $component;
    }

    /**
     * Apply dcat-style inline labels for range filter (from/to pair).
     *
     * The "from" field gets the label as prefix, the "to" field hides the label.
     */
    protected function applyRangeInlineLabels(Component $from, Component $to, string|Closure $label): void
    {
        if (! $this->shouldInlineLabel()) {
            return;
        }

        $from->hiddenLabel();
        $from->prefix($label);

        $to->hiddenLabel();
    }
}
