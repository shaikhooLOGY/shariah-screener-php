<?php
use function Core\route;
route('GET','/dashboard/admin', [App\Controllers\Admin\DashboardController::class,'index']);
route('GET','/dashboard/admin/suggestions', [App\Controllers\Admin\SuggestionsController::class,'queue']);
route('POST','/dashboard/admin/suggestions/{id}', [App\Controllers\Admin\SuggestionsController::class,'accept']);
route('GET','/dashboard/admin/moderation', [App\Controllers\Admin\ModerationController::class,'index']);
route('GET','/dashboard/admin/evidence', [App\Controllers\Admin\EvidenceController::class,'index']);
route('GET','/dashboard/admin/ingestion', [App\Controllers\Admin\IngestionController::class,'index']);
route('GET','/dashboard/admin/companies', [App\Controllers\Admin\CompaniesController::class,'index']);
route('GET','/dashboard/admin/methodology', [App\Controllers\Admin\DashboardController::class,'methodology']);