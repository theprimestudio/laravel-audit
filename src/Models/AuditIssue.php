<?php

namespace ThePrimeStudio\Audit\Models;

use Illuminate\Database\Eloquent\Model;

class AuditIssue extends Model
{
    protected $guarded = [];

    protected $casts = [
        'evidence' => 'array',
        'first_detected_at' => 'datetime',
        'last_detected_at' => 'datetime',
    ];

    public function getConnectionName()
    {
        return config('audit.database.connection') ?? parent::getConnectionName();
    }

    public function run()
    {
        return $this->belongsTo(AuditRun::class, 'audit_run_id');
    }
}
