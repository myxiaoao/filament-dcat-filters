<?php

namespace Cooper\FilamentDcatFilters\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakeDcatFilterCommand extends Command
{
    protected $signature = 'make:dcat-filter {name : The name of the filter class}
                            {--type=basic : The filter type (basic, like, in, comparison, boolean, null, enum, range, regex, fulltext, json)}
                            {--force : Overwrite existing file}';

    protected $description = 'Create a new Dcat-style Filament filter class';

    protected array $filterTypes = [
        'basic' => 'Filament\Tables\Filters\Filter',
        'like' => 'Cooper\FilamentDcatFilters\Filters\LikeFilter',
        'in' => 'Cooper\FilamentDcatFilters\Filters\InFilter',
        'comparison' => 'Cooper\FilamentDcatFilters\Filters\ComparisonFilter',
        'boolean' => 'Cooper\FilamentDcatFilters\Filters\BooleanFilter',
        'null' => 'Cooper\FilamentDcatFilters\Filters\NullFilter',
        'enum' => 'Cooper\FilamentDcatFilters\Filters\EnumFilter',
        'range' => 'Cooper\FilamentDcatFilters\Filters\RangeFilter',
        'regex' => 'Cooper\FilamentDcatFilters\Filters\RegexFilter',
        'fulltext' => 'Cooper\FilamentDcatFilters\Filters\FullTextFilter',
        'json' => 'Cooper\FilamentDcatFilters\Filters\JsonFilter',
    ];

    public function handle(Filesystem $filesystem): int
    {
        $name = $this->argument('name');
        $type = $this->option('type');

        if (! isset($this->filterTypes[$type])) {
            $this->error("Invalid filter type: {$type}. Available types: ".implode(', ', array_keys($this->filterTypes)));

            return self::FAILURE;
        }

        $className = Str::studly($name);

        if (! Str::endsWith($className, 'Filter')) {
            $className .= 'Filter';
        }

        $namespace = $this->resolveNamespace();
        $path = $this->resolvePath($className);

        if ($filesystem->exists($path) && ! $this->option('force')) {
            $this->error("Filter [{$path}] already exists. Use --force to overwrite.");

            return self::FAILURE;
        }

        $filesystem->ensureDirectoryExists(dirname($path));

        $parentClass = $this->filterTypes[$type];
        $parentShortName = class_basename($parentClass);
        $stub = $this->buildStub($namespace, $className, $parentClass, $parentShortName, $type);

        $filesystem->put($path, $stub);

        $this->info("Filter [{$className}] created successfully at: {$path}");

        return self::SUCCESS;
    }

    protected function resolveNamespace(): string
    {
        return app()->getNamespace().'Filament\\Filters';
    }

    protected function resolvePath(string $className): string
    {
        return app_path('Filament/Filters/'.$className.'.php');
    }

    protected function buildStub(
        string $namespace,
        string $className,
        string $parentClass,
        string $parentShortName,
        string $type
    ): string {
        $useStatements = "use {$parentClass};";
        $traits = $this->getTraitsForType($type);
        $traitUses = '';
        $traitImports = '';

        foreach ($traits as $trait) {
            $traitImports .= "\nuse {$trait};";
            $traitUses .= "\n    use ".class_basename($trait).';';
        }

        $body = $this->getBodyForType($type);

        return <<<PHP
<?php

namespace {$namespace};

{$useStatements}{$traitImports}

class {$className} extends {$parentShortName}
{{$traitUses}
{$body}
}

PHP;
    }

    /**
     * @return array<string>
     */
    protected function getTraitsForType(string $type): array
    {
        $traits = [];

        if ($type === 'basic') {
            $traits[] = 'Cooper\FilamentDcatFilters\Concerns\HasColumnName';
            $traits[] = 'Cooper\FilamentDcatFilters\Concerns\HasInlineLabel';
        }

        return $traits;
    }

    protected function getBodyForType(string $type): string
    {
        return match ($type) {
            'basic' => <<<'BODY'
    protected function setUp(): void
    {
        parent::setUp();

        $this->columnSpan(1);
    }
BODY,
            default => <<<'BODY'
    //
BODY,
        };
    }
}
