<?php
namespace App\Controllers\Superadmin;
use Core\Controller;
class ProfilesController extends Controller { public function index(){ $this->view('superadmin/profiles'); } public function save(){ echo 'Saved'; } }