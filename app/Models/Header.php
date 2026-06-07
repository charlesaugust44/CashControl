<?php

namespace App\Models;

use Database\Factories\HeaderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Header extends Model
{
    /** @use HasFactory<HeaderFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type',
        'rule',
        'default_amount',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'type' => 'string',
        'rule' => 'string',
        'default_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }
}
