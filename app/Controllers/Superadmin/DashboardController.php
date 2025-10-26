<?php
namespace App\Controllers\Superadmin;
use Core\Controller;
class DashboardController extends Controller { public function index(){ $this->view('superadmin/dashboard'); } }