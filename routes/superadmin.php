<?php
use function Core\route;
route('GET','/dashboard/superadmin', [App\Controllers\Superadmin\DashboardController::class,'index']);
route('GET','/dashboard/superadmin/profiles', [App\Controllers\Superadmin\ProfilesController::class,'index']);
route('POST','/dashboard/superadmin/profiles', [App\Controllers\Superadmin\ProfilesController::class,'save']);
route('GET','/dashboard/superadmin/roles', [App\Controllers\Superadmin\RolesController::class,'index']);
route('POST','/dashboard/superadmin/roles', [App\Controllers\Superadmin\RolesController::class,'save']);
route('GET','/dashboard/superadmin/ulama', [App\Controllers\Superadmin\UlamaBoardController::class,'index']);
route('GET','/dashboard/superadmin/audit-legal', [App\Controllers\Superadmin\AuditLegalController::class,'index']);
route('GET','/dashboard/superadmin/governance', [App\Controllers\Superadmin\GovernanceController::class,'index']);
route('GET','/dashboard/superadmin/feature-flags', [App\Controllers\Superadmin\FeatureFlagsController::class,'index']);
route('GET','/dashboard/superadmin/master-sheet', [App\Controllers\Superadmin\MasterSheetController::class,'masterSheet']);
route('POST','/dashboard/superadmin/master-sheet/edit/{id}', [App\Controllers\Superadmin\MasterSheetController::class,'editRatio']);
route('POST','/dashboard/superadmin/master-sheet/delete/{id}', [App\Controllers\Superadmin\MasterSheetController::class,'deleteRatio']);