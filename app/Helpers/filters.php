<?php
namespace App\Helpers; class Filters { public static function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES,'UTF-8'); } }
