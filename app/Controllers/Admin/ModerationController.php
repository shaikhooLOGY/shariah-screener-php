<?php
namespace App\Controllers\Admin;
use Core\Controller;
class ModerationController extends Controller { public function index(){ $this->view('admin/moderation'); } }