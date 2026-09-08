<?php

namespace ThePrimeStudio\Audit\Models;

use Illuminate\Database\Eloquent\Model;

class AuditSecurityResult extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_https' => 'boolean',
        'hsts_enabled' => 'boolean',
        'ssl_expiry' => 'datetime',
    ];

    public function getConnectionName()
    {
        return config('audit.database.connection') ?? parent::getConnectionName();
    }

    public function url()
    {
        return $this->belongsTo(AuditUrl::class, 'audit_url_id');
    }
}
