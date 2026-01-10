# Accessibility

This package is built with accessibility in mind, following WCAG 2.1 guidelines to ensure all users can effectively interact with filters.

## Features

### ARIA Attributes

All filter components include proper ARIA attributes for screen reader compatibility:

- `role="combobox"` - Identifies filter inputs as comboboxes
- `aria-haspopup="dialog"` - Indicates that the element opens a dialog
- `aria-expanded` - Dynamically reflects the open/closed state
- `aria-label` - Provides descriptive labels for interactive elements
- `aria-live="polite"` - Announces dynamic content changes

### Keyboard Navigation

Filters support full keyboard navigation:

| Key | Action |
|-----|--------|
| `Tab` | Move focus between filter elements |
| `Enter` | Activate the focused filter/open modal |
| `Space` | Activate the focused filter/open modal |
| `Escape` | Close modal dialogs |
| `Arrow keys` | Navigate within dropdown options |

### Screen Reader Support

The package includes screen reader announcements for:

- Selection changes
- Filter application
- Filter clearing
- Loading states
- Error messages

```blade
{{-- Screen reader announcements are automatically handled --}}
<div role="status" aria-live="polite" aria-atomic="true" class="sr-only">
    Selection updated
</div>
```

## Translation Keys

All accessibility-related text is translatable:

```php
// resources/lang/en/filament-dcat-filters.php
'accessibility.selection_updated' => 'Selection updated',
'accessibility.selection_cleared' => 'Selection cleared',
'accessibility.change_selection' => 'Change selection',
'accessibility.open_selection' => 'Open selection dialog',
'accessibility.clear_selection' => 'Clear selection',
'accessibility.items_selected' => 'Items selected',
'accessibility.no_selection' => 'No items selected',
'accessibility.filter_applied' => 'Filter applied',
'accessibility.filter_cleared' => 'Filter cleared',
'accessibility.loading' => 'Loading',
'accessibility.error_occurred' => 'An error occurred',
```

## Focus Management

The package handles focus management automatically:

1. When opening a modal, focus moves to the modal content
2. When closing a modal, focus returns to the trigger element
3. Focus trapping within modals prevents focus from escaping

```javascript
// Focus is automatically managed
openModal(event) {
    if (event) {
        this.triggerElement = event.target;
    }
    // ... open modal
}

// Return focus on close (handled by Filament modal)
```

## Best Practices

### Custom Labels

Always provide meaningful labels for your filters:

```php
ModalSelectFilter::make('author')
    ->label('Select Author')  // Clear, descriptive label
    ->modelClass(User::class)
    ->titleColumn('name');
```

### Descriptive Titles

Use descriptive dialog titles:

```php
ModalSelectFilter::make('category')
    ->dialogTitle('Select Product Category')
    ->modelClass(Category::class);
```

### Clear Feedback

The package provides visual and audible feedback:

- Loading spinners with `aria-busy`
- Error messages with `role="alert"`
- Success confirmations via screen reader announcements

## Testing Accessibility

You can test accessibility using:

1. **Keyboard-only navigation** - Tab through all filters without using a mouse
2. **Screen readers** - Use VoiceOver (Mac), NVDA, or JAWS (Windows)
3. **Browser DevTools** - Use the Accessibility tree inspector
4. **Automated tools** - axe DevTools, WAVE, or Lighthouse

### Example Test

```php
it('has proper ARIA attributes', function () {
    $html = view('filament-dcat-filters::filters.modal-select', [
        'filterName' => 'test',
        'component' => $mockComponent,
        // ... other props
    ])->render();

    expect($html)
        ->toContain('role="combobox"')
        ->toContain('aria-haspopup="dialog"')
        ->toContain(':aria-expanded');
});
```

## Browser Support

Accessibility features are supported in all modern browsers:

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

Screen reader support:

- VoiceOver (macOS/iOS)
- NVDA (Windows)
- JAWS (Windows)
- TalkBack (Android)

## Contributing

If you find accessibility issues, please report them on GitHub. We take accessibility seriously and prioritize fixes for accessibility-related bugs.
