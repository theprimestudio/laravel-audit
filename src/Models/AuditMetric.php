<?php

namespace ThePrimeStudio\Audit\Models;

use Illuminate\Database\Eloquent\Model;

class AuditMetric extends Model
{
    protected $guarded = [];

    public function getConnectionName()
    {
        return config('audit.database.connection') ?? parent::getConnectionName();
    }

    public function run()
    {
        return $this->belongsTo(AuditRun::class, 'audit_run_id');
    }
}
