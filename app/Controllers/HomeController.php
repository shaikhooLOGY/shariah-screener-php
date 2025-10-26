<?php
namespace App\Controllers;
use Core\Controller;
class HomeController extends Controller { public function index(){ $this->view('user/home'); } public function contact(){ echo 'Contact'; } }