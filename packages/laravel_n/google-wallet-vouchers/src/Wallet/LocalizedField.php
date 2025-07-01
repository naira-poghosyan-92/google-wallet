<?php
namespace LaravelN\GoogleWalletVouchers\Wallet;
class LocalizedField {
  public static function make(string $defaultLang, string $defaultValue, array $translations = []): array {
    $data = [
      'defaultValue'     => [
        'language' => $defaultLang,
        'value'    => $defaultValue,
      ],
      'translatedValues' => [],
    ];

    foreach ($translations as $lang => $value) {
      $data['translatedValues'][] = [
        'language' => $lang,
        'value'    => $value,
      ];
    }
    
    return $data;
  }
}
?>