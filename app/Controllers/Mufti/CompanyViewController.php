<?php
namespace App\Controllers\Mufti;
use Core\Controller;
class CompanyViewController extends Controller { public function show($id){ $this->view('mufti/company',['id'=>$id]); } public function approve($id){ echo 'Approved '.(int)$id; } }