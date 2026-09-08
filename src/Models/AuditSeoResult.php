<?php

namespace ThePrimeStudio\Audit\Models;

use Illuminate\Database\Eloquent\Model;

class AuditSeoResult extends Model
{
    protected $guarded = [];

    protected $casts = [
        'h1_headings' => 'array',
        'schema_markup' => 'array',
        'has_open_graph' => 'boolean',
        'has_twitter_card' => 'boolean',
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
