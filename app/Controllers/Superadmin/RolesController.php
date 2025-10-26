<?php
namespace App\Controllers\Superadmin;
use Core\Controller;
class RolesController extends Controller { public function index(){ $this->view('superadmin/roles'); } public function save(){ echo 'Saved'; } }