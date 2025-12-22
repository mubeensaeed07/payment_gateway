<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Slab extends Model
{
    protected $fillable = [
        'admin_id',
        'slab_number',
        'from_amount',
        'to_amount',
        'charge',
        'onelink_fee',
    ];

    protected $casts = [
        'from_amount' => 'decimal:2',
        'to_amount' => 'decimal:2',
        'charge' => 'decimal:2',
        'onelink_fee' => 'decimal:2',
    ];

    /**
     * Get the admin that owns this slab
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
