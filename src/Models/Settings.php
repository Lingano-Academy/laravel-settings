<?php

namespace Vocabia\LaravelSettings\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Support\Facades\Config;

class Settings extends Model
{
    use HasUlids;

    protected $guarded = [];

    public function getTable()
    {
        return Config::get('settings.table_name', parent::getTable());
    }

    protected $casts = [
        'json_value' => 'array',
    ];
}