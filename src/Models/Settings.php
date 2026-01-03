<?php

namespace Vocabia\LaravelSettings\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class Settings extends Model
{
    use HasUlids;

    protected $casts = [
        'is_locked' => 'boolean',
        'json_value' => 'array',
    ];

    protected $guarded = [];

    public function getTable()
    {
        return Config::get('settings.table_name', parent::getTable());
    }


    public function getPayloadAttribute(): mixed
    {
        // If the data type is array or JSON, read from the json_value column
        if (in_array($this->type, ['array', 'json'])) {
            return $this->json_value;
        }

        // If the data type was encrypted, decrypt it
        if ($this->type === 'encrypted') {
            try {
                return Crypt::decrypt($this->value);
            } catch (\Exception $e) {
                Log::error($e);
                return $this->value;
            }
        }

        // 3. Cast other data types (Laravel Casts)
        return match ($this->type) {
            'integer' => (int) $this->value,
            'float'   => (float) $this->value,
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            default   => $this->value,
        };
    }

    public function setPayload($value, string $type = null): void
    {
        $type = $type ?? $this->type ?? 'string';
        $this->type = $type;

        if (in_array($type, ['array', 'json'])) {
            $this->json_value = $value;
            $this->value = null; // To be sure
        } elseif ($type === 'encrypted') {
            $this->value = Crypt::encrypt($value);
            $this->json_value = null;
        } else {
            $this->value = (string) $value;
            $this->json_value = null;
        }

        $this->save();
    }
}