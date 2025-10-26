<?php
namespace App\Middleware; class CsrfGuard { public function token(){return '';} public function verify(){ return true; } }