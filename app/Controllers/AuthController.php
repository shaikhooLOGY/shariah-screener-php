<?php
namespace App\Controllers;
use Core\Controller;
class AuthController extends Controller { public function loginForm(){ $this->view('user/login'); } public function login(){ echo 'Login'; } public function signupForm(){ $this->view('user/signup'); } public function signup(){ echo 'Signup'; } }