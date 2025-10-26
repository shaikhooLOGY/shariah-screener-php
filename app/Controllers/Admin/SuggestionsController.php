<?php
namespace App\Controllers\Admin;
use Core\Controller;
class SuggestionsController extends Controller { public function queue(){ $this->view('admin/suggestions'); } public function accept($id){ echo 'Accepted '.(int)$id; } public function reject($id){ echo 'Rejected '.(int)$id; } }