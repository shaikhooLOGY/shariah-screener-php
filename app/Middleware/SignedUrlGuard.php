<?php
namespace App\Middleware; class SignedUrlGuard { public function verify($url){ return true; } }