<?php

namespace Fazzinipierluigi\LaraccoonLayouts\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DatagridLayout extends Model
{
    protected $table = 'datagrid_layouts';

    protected $fillable = [
        'user_id',
        'page_key',
        'name',
        'layout_data',
        'is_public',
        'is_default',
    ];

    protected $casts = [
        'layout_data' => 'array',
        'is_public' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('raccoon_layouts.user_model'));
    }
}
