<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExternalProviderLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'api_type',
        'invoice_number',
        'customer_name',
        'customer_number',
        'request_data',
        'response_data',
        'response_status',
        'is_successful',
        'error_message',
        'external_provider_url',
    ];

    protected $casts = [
        'request_data' => 'array',
        'response_data' => 'array',
        'is_successful' => 'boolean',
        'response_status' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the admin that owns this log
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
