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
        Schema::create('audit_runs', function (Blueprint $table) {
            $table->id();
            $table->string('status')->default('pending'); // pending, running, completed, failed
            $table->decimal('overall_score', 5, 2)->nullable();
            $table->decimal('seo_score', 5, 2)->nullable();
            $table->decimal('security_score', 5, 2)->nullable();
            $table->decimal('performance_score', 5, 2)->nullable();
            $table->decimal('reliability_score', 5, 2)->nullable();
            $table->decimal('laravel_score', 5, 2)->nullable();
            $table->integer('urls_crawled')->default(0);
            $table->integer('issues_found')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('audit_urls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_run_id')->constrained('audit_runs')->cascadeOnDelete();
            $table->text('url');
            $table->string('path', 2048)->nullable();
            $table->boolean('is_external')->default(false);
            $table->integer('discovered_depth')->default(0);
            $table->timestamps();

            $table->index(['audit_run_id']);
        });

        Schema::create('audit_http_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_url_id')->constrained('audit_urls')->cascadeOnDelete();
            $table->integer('status_code')->nullable();
            $table->integer('response_time_ms')->nullable();
            $table->integer('ttfb_ms')->nullable();
            $table->bigInteger('response_size_bytes')->nullable();
            $table->string('content_type')->nullable();
            $table->text('redirect_url')->nullable();
            $table->json('headers')->nullable();
            $table->timestamps();

            $table->index(['audit_url_id']);
        });

        Schema::create('audit_seo_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_url_id')->constrained('audit_urls')->cascadeOnDelete();
            $table->string('title', 1024)->nullable();
            $table->text('meta_description')->nullable();
            $table->text('canonical_url')->nullable();
            $table->string('robots_meta')->nullable();
            $table->json('h1_headings')->nullable();
            $table->json('schema_markup')->nullable();
            $table->boolean('has_open_graph')->default(false);
            $table->boolean('has_twitter_card')->default(false);
            $table->integer('issues_count')->default(0);
            $table->timestamps();

            $table->index(['audit_url_id']);
        });

        Schema::create('audit_security_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_url_id')->constrained('audit_urls')->cascadeOnDelete();
            $table->boolean('is_https')->default(false);
            $table->boolean('hsts_enabled')->default(false);
            $table->string('csp_header', 2048)->nullable();
            $table->string('x_frame_options')->nullable();
            $table->string('x_content_type_options')->nullable();
            $table->string('referrer_policy')->nullable();
            $table->string('permissions_policy', 2048)->nullable();
            $table->timestamp('ssl_expiry')->nullable();
            $table->timestamps();

            $table->index(['audit_url_id']);
        });

        Schema::create('audit_performance_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_url_id')->constrained('audit_urls')->cascadeOnDelete();
            $table->bigInteger('memory_usage_bytes')->nullable();
            $table->integer('query_count')->default(0);
            $table->integer('query_time_ms')->default(0);
            $table->timestamps();

            $table->index(['audit_url_id']);
        });

        Schema::create('audit_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_url_id')->constrained('audit_urls')->cascadeOnDelete();
            $table->text('source_url');
            $table->text('target_url');
            $table->text('anchor_text')->nullable();
            $table->boolean('is_broken')->default(false);
            $table->integer('status_code')->nullable();
            $table->timestamps();

            $table->index(['audit_url_id']);
        });

        Schema::create('audit_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_url_id')->constrained('audit_urls')->cascadeOnDelete();
            $table->text('image_url');
            $table->text('alt_text')->nullable();
            $table->string('dimensions')->nullable();
            $table->bigInteger('file_size_bytes')->nullable();
            $table->boolean('is_lazy_loaded')->default(false);
            $table->timestamps();

            $table->index(['audit_url_id']);
        });

        Schema::create('audit_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_run_id')->constrained('audit_runs')->cascadeOnDelete();
            $table->string('category'); // security, seo, performance, reliability, laravel
            $table->string('type');
            $table->string('severity'); // critical, high, medium, low, info
            $table->text('url')->nullable();
            $table->string('title', 512);
            $table->text('description')->nullable();
            $table->json('evidence')->nullable();
            $table->text('recommendation')->nullable();
            $table->timestamp('first_detected_at')->nullable();
            $table->timestamp('last_detected_at')->nullable();
            $table->string('status')->default('open'); // open, acknowledged, ignored, resolved
            $table->timestamps();

            $table->index(['audit_run_id', 'status']);
            $table->index(['category']);
        });

        Schema::create('audit_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_run_id')->constrained('audit_runs')->cascadeOnDelete();
            $table->string('name');
            $table->string('value');
            $table->string('group')->nullable();
            $table->timestamps();

            $table->index(['audit_run_id']);
        });

        Schema::create('audit_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_run_id')->constrained('audit_runs')->cascadeOnDelete();
            $table->text('uri');
            $table->json('methods');
            $table->string('name')->nullable();
            $table->string('controller')->nullable();
            $table->json('middleware')->nullable();
            $table->boolean('is_protected')->default(false);
            $table->timestamps();

            $table->index(['audit_run_id']);
        });

        Schema::create('audit_exceptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_run_id')->constrained('audit_runs')->cascadeOnDelete();
            $table->string('class');
            $table->text('message');
            $table->json('trace')->nullable();
            $table->string('file')->nullable();
            $table->integer('line')->nullable();
            $table->timestamp('occurred_at')->nullable();
            $table->timestamps();

            $table->index(['audit_run_id']);
        });

        Schema::create('audit_queries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_run_id')->constrained('audit_runs')->cascadeOnDelete();
            $table->text('sql');
            $table->json('bindings')->nullable();
            $table->integer('time_ms');
            $table->string('connection')->nullable();
            $table->string('caller')->nullable();
            $table->timestamps();

            $table->index(['audit_run_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_queries');
        Schema::dropIfExists('audit_exceptions');
        Schema::dropIfExists('audit_routes');
        Schema::dropIfExists('audit_metrics');
        Schema::dropIfExists('audit_issues');
        Schema::dropIfExists('audit_images');
        Schema::dropIfExists('audit_links');
        Schema::dropIfExists('audit_performance_results');
        Schema::dropIfExists('audit_security_results');
        Schema::dropIfExists('audit_seo_results');
        Schema::dropIfExists('audit_http_results');
        Schema::dropIfExists('audit_urls');
        Schema::dropIfExists('audit_runs');
    }
};
