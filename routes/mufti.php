<?php
use function Core\route;
route('GET','/dashboard/ulama', [App\Controllers\Mufti\ReviewQueueController::class,'index']);
route('GET','/dashboard/ulama/company/{id}', [App\Controllers\Mufti\CompanyViewController::class,'show']);
route('POST','/dashboard/ulama/company/{id}/approve', [App\Controllers\Mufti\CompanyViewController::class,'approve']);
route('GET','/dashboard/ulama/decisions', [App\Controllers\Mufti\DecisionsController::class,'index']);
route('GET','/dashboard/ulama/profile', [App\Controllers\Mufti\ProfileController::class,'edit']);