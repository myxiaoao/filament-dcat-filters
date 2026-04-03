<?php

it('outputs markdown by default', function () {
    $outputPath = sys_get_temp_dir().'/test-matrix-md-'.uniqid().'.md';

    $this->artisan('dcat-filters:matrix', ['--output' => $outputPath])
        ->assertExitCode(0);

    $content = file_get_contents($outputPath);
    expect($content)
        ->toContain('# Filter Capability Matrix')
        ->toContain('LikeFilter')
        ->toContain('RangeFilter')
        ->toContain('FilterGroup');

    @unlink($outputPath);
});

it('outputs json format', function () {
    $outputPath = sys_get_temp_dir().'/test-matrix-json-'.uniqid().'.json';

    $this->artisan('dcat-filters:matrix', ['--format' => 'json', '--output' => $outputPath])
        ->assertExitCode(0);

    $data = json_decode(file_get_contents($outputPath), true);
    expect($data)->toBeArray()
        ->and($data[0])->toHaveKeys(['filter', 'state_type', 'fields', 'capabilities']);

    @unlink($outputPath);
});

it('writes to file with --output option', function () {
    $outputPath = sys_get_temp_dir().'/test-matrix-write-'.uniqid().'.md';

    $this->artisan('dcat-filters:matrix', ['--output' => $outputPath])
        ->assertExitCode(0);

    expect(file_exists($outputPath))->toBeTrue();

    $content = file_get_contents($outputPath);
    expect($content)
        ->toContain('# Filter Capability Matrix')
        ->toContain('LikeFilter');

    @unlink($outputPath);
});

it('lists all 22 filters in the matrix', function () {
    $outputPath = sys_get_temp_dir().'/test-matrix-count-'.uniqid().'.json';

    $this->artisan('dcat-filters:matrix', ['--format' => 'json', '--output' => $outputPath])
        ->assertExitCode(0);

    $data = json_decode(file_get_contents($outputPath), true);
    expect($data)->toHaveCount(22);

    @unlink($outputPath);
});
