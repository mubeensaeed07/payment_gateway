<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'admin_id',
        'name',
        'email',
        'phone_number',
        'payee_name',
        'reference_id',
        'user_number',
        'balance',
    ];

    /**
     * Get the admin that owns the customer
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Get all invoices for this customer
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get the latest unpaid invoice (excludes blocked invoices)
     */
    public function getLatestUnpaidInvoice()
    {
        return $this->invoices()
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * Get the balance from latest unpaid invoice
     */
    public function getLatestUnpaidBalance()
    {
        $latestUnpaid = $this->getLatestUnpaidInvoice();
        return $latestUnpaid ? $latestUnpaid->amount : 0;
    }
}
