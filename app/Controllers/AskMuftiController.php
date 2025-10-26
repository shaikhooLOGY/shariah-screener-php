<?php
namespace App\Controllers;
use Core\Controller;
class AskMuftiController extends Controller { public function form(){ $this->view('user/ask-mufti'); } public function submit(){ echo 'Submitted'; } }