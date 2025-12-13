<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    protected $fillable = [
        'customer_id',
        'admin_id',
        'reference_id',
        'invoice_number',
        'amount',
        'charge',
        'due_date',
        'expiry_date',
        'amount_after_due_date',
        'description',
        'status',
        'paid_at',
        'next_payment_due_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'charge' => 'decimal:2',
        'amount_after_due_date' => 'decimal:2',
        'due_date' => 'date',
        'expiry_date' => 'date',
        'paid_at' => 'datetime',
        'next_payment_due_at' => 'datetime',
    ];

    /**
     * Get the customer that owns the invoice
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the admin that created the invoice
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
