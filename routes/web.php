<?php

use Illuminate\Support\Facades\Route;

$middlewareClass = \ThePrimeStudio\Audit\Http\Middleware\AuthorizeAudit::class;

Route::middleware(["{$middlewareClass}:dashboard"])->get('/', 'DashboardController@index')->name('dashboard');

Route::middleware(["{$middlewareClass}:websites"])->get('/websites', 'DashboardController@websites')->name('websites');
Route::middleware(["{$middlewareClass}:seo"])->get('/seo', 'DashboardController@seo')->name('seo');
Route::middleware(["{$middlewareClass}:security"])->get('/security', 'DashboardController@security')->name('security');
Route::middleware(["{$middlewareClass}:performance"])->get('/performance', 'DashboardController@performance')->name('performance');
Route::middleware(["{$middlewareClass}:laravel"])->get('/laravel', 'DashboardController@laravel')->name('laravel');
Route::middleware(["{$middlewareClass}:api"])->get('/api', 'DashboardController@api')->name('api');

// Issues
Route::group(['prefix' => 'issues'], function () use ($middlewareClass) {
    Route::middleware(["{$middlewareClass}:issues"])->get('/', 'IssueController@index')->name('issues.index');
    Route::middleware(["{$middlewareClass}:actions.resolve_issue"])->post('/{issue}/resolve', 'IssueController@resolve')->name('issues.resolve');
    Route::middleware(["{$middlewareClass}:actions.ignore_issue"])->post('/{issue}/ignore', 'IssueController@ignore')->name('issues.ignore');
    Route::middleware(["{$middlewareClass}:actions.delete_issue"])->delete('/{issue}', 'IssueController@delete')->name('issues.delete');
});

// Audits
Route::group(['prefix' => 'audits'], function () use ($middlewareClass) {
    Route::middleware(["{$middlewareClass}:audits"])->get('/', 'AuditController@index')->name('audits.index');
    Route::middleware(["{$middlewareClass}:actions.run_audit"])->post('/run', 'AuditController@run')->name('audits.run');
    Route::middleware(["{$middlewareClass}:actions.run_audit"])->get('/progress', 'AuditController@progress')->name('audits.progress');
    Route::middleware(["{$middlewareClass}:actions.run_audit"])->get('/progress-data', 'AuditController@progressData')->name('audits.progress-data');
    Route::middleware(["{$middlewareClass}:actions.run_audit"])->post('/resolve-stuck', 'AuditController@resolveStuck')->name('audits.resolve-stuck');
    Route::middleware(["{$middlewareClass}:audits"])->get('/{run}', 'AuditController@show')->name('audits.show');
    Route::middleware(["{$middlewareClass}:actions.delete_audit"])->delete('/{run}', 'AuditController@delete')->name('audits.delete');
});

// Reports
Route::group(['prefix' => 'reports'], function () use ($middlewareClass) {
    Route::middleware(["{$middlewareClass}:reports"])->get('/', 'ReportController@index')->name('reports.index');
    Route::middleware(["{$middlewareClass}:actions.export_report"])->get('/export', 'ReportController@export')->name('reports.export');
});

// Settings
Route::group(['prefix' => 'settings'], function () use ($middlewareClass) {
    Route::middleware(["{$middlewareClass}:settings"])->get('/', 'SettingController@index')->name('settings.index');
    Route::middleware(["{$middlewareClass}:actions.manage_settings"])->post('/', 'SettingController@update')->name('settings.update');
});
