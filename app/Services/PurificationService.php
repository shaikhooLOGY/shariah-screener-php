<?php
namespace App\Services; class PurificationService { public function calc(float $impurePct, float $dividend): float { return max(0.0,$impurePct)*max(0.0,$dividend);} }
