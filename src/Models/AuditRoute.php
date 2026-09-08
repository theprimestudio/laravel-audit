<?php

namespace ThePrimeStudio\Audit\Models;

use Illuminate\Database\Eloquent\Model;

class AuditRoute extends Model
{
    protected $guarded = [];

    protected $casts = [
        'methods' => 'array',
        'middleware' => 'array',
        'is_protected' => 'boolean',
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
