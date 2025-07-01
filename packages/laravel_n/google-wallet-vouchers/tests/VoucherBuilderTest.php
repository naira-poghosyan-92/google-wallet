<?php
namespace Tests\Unit;

use Carbon\Carbon;
use LaravelN\GoogleWalletVouchers\Wallet\VoucherBuilder;
use Tests\TestCase;

class VoucherBuilderTest extends TestCase {
  private VoucherBuilder $builder;

  /**
   * This method is called before each test.
   */
  protected function setUp(): void {
    parent::setUp();
    $this->builder = new VoucherBuilder();
  }

  /**
   * This method is called after each test.
   */
  protected function tearDown(): void {
    // Reset Carbon's mock time after each test to avoid side-effects.
    Carbon::setTestNow();
    parent::tearDown();
  }

  public function test_set_id_field() {
    $objectId = 'voucher-test-id';
    $this->builder->setObjectId($objectId);

    $this->assertSame($objectId, $this->builder->getObjectId());
    $this->assertSame($objectId, $this->builder->build()['id']);
  }

  public function test_set_title_field() {
    $data = $this->builder->setTitle('Test Title')
      ->build();

    $this->assertEquals('Test Title', $data['cardTitle']['defaultValue']['value']);
    $this->assertEquals('en-US', $data['cardTitle']['defaultValue']['language']);
  }

  public function test_set_title_field_with_de_language() {
    $data = $this->builder->setTitle('Test Title', ['de-DE' => 'Testtitel'])
      ->build();

    $this->assertEquals('Test Title', $data['cardTitle']['defaultValue']['value']);
    $this->assertEquals('de-DE', $data['cardTitle']['translatedValues'][0]['language']);
    $this->assertEquals('Testtitel', $data['cardTitle']['translatedValues'][0]['value']);
  }

  public function test_set_header_field_with_default_language() {
    $data = $this->builder->setHeader('Header Text')
      ->build();

    $this->assertEquals('Header Text', $data['header']['defaultValue']['value']);
    $this->assertEquals('en-US', $data['header']['defaultValue']['language']);
  }

  public function test_set_header_field_with_de_language() {
    $data = $this->builder->setHeader('Header Text', ['de-DE' => 'Kopfzeilentext'])
      ->build();

    $this->assertEquals('Header Text', $data['header']['defaultValue']['value']);
    $this->assertEquals('de-DE', $data['header']['translatedValues'][0]['language']);
    $this->assertEquals('Kopfzeilentext', $data['header']['translatedValues'][0]['value']);
  }

  public function test_it_can_set_barcode_with_code_hidden(): void {
    $this->builder->setBarcode('12345ABC', false);

    $expected = [
      'barcode' => [
        'type'          => 'qrCode',
        'value'         => '12345ABC',
        'alternateText' => '',
      ],
    ];

    $this->assertEquals($expected, $this->builder->build());
  }

  public function test_it_can_set_barcode_with_code_shown(): void {
    $this->builder->setBarcode('12345ABC', true);

    $expected = [
      'barcode' => [
        'type'          => 'qrCode',
        'value'         => '12345ABC',
        'alternateText' => '12345ABC',
      ],
    ];

    $this->assertEquals($expected, $this->builder->build());
  }

  public function test_it_can_set_logo(): void {
    $uri = 'https://example.com/logo.png';
    $this->builder->setLogo($uri);

    $expected = [
      'logo' => [
        'sourceUri' => ['uri' => $uri],
      ],
    ];

    $this->assertEquals($expected, $this->builder->build());
  }

  public function test_it_can_set_hero_image(): void {
    $uri = 'https://example.com/hero.png';
    $this->builder->setHeroImage($uri);

    $expected = [
      'heroImage' => [
        'sourceUri' => ['uri' => $uri],
      ],
    ];

    $this->assertEquals($expected, $this->builder->build());
  }

  public function test_it_can_set_back_color(): void {
    $this->builder->setBackColor('#FFFFFF');

    $this->assertSame('#FFFFFF', $this->builder->build()['hexBackgroundColor']);
  }

  public function test_set_state_field() {
    $data = $this->builder->setState('active')
      ->build();

    $this->assertEquals('active', $this->builder->getState());
  }

  public function test_set_valid_time_interval_field() {
    $data = $this->builder->setValidTimeInterval('2025-06-24 08:54:28', '2025-07-04 08:54:28')
      ->build();

    $this->assertEquals('2025-06-24 08:54:28', $data['validTimeInterval']['start']['date']);
    $this->assertEquals('2025-07-04 08:54:28', $data['validTimeInterval']['end']['date']);
  }

  public function test_valid_time_interval_sets_state_to_inactive(): void {
    $this->builder->setValidTimeInterval('2500-06-30 00:00:00', '2500-07-10 00:00:00');

    $this->assertEquals('inactive', $this->builder->getState());
  }

  public function test_valid_time_interval_sets_state_to_active(): void {
    $now               = Carbon::now();
    $endDate           = $now->copy()->addDays(10);
    $formatedStartDate = $now->setTimezone('UTC');
    $formatedEndDate   = $endDate->setTimezone('UTC');
    $this->builder->setValidTimeInterval($formatedStartDate, $formatedEndDate);

    $this->assertEquals('active', $this->builder->getState());
  }

  public function test_valid_time_interval_sets_state_to_expired() {
    $this->builder->setValidTimeInterval('2025-06-01 00:00:00', '2025-06-10 00:00:00');

    $this->assertEquals('expired', $this->builder->getState());
  }

  public function test_build_method_returns_the_complete_data_array(): void {
    $this->builder
      ->setObjectId('issuer.object_123')
      ->setTitle('My Voucher')
      ->setHeader('Voucher')
      ->setBarcode('12345678', true)
      ->setLogo('https://example.com/logo.png')
      ->setHeroImage('https://example.com/image.png')
      ->setBackColor('#000000')
      ->setState('expired');

    $data = $this->builder->build();

    $this->assertIsArray($data);
    $this->assertArrayHasKey('id', $data);
    $this->assertArrayHasKey('cardTitle', $data);
    $this->assertArrayHasKey('header', $data);
    $this->assertArrayHasKey('barcode', $data);
    $this->assertArrayHasKey('heroImage', $data);
    $this->assertArrayHasKey('logo', $data);
    $this->assertArrayHasKey('hexBackgroundColor', $data);
    $this->assertArrayHasKey('state', $data);

    $this->assertSame('issuer.object_123', $data['id']);
    $this->assertSame('#000000', $data['hexBackgroundColor']);
    $this->assertSame('expired', $data['state']);
  }
}
