<?php

use Cooper\FilamentDcatFilters\Concerns\HasRelationship;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Create a concrete test class that uses the trait
class HasRelationshipTestFilter
{
    use HasRelationship;

    public function getRelationshipName(): ?string
    {
        return $this->relationshipName;
    }

    public function getRelationshipTitleColumn(): ?string
    {
        return $this->relationshipTitleColumn;
    }

    public function testApplyRelationshipConstraint(Builder $query, string $column, string $operator, mixed $value): Builder
    {
        return $this->applyRelationshipConstraint($query, $column, $operator, $value);
    }

    public function testApplyRelationshipWhereIn(Builder $query, string $column, array $values, bool $negate = false): Builder
    {
        return $this->applyRelationshipWhereIn($query, $column, $values, $negate);
    }
}

function freshRelationshipQuery(): Builder
{
    $model = new class extends Model
    {
        protected $table = 'test_items';

        public function tags(): HasMany
        {
            return $this->hasMany(self::class, 'test_item_id');
        }
    };

    return $model->newQuery();
}

describe('Relationship Configuration', function () {
    it('has no relationship by default', function () {
        $filter = new HasRelationshipTestFilter;

        expect($filter->hasRelationship())->toBeFalse();
        expect($filter->getRelationshipName())->toBeNull();
        expect($filter->getRelationshipTitleColumn())->toBeNull();
    });

    it('can set relationship with name only', function () {
        $filter = new HasRelationshipTestFilter;
        $result = $filter->relationship('customer');

        expect($filter->hasRelationship())->toBeTrue();
        expect($filter->getRelationshipName())->toBe('customer');
        expect($filter->getRelationshipTitleColumn())->toBeNull();
        expect($result)->toBe($filter);
    });

    it('can set relationship with name and title column', function () {
        $filter = new HasRelationshipTestFilter;
        $filter->relationship('customer', 'full_name');

        expect($filter->hasRelationship())->toBeTrue();
        expect($filter->getRelationshipName())->toBe('customer');
        expect($filter->getRelationshipTitleColumn())->toBe('full_name');
    });
});

describe('Fluent Interface', function () {
    it('returns self for method chaining', function () {
        $filter = new HasRelationshipTestFilter;
        $result = $filter->relationship('author', 'name');

        expect($result)->toBeInstanceOf(HasRelationshipTestFilter::class);
    });
});

describe('applyRelationshipConstraint', function () {
    it('adds whereHas subquery to the query', function () {
        $filter = new HasRelationshipTestFilter;
        $filter->relationship('tags');

        $query = $filter->testApplyRelationshipConstraint(freshRelationshipQuery(), 'name', '=', 'php');
        $sql = $query->toSql();

        expect($sql)->toContain('exists');
        expect($query->getBindings())->toContain('php');
    });
});

describe('applyRelationshipWhereIn', function () {
    it('adds whereHas + whereIn subquery to the query', function () {
        $filter = new HasRelationshipTestFilter;
        $filter->relationship('tags');

        $query = $filter->testApplyRelationshipWhereIn(freshRelationshipQuery(), 'id', [1, 2, 3]);
        $sql = $query->toSql();

        expect($sql)->toContain('exists');
        expect($sql)->toContain(' in ');
        expect($query->getBindings())->toContain(1);
        expect($query->getBindings())->toContain(2);
        expect($query->getBindings())->toContain(3);
    });

    it('adds whereHas + whereNotIn subquery when negate is true', function () {
        $filter = new HasRelationshipTestFilter;
        $filter->relationship('tags');

        $query = $filter->testApplyRelationshipWhereIn(freshRelationshipQuery(), 'id', [1, 2], negate: true);
        $sql = $query->toSql();

        expect($sql)->toContain('exists');
        expect($sql)->toContain('not in');
        expect($query->getBindings())->toContain(1);
        expect($query->getBindings())->toContain(2);
    });
});
