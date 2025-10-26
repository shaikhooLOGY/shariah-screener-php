<?php
namespace App\Controllers\Mufti;
use Core\Controller;
class ReviewQueueController extends Controller { public function index(){ $this->view('mufti/review-queue'); } }