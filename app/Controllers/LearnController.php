<?php
namespace App\Controllers;
use Core\Controller;
class LearnController extends Controller { public function index(){ $this->view('user/learn'); } public function versions(){ $this->view('user/versions'); } public function privacy(){ $this->view('user/privacy'); } public function terms(){ $this->view('user/terms'); } }