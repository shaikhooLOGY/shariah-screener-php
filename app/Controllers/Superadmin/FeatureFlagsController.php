<?php
namespace App\Controllers\Superadmin;
use Core\Controller;
class FeatureFlagsController extends Controller { public function index(){ $this->view('superadmin/feature-flags'); } }