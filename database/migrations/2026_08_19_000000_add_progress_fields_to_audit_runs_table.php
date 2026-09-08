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
        Schema::table('audit_runs', function (Blueprint $table) {
            $table->string('progress_message')->nullable()->after('status');
            $table->integer('progress_percent')->default(0)->after('progress_message');
        });
    }

    public function down(): void
    {
        Schema::table('audit_runs', function (Blueprint $table) {
            $table->dropColumn(['progress_message', 'progress_percent']);
        });
    }
};
