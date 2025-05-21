<?php

namespace App\Models;

use App\Support\LocationLabel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Poll extends Model
{
    /** @use HasFactory<\Database\Factories\PollFactory> */
    use HasFactory;

    protected $fillable = [
        'question',
        'slug',
        'closes_at',
        'city',
        'region',
        'country_code',
        'latitude',
        'longitude',
    ];

    protected function casts(): array
    {
        return [
            'closes_at' => 'datetime',
            // Cast to numbers so distance math gets floats, not decimal strings.
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    /**
     * Use the slug for shareable, route-model-bound URLs.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Human-readable place this poll was asked from — "Seattle, WA" in the US,
     * or just the country name elsewhere. Null when the poll has no location.
     */
    protected function location(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => LocationLabel::make($this->city, $this->region, $this->country_code),
        );
    }

    /**
     * @return HasMany<PollOption, $this>
     */
    public function options(): HasMany
    {
        return $this->hasMany(PollOption::class)->orderBy('position');
    }

    /**
     * @return HasMany<Vote, $this>
     */
    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function isClosed(): bool
    {
        return $this->closes_at !== null && $this->closes_at->isPast();
    }
}
