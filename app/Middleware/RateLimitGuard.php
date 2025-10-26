<?php
namespace App\Middleware; class RateLimitGuard { public function check($key,$limit,$window){ return true; } }