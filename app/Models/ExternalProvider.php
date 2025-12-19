<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class ExternalProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'username',
        'password',
        'bill_enquiry_url',
        'bill_payment_url',
    ];

    protected $hidden = [
        'password',
    ];

    /**
     * Get the admin that owns this external provider
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Set password attribute (encrypt)
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Crypt::encryptString($value);
    }

    /**
     * Get password attribute (decrypt)
     */
    public function getPasswordAttribute($value)
    {
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return $value;
        }
    }
}
