<?php

use Cooper\FilamentDcatFilters\Concerns\HasRelationship;

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
