<?php
use function Core\route;
route('GET','/', [App\Controllers\HomeController::class,'index']);
route('GET','/company/{symbol}', [App\Controllers\CompanyController::class,'profile']);
route('GET',  '/company/{symbol}/discussion', [App\Controllers\CompanyDiscussionController::class,'index']);
route('POST', '/company/{symbol}/discussion', [App\Controllers\CompanyDiscussionController::class,'store']);

route('GET',  '/company/{symbol}/suggest',    [App\Controllers\CompanySuggestController::class,'form']);
route('POST', '/company/{symbol}/suggest',    [App\Controllers\CompanySuggestController::class,'submit']);
route('GET','/profile', [App\Controllers\UserController::class,'profile']);
route('GET','/learn', [App\Controllers\LearnController::class,'index']);
route('GET','/ask-mufti', [App\Controllers\AskMuftiController::class,'form']);
route('POST','/ask-mufti', [App\Controllers\AskMuftiController::class,'submit']);
route('GET','/login', [App\Controllers\AuthController::class,'loginForm']);
route('POST','/login', [App\Controllers\AuthController::class,'login']);
route('GET','/signup', [App\Controllers\AuthController::class,'signupForm']);
route('POST','/signup', [App\Controllers\AuthController::class,'signup']);
route('GET','/inbox', [App\Controllers\UserController::class,'inbox']);
route('GET','/versions', [App\Controllers\LearnController::class,'versions']);
route('GET','/privacy', [App\Controllers\LearnController::class,'privacy']);
route('GET','/terms', [App\Controllers\LearnController::class,'terms']);
route('GET','/contact', [App\Controllers\HomeController::class,'contact']);
route('GET','/health', [App\Controllers\HealthController::class,'index']);
