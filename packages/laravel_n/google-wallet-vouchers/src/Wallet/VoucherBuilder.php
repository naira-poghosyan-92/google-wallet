<?php
namespace LaravelN\GoogleWalletVouchers\Wallet;
use Carbon\Carbon;

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
    $this->data['state'] = __('voucherwallet::messages.' . $value);
    return $this;
  }

  public function setValidTimeInterval(string $start, string $end): self {
    $validFrom                       = Carbon::parse($start);
    $validTo                         = Carbon::parse($end);
    $now                             = Carbon::now();
    $this->data['validTimeInterval'] = [
      'start' => [
        'date' => $validFrom,
      ],
      'end'   => [
        'date' => $validTo,
      ],
    ];
    if ($now->lt($validFrom)) {
      $this->setState('inactive');
    } elseif ($now->gt($validTo)) {
      $this->setState('expired');
    } else {
      $this->setState('active');
    }

    return $this;
  }

  public function setTextModulesData(array $values): self {
    $this->data['textModulesData'] = [];
    foreach ($values as $value) {
      array_push($this->data['textModulesData'],
        ['id'             => $value[0],
          'localizedHeader' => LocalizedField::make('en-US', $value[1]),
          'localizedBody'   => LocalizedField::make('en-US', $value[2]),
        ]);
    }
    $state = $this->data['state'];
    if ($this->data['state']) {
      array_push($this->data['textModulesData'],
        ['id'             => 'status',
          'localizedHeader' => LocalizedField::make('en-US', __('voucherwallet::messages.status')),
          'localizedBody'   => LocalizedField::make('en-US', $state),
        ]);
    }
    return $this;
  }

  public function build() {
    return $this->data;
  }

  public function getObjectId(): string {
    return $this->data['id'];
  }

  public function getState() {
    return $this->data['state'];
  }

  public function getValidTimeInterval() {
    return $this->data['validTimeInterval'];
  }
}
?>
