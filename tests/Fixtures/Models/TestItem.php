<?php

namespace Cooper\FilamentDcatFilters\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestItem extends Model
{
    protected $table = 'test_items';

    protected $guarded = [];

    public function category(): BelongsTo
    {
        return $this->belongsTo(TestCategory::class, 'category_id');
    }
}
