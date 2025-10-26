<?php
namespace App\Services\Auth; class JwtService { public function issue($uid){return 'token';} public function verify($tok){return true;} }
