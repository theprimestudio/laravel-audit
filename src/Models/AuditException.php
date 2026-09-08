<?php

namespace ThePrimeStudio\Audit\Models;

use Illuminate\Database\Eloquent\Model;

class AuditException extends Model
{
    protected $guarded = [];

    protected $casts = [
        'trace' => 'array',
        'occurred_at' => 'datetime',
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
