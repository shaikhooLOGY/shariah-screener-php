<?php
use function Core\route;
use App\Controllers\GenericPageController;
use App\Controllers\CompanyController;
use App\Controllers\CompanyDiscussionController;
use App\Controllers\CompanySuggestController;
use App\Controllers\HealthController;

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
route('GET', '/login', [GenericPageController::class, 'login']);
route('GET', '/register', [GenericPageController::class, 'register']);
route('GET', '/forgot', [GenericPageController::class, 'forgot']);

// Dashboards
route('GET', '/dashboard/ulama', [GenericPageController::class, 'ulamaDashboard']);
route('GET', '/dashboard/ulama/reviews', [GenericPageController::class, 'ulamaReviews']);
route('GET', '/dashboard/admin', [GenericPageController::class, 'adminDashboard']);
route('GET', '/dashboard/admin/companies', [GenericPageController::class, 'adminCompanies']);
route('GET', '/dashboard/admin/filings', [GenericPageController::class, 'adminFilings']);
route('GET', '/dashboard/admin/users', [GenericPageController::class, 'adminUsers']);
route('GET', '/dashboard/admin/settings', [GenericPageController::class, 'adminSettings']);

// Health checks
route('GET', '/health', [HealthController::class, 'index']);
route('GET', '/prod-health', [HealthController::class, 'index']);
