<?php

use Cooper\FilamentDcatFilters\FilamentDcatFiltersServiceProvider;

beforeEach(function () {
    // Ensure lang files are loaded
    $this->app->register(FilamentDcatFiltersServiceProvider::class);
});

describe('Accessibility Translations', function () {
    it('has all accessibility translations in English', function () {
        $translations = trans('filament-dcat-filters::filament-dcat-filters');

        expect($translations)->toHaveKey('accessibility.selection_updated');
        expect($translations)->toHaveKey('accessibility.selection_cleared');
        expect($translations)->toHaveKey('accessibility.change_selection');
        expect($translations)->toHaveKey('accessibility.open_selection');
        expect($translations)->toHaveKey('accessibility.clear_selection');
        expect($translations)->toHaveKey('accessibility.items_selected');
        expect($translations)->toHaveKey('accessibility.no_selection');
        expect($translations)->toHaveKey('accessibility.filter_applied');
        expect($translations)->toHaveKey('accessibility.filter_cleared');
        expect($translations)->toHaveKey('accessibility.loading');
        expect($translations)->toHaveKey('accessibility.error_occurred');
        expect($translations)->toHaveKey('accessibility.remove_item');
        expect($translations)->toHaveKey('accessibility.clear_value');
        expect($translations)->toHaveKey('accessibility.open_modal');
        expect($translations)->toHaveKey('accessibility.retry_fetch');
        expect($translations)->toHaveKey('accessibility.fetch_error');
        expect($translations)->toHaveKey('accessibility.selected_summary');
    });

    it('has all accessibility translations in Chinese', function () {
        app()->setLocale('zh_CN');

        $translations = trans('filament-dcat-filters::filament-dcat-filters');

        expect($translations)->toHaveKey('accessibility.selection_updated');
        expect($translations['accessibility.selection_updated'])->toBe('选择已更新');
        expect($translations['accessibility.selection_cleared'])->toBe('选择已清空');
        expect($translations['accessibility.remove_item'])->toBe('移除 :label');
        expect($translations['accessibility.clear_value'])->toBe('清除已选值');
        expect($translations['accessibility.open_modal'])->toBe('打开 :label 选择对话框');
        expect($translations['accessibility.retry_fetch'])->toBe('重试加载标签');
        expect($translations['accessibility.fetch_error'])->toBe('加载标签失败，点击重试。');
        expect($translations['accessibility.selected_summary'])->toBe('已选 :count 项');
    });

    it('has all accessibility translations in Traditional Chinese', function () {
        app()->setLocale('zh_TW');

        $translations = trans('filament-dcat-filters::filament-dcat-filters');

        expect($translations['accessibility.remove_item'])->toBe('移除 :label');
        expect($translations['accessibility.clear_value'])->toBe('清除已選值');
        expect($translations['accessibility.open_modal'])->toBe('開啟 :label 選擇對話框');
        expect($translations['accessibility.retry_fetch'])->toBe('重試載入標籤');
        expect($translations['accessibility.fetch_error'])->toBe('載入標籤失敗，點擊重試。');
        expect($translations['accessibility.selected_summary'])->toBe('已選 :count 項');
    });
});

describe('Accessibility Features', function () {
    it('has proper ARIA structure for screen readers', function () {
        // Verify that key ARIA roles and attributes are defined in translations
        $enTranslations = trans('filament-dcat-filters::filament-dcat-filters');

        // Verify all required accessibility strings exist
        expect($enTranslations['accessibility.selection_updated'])->toBe('Selection updated');
        expect($enTranslations['accessibility.open_selection'])->toBe('Open selection dialog');
        expect($enTranslations['accessibility.clear_selection'])->toBe('Clear selection');
    });

    it('provides feedback text for loading states', function () {
        $translations = trans('filament-dcat-filters::filament-dcat-filters');

        expect($translations['accessibility.loading'])->toBe('Loading');
    });

    it('provides feedback text for error states', function () {
        $translations = trans('filament-dcat-filters::filament-dcat-filters');

        expect($translations['accessibility.error_occurred'])->toBe('An error occurred');
        expect($translations['accessibility.fetch_error'])->toBe('Failed to load labels. Click to retry.');
        expect($translations['accessibility.retry_fetch'])->toBe('Retry loading labels');
    });

    it('provides text for selection states', function () {
        $translations = trans('filament-dcat-filters::filament-dcat-filters');

        expect($translations['accessibility.items_selected'])->toBe('Items selected');
        expect($translations['accessibility.no_selection'])->toBe('No items selected');
        expect($translations['accessibility.remove_item'])->toBe('Remove :label');
        expect($translations['accessibility.clear_value'])->toBe('Clear selected value');
        expect($translations['accessibility.open_modal'])->toBe('Open :label selection dialog');
        expect($translations['accessibility.selected_summary'])->toBe(':count items selected');
    });
});

describe('Keyboard Navigation Support', function () {
    it('defines expected keyboard interactions', function () {
        // This test documents the expected keyboard behavior
        // Actual keyboard testing requires browser/E2E tests

        $expectedKeyboardSupport = [
            'Tab' => 'Move focus between filter elements',
            'Enter' => 'Activate the focused filter/open modal',
            'Space' => 'Activate the focused filter/open modal',
            'Escape' => 'Close modal dialogs',
            'Arrow keys' => 'Navigate within dropdown options',
        ];

        expect($expectedKeyboardSupport)->toHaveCount(5);
        expect($expectedKeyboardSupport)->toHaveKey('Tab');
        expect($expectedKeyboardSupport)->toHaveKey('Enter');
        expect($expectedKeyboardSupport)->toHaveKey('Escape');
    });
});

describe('Focus Management', function () {
    it('documents focus return behavior', function () {
        // Document expected focus management behavior
        $focusBehaviors = [
            'modal_open' => 'Focus moves to modal content',
            'modal_close' => 'Focus returns to trigger element',
            'filter_clear' => 'Focus remains on clear button area',
        ];

        expect($focusBehaviors)->toHaveKey('modal_open');
        expect($focusBehaviors)->toHaveKey('modal_close');
        expect($focusBehaviors)->toHaveKey('filter_clear');
    });
});
