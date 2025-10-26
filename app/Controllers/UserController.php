<?php
namespace App\Controllers;
use Core\Controller;
class UserController extends Controller { public function profile(){ $this->view('user/profile'); } public function inbox(){ $this->view('user/inbox'); } }