<?php
namespace LaravelN\GoogleWalletVouchers\Wallet;
enum State {
case STATE_UNSPECIFIED;
case ACTIVE;
case COMPLETED;
case EXPIRED;
case INACTIVE;
}

class VoucherBuilder {
  protected array $data = [];
  public function setObjectId(string $value): self {
    $this->data['id'] = $value;
    return $this;
  }
  public function setTitle(string $default, array $translations = []): self {
    $this->data['cardTitle'] = LocalizedField::make('en-US', $default, $translations);
    return $this;
  }

  public function setHeader(string $default, array $translations = []): self {
    $this->data['header'] = LocalizedField::make('en-US', $default, $translations);
    return $this;
  }

  public function setBarcode(string $code, bool $isCodeShow = false): self {
    $this->data['barcode'] = [
      'type'          => 'qrCode',
      'value'         => $code,
      'alternateText' => $isCodeShow ? $code : '',
    ];
    return $this;
  }

  public function setLogo(string $value): self {
    $this->data['logo'] = [
      "sourceUri" => [
        "uri" => $value,
      ],
    ];
    return $this;
  }

  public function setHeroImage(string $value): self {
    $this->data['heroImage'] = [
      "sourceUri" => [
        "uri" => $value,
      ],
    ];
    return $this;
  }

  public function setBackColor(string $value): self {
    $this->data['hexBackgroundColor'] = $value;
    return $this;
  }

  public function setState(string $value): self {
    $this->data['state'] = $value;
    return $this;
  }

  public function setValidTimeInterval(string $startDate, string $endDate): self {
    $this->data['validTimeInterval'] = [
      'start' => [
        'date' => $startDate,
      ],
      'end'   => [
        'date' => $endDate,
      ],
    ];
    return $this;
  }

  public function setTextModulesData(array $values): self {
    $this->data['textModulesData'] = [];
    foreach ($values as $value) {
      $hTranslations = array_key_exists('hTranslations', $value) ? $value['hTranslations'] : [];
      $bTranslations = array_key_exists('bTranslations', $value) ? $value['bTranslations'] : [];
      array_push($this->data['textModulesData'],
        ['id'             => $value['id'],
          'localizedHeader' => LocalizedField::make('en-US', $value['header'], $hTranslations),
          'localizedBody'   => LocalizedField::make('en-US', $value['body'], $bTranslations),
        ]);

    }
    return $this;

  }

  public function build(): array {
    return $this->data;
  }
}
?>