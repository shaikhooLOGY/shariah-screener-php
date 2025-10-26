<?php
use function Core\route;
use App\Controllers\HealthController;
use App\Controllers\CompanyController;

route('GET', '/health', [HealthController::class, 'index']);
route('GET', '/company/{symbol}', [CompanyController::class, 'show']);
route('GET', '/prod-health', [HealthController::class, 'index']);
