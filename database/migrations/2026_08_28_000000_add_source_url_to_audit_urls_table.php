<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function getConnection()
    {
        return config('audit.database.connection');
    }

    public function up(): void
    {
        Schema::table('audit_urls', function (Blueprint $table) {
            $table->text('source_url')->nullable()->after('is_external');
        });
    }

    public function down(): void
    {
        Schema::table('audit_urls', function (Blueprint $table) {
            $table->dropColumn('source_url');
        });
    }
};
