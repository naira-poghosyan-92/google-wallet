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

    $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'voucherwallet');

    $this->publishes([
      __DIR__ . '/config/voucherwallet.php' => config_path('voucherwallet.php'),
    ], 'voucherwallet');

    $this->publishes([
      __DIR__.'/../resources/lang' => resource_path('lang/vendor/voucherwallet'),
    ], 'voucherwallet-translations');

  }
}
