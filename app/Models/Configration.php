<?php

namespace App\Models;

use App\Enums\ConfigrationTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Configration extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'key',
        'value',
        'type',
    ];

    public function scopeTerm($query)
    {
        return $query->where('type', ConfigrationTypeEnum::term->value);
    }

    public function scopeFaq($query)
    {
        return $query->where('type', ConfigrationTypeEnum::faq->value);
    }

    public function scopePolicy($query)
    {
        return $query->where('type', ConfigrationTypeEnum::policy->value);
    }
}
