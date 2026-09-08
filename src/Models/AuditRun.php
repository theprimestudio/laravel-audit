<?php

namespace ThePrimeStudio\Audit\Models;

use Illuminate\Database\Eloquent\Model;

class AuditRun extends Model
{
    protected $guarded = [];

    protected $casts = [
        'overall_score' => 'decimal:2',
        'seo_score' => 'decimal:2',
        'security_score' => 'decimal:2',
        'performance_score' => 'decimal:2',
        'reliability_score' => 'decimal:2',
        'laravel_score' => 'decimal:2',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function getConnectionName()
    {
        return config('audit.database.connection') ?? parent::getConnectionName();
    }

    public function urls()
    {
        return $this->hasMany(AuditUrl::class);
    }

    public function issues()
    {
        return $this->hasMany(AuditIssue::class);
    }

    public function metrics()
    {
        return $this->hasMany(AuditMetric::class);
    }

    public function exceptions()
    {
        return $this->hasMany(AuditException::class);
    }

    public function routes()
    {
        return $this->hasMany(AuditRoute::class);
    }

    public function queries()
    {
        return $this->hasMany(AuditQuery::class);
    }
}
