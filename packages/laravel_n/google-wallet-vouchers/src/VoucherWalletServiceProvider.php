<?php
namespace LaravelN\GoogleWalletVouchers;

use Illuminate\Support\ServiceProvider;

class VoucherWalletServiceProvider extends ServiceProvider {
  /**
   * Register services.
   */
  public function register(): void {
    $this->mergeConfigFrom(
      __DIR__ . '/config/voucherwallet.php',
      'voucherwallet'
    );
  }

  /**
   * Bootstrap services.
   */
  public function boot(): void {

    $this->publishes([
      __DIR__ . '/config/voucherwallet.php' => config_path('voucherwallet.php'),
    ], 'voucherwallet');

  }
}
