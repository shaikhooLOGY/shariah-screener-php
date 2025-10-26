<?php
namespace App\Controllers\Admin;
use Core\Controller;
class CompaniesController extends Controller { public function index(){ $this->view('admin/companies'); } }