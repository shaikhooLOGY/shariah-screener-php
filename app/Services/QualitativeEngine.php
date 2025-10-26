<?php
namespace App\Services;
class QualitativeEngine {
  public function autoBucket(string $sector, string $desc): string {
    $s = strtolower($sector.' '.$desc);
    $red = ['bank','nbfc lend','insurance','casino','brewery','distillery','adult','porn','pork'];
    foreach($red as $w) if (str_contains($s,$w)) return 'non_permissible';
    $grey = ['ott','media','stream','hotel','bar','airline','fintech','platform','marketplace','ecommerce'];
    foreach($grey as $w) if (str_contains($s,$w)) return 'grey';
    return 'permissible';
  }
}