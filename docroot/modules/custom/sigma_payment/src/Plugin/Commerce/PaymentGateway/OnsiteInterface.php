<?php


namespace Drupal\sigma_payment\Plugin\Commerce\PaymentGateway;


use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\OnsitePaymentGatewayInterface;
use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\SupportsAuthorizationsInterface;
use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\SupportsRefundsInterface;

interface OnsiteInterface extends OnsitePaymentGatewayInterface,
  SupportsAuthorizationsInterface, SupportsRefundsInterface {

}
