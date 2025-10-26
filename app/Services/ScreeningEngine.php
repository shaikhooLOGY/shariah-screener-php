<?php
namespace App\Services;
class ScreeningEngine {
  public function compute(array $f): array {
    $assets = max(1e-9, (float)($f['total_assets'] ?? 0));
    $revenue = max(1e-9, (float)($f['revenue'] ?? 0));
    return [
      'debt_pct'    => ((float)($f['total_debt'] ?? 0)) / $assets,
      'interest_pct'=> ((float)($f['interest_income'] ?? 0)) / $revenue,
      'liquid_pct'  => (((float)($f['cash'] ?? 0)) + ((float)($f['receivables'] ?? 0))) / $assets,
      'nonsh_pct'   => ((float)($f['non_shariah_income'] ?? 0)) / $revenue,
    ];
  }
}