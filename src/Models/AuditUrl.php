<?php

namespace ThePrimeStudio\Audit\Models;

use Illuminate\Database\Eloquent\Model;

class AuditUrl extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_external' => 'boolean',
    ];

    public function getConnectionName()
    {
        return config('audit.database.connection') ?? parent::getConnectionName();
    }

    public function run()
    {
        return $this->belongsTo(AuditRun::class, 'audit_run_id');
    }

    public function httpResult()
    {
        return $this->hasOne(AuditHttpResult::class);
    }

    public function seoResult()
    {
        return $this->hasOne(AuditSeoResult::class);
    }

    public function securityResult()
    {
        return $this->hasOne(AuditSecurityResult::class);
    }

    public function performanceResult()
    {
        return $this->hasOne(AuditPerformanceResult::class);
    }

    public function links()
    {
        return $this->hasMany(AuditLink::class);
    }

    public function images()
    {
        return $this->hasMany(AuditImage::class);
    }
}
