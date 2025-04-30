<?php
namespace LaravelN\GoogleWalletVouchers\Wallet;

use Firebase\JWT\JWT;
use Google\Client;
use Google\Service\Exception;
use Google\Service\Walletobjects;
use Google\Service\Walletobjects\GenericClass;
use Google\Service\Walletobjects\GenericObject;

const TEMPLATE_INFO = ["classTemplateInfo" => [
  "cardTemplateOverride" => [
    "cardRowTemplateInfos" => [
      [
        "threeItems" => [
          "startItem"  => [
            "firstValue" => [
              "fields" => [
                [
                  "fieldPath" => "object.textModulesData['value_init']",
                ],
              ],
            ],
          ],
          "middleItem" => [
            "firstValue" => [
              "fields" => [
                [
                  "fieldPath" => "object.textModulesData['status']",
                ],
              ],
            ],
          ],
          "endItem"    => [
            "firstValue" => [
              "fields" => [
                [
                  "fieldPath" => "object.textModulesData['value']",
                ],
              ],
            ],
          ],

        ],
      ],
      [
        "twoItems" => [
          "startItem" => [
            "firstValue" => [
              "fields" => [
                [
                  "fieldPath" => "object.textModulesData['issued_on']",
                ],
              ],
            ],
          ],
          "endItem"   => [
            "firstValue" => [
              "fields" => [
                [
                  "fieldPath" => "object.textModulesData['valid_until']",
                ],
              ],
            ],
          ],

        ],
      ],
    ],
  ],
]];

class GoogleWalletClient {

  public function __construct() {
    $this->issuerId = config('voucherwallet.issuer_id');
    $this->client   = new Client();
    $this->client->setApplicationName('Laravel Google Wallet');
    $this->client->setAuthConfig(config('voucherwallet.service_account_file'));
    $this->client->setScopes(['https://www.googleapis.com/auth/wallet_object.issuer']);
    $this->service   = new Walletobjects($this->client);
    $this->className = config('voucherwallet.class_id');
  }

  public function createGenericClass() {
    $classId = "{$this->issuerId}.{$this->className}";
    try {
      $this->service->genericclass->get($classId);
      print_r("---Class $classId already exists!---");
      return;
    } catch (Exception $e) {
      $newClass = new GenericClass(array_merge([
        'id' => $classId,
      ], TEMPLATE_INFO));
      $this->service->genericclass->insert($newClass);
      print_r("---Created $classId class!---");
    }

  }

  public function generateSaveUrl(array $voucherObject): string {
    $serviceAccount =
      json_decode(file_get_contents(config('voucherwallet.service_account_file')), true);
    $voucherObject['id']      = "{$this->issuerId}.{$voucherObject['id']}";
    $voucherObject['classId'] = "{$this->issuerId}.{$this->className}";
    $jwtPayload               = [
      'iss'     => $serviceAccount['client_email'],
      'aud'     => 'google',
      'typ'     => 'savetowallet',
      'payload' => [
        'genericObjects' => [$voucherObject],
      ],
    ];
    //Jenerated link to store votcher in google wallet
    $jwt = JWT::encode($jwtPayload, $serviceAccount['private_key'], 'RS256');
    return "https://pay.google.com/gp/v/save/{$jwt}";
  }

  public function updateVoucher(string $objectId, array $updateData) {
    try {
      $genericObj    = new GenericObject($updateData);
      $updatedObject = $this->service->genericobject->patch("{$this->issuerId}.{$objectId}", $genericObj);
      print_r("---Voucher updated successfully!---");
    } catch (Exception $e) {
      print_r("---Error updating voucher: " . $e->getMessage());
    }
  }
}
?>
