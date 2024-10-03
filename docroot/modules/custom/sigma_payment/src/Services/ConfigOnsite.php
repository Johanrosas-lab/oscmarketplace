<?php

namespace Drupal\sigma_payment\Services;
use Drupal\sigma_payment\SDK\Configuration\MetropagoGateway;

class ConfigOnsite {

  /**
   * Get commerce sigma_payment_onsite config.
   * @return bool
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getConfig() {
    // Get plugin type commerce payment gateway.
    $gateway = \Drupal::entityTypeManager()
      ->getStorage('commerce_payment_gateway')->loadByProperties([
        'plugin' => 'sigma_payment_onsite',
      ]);
    $plugin = reset($gateway);
    // Get plugin.
    $plugin = $plugin->getPlugin();
    if ($config = $plugin->getConfiguration()) {
      return $config;
    }
    \Drupal::logger('SIGMA_PAYMENT')
      ->error('<pre>' . print_r('No found sigma_payment_onsite', 1) . '</pre>');
    return FALSE;
  }

  /**
   * Initialize the SDK
   *
   * @param string $terminal
   *
   * @return bool|\MetropagoGateway
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function setUp($terminal = 'USD') {
    $config = $this->getConfig();
    if ($config) {
      try {
        if ($config['mode'] === 'PRODUCTION') {
          if ($terminal === 'USD') {
            return new MetropagoGateway($config['mode'], $config['merchant_id'], $config['terminal_id_dollars'], '', '');
          }
          else {
            return new MetropagoGateway($config['mode'], $config['merchant_id'], $config['terminal_id_colones'], '', '');
          }
        }
        else {
          return new MetropagoGateway($config['mode'], $config['merchant_id_sandbox'], $config['terminal_id_sandbox'], '', '');
        }
      } catch (\Exception $e) {
        \Drupal::logger('sigma_payment')
          ->error('<pre>' . print_r('Error: ' . $e, 1) . '</pre>');
      }
    }
    return FALSE;
  }
}
