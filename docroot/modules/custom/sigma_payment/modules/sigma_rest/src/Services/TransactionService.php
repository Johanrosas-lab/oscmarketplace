<?php

namespace Drupal\sigma_rest\Services;


use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_payment\Entity\Payment;
use Drupal\commerce_payment\Entity\PaymentMethod;
use Drupal\commerce_price\Price;

class TransactionService {

  /**
   * Process to paid the order.
   *
   * @param $order_data
   *
   * @return bool
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function transaction($order_data) {
    // Load payment method.
    $payment_method = PaymentMethod::load($order_data['payment_id']);

    // Transaction with SIGMA_PAYMENT method.
    $response = \Drupal::service('sigma_payment.transaction_manager')->saleTransaction($payment_method->getOwnerId(),
      $order_data['order_id'], $payment_method->getRemoteId(), $order_data['number'], $order_data['currency']);

    // Create payment
    $payment = Payment::create([
      'type' => 'payment_default',
      'payment_gateway' => $payment_method->getPaymentGatewayId(),
      'payment_method' => $payment_method->id(),
      'order_id' => $order_data['order_id'],
      'remote_id' => $response ? $response : NULL,
      'amount' => ['number' => $order_data['number'], 'currency_code' => $order_data['currency']],
      'state' => $response ? 'completed' : 'voided',
      'payment_gateway_mode' => $payment_method->getPaymentGatewayMode(),
      'completed' => $response ? \Drupal::time()->getCurrentTime() : NULL
    ]);
    $payment->save();
    // Update the order by $transaction_result.
    $this->updateOrder($order_data['order_id'], $response ? TRUE : FALSE);
    return $response ? TRUE : FALSE;
  }

  /**
   * Update the order by transaction result.
   *
   * @param $order_id
   * @param $status
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function updateOrder($order_id, $status) {
    // Load order
    $order = Order::load($order_id);

    // Validate if the transaction was completed.
    if ($status) {
      // Execute paid action.
      $order->setTotalPaid(new Price($order->getTotalPrice()->getNumber(),
        $order->getTotalPrice()->getCurrencyCode()));
      $order->set('state', 'completed');
      $order->setCompletedTime(\Drupal::time()->getCurrentTime());
    }
    else {
      $order->set('state', 'failed');
      // Set next payment the next day.
      $order->set('field_next_payment', date('Y-m-d', strtotime('tomorrow')));
    }
    $order->save();
    return;
  }
}
