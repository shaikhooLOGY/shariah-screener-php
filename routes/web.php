<?php
use function Core\route;
use function Core\guard;
use App\Controllers\GenericPageController;
use App\Controllers\CompanyController;
use App\Controllers\CompanyDiscussionController;
use App\Controllers\CompanySuggestController;
use App\Controllers\HealthController;
use App\Controllers\AuthController;
use App\Controllers\Superadmin\UsersController as SAUsersController;
use App\Controllers\Superadmin\EngineController as SAEngineController;
use App\Controllers\Superadmin\BucketsController as SABucketsController;
use App\Controllers\Superadmin\SystemController as SASystemController;
use App\Controllers\Superadmin\AuditController as SAAuditController;
use App\Controllers\Admin\ApprovalsController as AdminApprovalsController;
use App\Controllers\Superadmin\ApprovalsController as SAApprovalsController;
use App\Controllers\Superadmin\CmvController as SACmvController;

// Public pages
route('GET', '/', [GenericPageController::class, 'home']);
route('GET', '/explore', [GenericPageController::class, 'explore']);
route('GET', '/companies', [GenericPageController::class, 'companies']);
route('GET', '/methodology', [GenericPageController::class, 'methodology']);
route('GET', '/standards', [GenericPageController::class, 'standards']);
route('GET', '/case-studies', [GenericPageController::class, 'caseStudies']);
route('GET', '/faq', [GenericPageController::class, 'faq']);
route('GET', '/glossary', [GenericPageController::class, 'glossary']);
route('GET', '/about', [GenericPageController::class, 'about']);
route('GET', '/contact', [GenericPageController::class, 'contact']);
route('GET', '/terms', [GenericPageController::class, 'terms']);
route('GET', '/privacy', [GenericPageController::class, 'privacy']);
route('GET', '/disclaimer', [GenericPageController::class, 'disclaimer']);
route('GET', '/purification', [GenericPageController::class, 'purification']);
route('GET', '/scholars', [GenericPageController::class, 'scholars']);
route('GET', '/scholar/{slug}', [GenericPageController::class, 'scholarProfile']);
route('GET', '/learn', [GenericPageController::class, 'learn']);
route('GET', '/articles', [GenericPageController::class, 'articles']);
route('GET', '/articles/{slug}', [GenericPageController::class, 'articleShow']);
route('GET', '/discussions', [GenericPageController::class, 'discussions']);
route('GET', '/suggest-ratios', [GenericPageController::class, 'suggestRatios']);

// Company profile & related
route('GET', '/company/{symbol}', [CompanyController::class, 'show']);
route('GET', '/company/{symbol}/discussion', [CompanyDiscussionController::class, 'index']);
route('POST', '/company/{symbol}/discussion', [CompanyDiscussionController::class, 'store']);
route('GET', '/company/{symbol}/suggest', [CompanySuggestController::class, 'form']);
route('POST', '/company/{symbol}/suggest', [CompanySuggestController::class, 'submit']);

// Auth
route('GET', '/login', [AuthController::class, 'show']);
route('POST', '/login', [AuthController::class, 'login']);
route('POST', '/logout', [AuthController::class, 'logout']);
route('GET', '/register', [GenericPageController::class, 'register']);
route('GET', '/forgot', [GenericPageController::class, 'forgot']);

// Dashboards
guard('mufti', function () {
    route('GET', '/dashboard/ulama', [GenericPageController::class, 'ulamaDashboard']);
    route('GET', '/dashboard/ulama/reviews', [GenericPageController::class, 'ulamaReviews']);
});

guard('admin', function () {
    route('GET', '/dashboard/admin', [GenericPageController::class, 'adminDashboard']);
    route('GET', '/dashboard/admin/companies', [GenericPageController::class, 'adminCompanies']);
    route('GET', '/dashboard/admin/filings', [GenericPageController::class, 'adminFilings']);
    route('GET', '/dashboard/admin/users', [GenericPageController::class, 'adminUsers']);
    route('GET', '/dashboard/admin/settings', [GenericPageController::class, 'adminSettings']);
});

route('POST', '/admin/approvals', [AdminApprovalsController::class, 'create']);

// Health checks
route('GET', '/health', [HealthController::class, 'index']);
route('GET', '/prod-health', [HealthController::class, 'index']);

guard('superadmin', function () {
    route('GET', '/dashboard/superadmin', [SASystemController::class, 'index']);
    route('GET', '/dashboard/superadmin/users', [SAUsersController::class, 'index']);
    route('POST', '/sa/users/{id}/role', [SAUsersController::class, 'updateRole']);
    route('POST', '/sa/users/{id}/status', [SAUsersController::class, 'updateStatus']);
    route('POST', '/sa/users/bulk-role', [SAUsersController::class, 'bulkRole']);

    route('GET', '/dashboard/superadmin/engine', [SAEngineController::class, 'index']);
    route('POST', '/sa/engine/run', [SAEngineController::class, 'run']);

    route('GET', '/dashboard/superadmin/buckets', [SABucketsController::class, 'index']);
    route('POST', '/sa/buckets/{companyId}/move', [SABucketsController::class, 'move']);
    route('GET', '/sa/buckets/export', [SABucketsController::class, 'exportCsv']);

    route('GET', '/dashboard/superadmin/system', [SASystemController::class, 'system']);
    route('POST', '/sa/flags/{key}', [SASystemController::class, 'toggleFlag']);

    route('GET', '/dashboard/superadmin/audit', [SAAuditController::class, 'index']);
    route('GET', '/sa/audit/export', [SAAuditController::class, 'exportCsv']);

    route('POST', '/superadmin/approvals/{id}/approve', [SAApprovalsController::class, 'approve']);
    route('POST', '/superadmin/approvals/{id}/reject', [SAApprovalsController::class, 'reject']);

    route('GET', '/dashboard/superadmin/cmv', [SACmvController::class, 'index']);
    route('POST', '/dashboard/superadmin/cmv/run', [SACmvController::class, 'run']);
    route('GET', '/dashboard/superadmin/cmv/{id}/diff', [SACmvController::class, 'diff']);
    route('POST', '/dashboard/superadmin/cmv/{id}/publish', [SACmvController::class, 'publish']);
    route('POST', '/dashboard/superadmin/cmv/{id}/rollback', [SACmvController::class, 'rollback']);
});
