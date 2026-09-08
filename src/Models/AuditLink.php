<?php

namespace ThePrimeStudio\Audit\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLink extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_broken' => 'boolean',
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
