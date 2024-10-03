<?php
namespace Drupal\sigma_payment\Plugin\Commerce\PaymentGateway;

use Drupal\commerce_payment\Entity\PaymentInterface;
use Drupal\commerce_payment\Entity\PaymentMethodInterface;
use Drupal\commerce_payment\Exception\PaymentGatewayException;
use Drupal\commerce_payment\PaymentMethodTypeManager;
use Drupal\commerce_payment\PaymentTypeManager;
use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\OnsitePaymentGatewayBase;
use Drupal\commerce_price\Price;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the Onsite Checkout payment gateway.
 *
 * @CommercePaymentGateway(
 *   id = "sigma_payment_onsite",
 *   label = @Translation("Sigma Payment"),
 *   display_label = @Translation("SigmaPayment"),
 *    forms = {
 *     "add-payment" = "Drupal\sigma_payment\PluginForm\Onsite\PaymentMethodAddForm",
 *   },
 *   payment_method_types = {"credit_card"},
 *   modes = {
 *     "SANDBOX" = @Translation("SANDBOX"),
 *     "PRODUCTION" = @Translation("PRODUCTION"),
 *   },
 *   credit_card_types = {
 *     "mastercard", "visa", "amex",
 *   },
 * )
 */
class Onsite extends OnsitePaymentGatewayBase implements OnsiteInterface {
  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition,
                              EntityTypeManagerInterface $entity_type_manager,
                              PaymentTypeManager $payment_type_manager,
                              PaymentMethodTypeManager $payment_method_type_manager,
                              TimeInterface $time) {
    parent::__construct($configuration, $plugin_id, $plugin_definition,
      $entity_type_manager, $payment_type_manager, $payment_method_type_manager, $time);
  }
  public function defaultConfiguration() {
    return [
        'merchant_id' => '',
        'terminal_id_colones' => '',
        'terminal_id_dollars' => '',
        'merchant_id_sandbox' => '',
        'terminal_id_sandbox' => '',
      ] + parent::defaultConfiguration();
  }

  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['merchant_id'] = [
      '#type' => 'number',
      '#title' => $this->t('Merchant ID'),
      '#description' => $this->t('A unique number provided by Sigma that identifies you as a merchant.'),
      '#default_value' => $this->configuration['merchant_id'],
      '#size' => 6,
      '#required' => TRUE,
    ];
    $form['terminal_id_colones'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Terminal ID Colones'),
      '#description' => $this->t('A unique number provided by Sigma that identifies Terminal.'),
      '#default_value' => $this->configuration['terminal_id_colones'],
      '#size' => 12,
      '#required' => TRUE,
    ];
    $form['terminal_id_dollars'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Terminal ID Dollars'),
      '#description' => $this->t('A unique number provided by Sigma that identifies Terminal.'),
      '#default_value' => $this->configuration['terminal_id_dollars'],
      '#size' => 12,
      '#required' => TRUE,
    ];
    $form['merchant_id_sandbox'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Merchant / SANDBOX'),
      '#description' => $this->t('A unique number provided by Sigma that identifies you as a merchant.'),
      '#default_value' => $this->configuration['merchant_id_sandbox'],
      '#size' => 12,
      '#required' => TRUE,
    ];
    $form['terminal_id_sandbox'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Terminal ID / SANDBOX'),
      '#description' => $this->t('A unique number provided by Sigma that identifies Terminal.'),
      '#default_value' => $this->configuration['terminal_id_sandbox'],
      '#size' => 12,
      '#required' => TRUE,
    ];

    return $form;
  }
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $values = $form_state->getValue($form['#parents']);
    $this->configuration['merchant_id'] = $values['merchant_id'];
    $this->configuration['terminal_id_colones'] = $values['terminal_id_colones'];
    $this->configuration['terminal_id_dollars'] = $values['terminal_id_dollars'];
    $this->configuration['merchant_id_sandbox'] = $values['merchant_id_sandbox'];
    $this->configuration['terminal_id_sandbox'] = $values['terminal_id_sandbox'];
  }
  /**
   * @inheritDoc
   */
  public function createPayment(PaymentInterface $payment, $capture = TRUE) {
    // TODO: Implement createPayment() method.
    $this->assertPaymentState($payment, ['new']);
    $message = $this->t('We encountered an error processing your payment method. Please verify your details and try again.');
    try {
      $amount = $payment->getAmount()->getNumber();
      $payment_method = $payment->getPaymentMethod();
      $response = \Drupal::service('sigma_payment.transaction_manager')->saleTransaction($payment_method->getOwnerId(),
        $payment->getOrderId(), $payment_method->getRemoteId(), $amount, $payment->getAmount()->getCurrencyCode());

      if ($response) {
        $payment->setRemoteId($response);
        $next_state = $capture ? 'completed' : 'authorization';
        $payment->setState($next_state);
        $payment->save();
      } else {
        $this->messenger()->addError($message);
        $order = \Drupal\commerce_order\Entity\Order::load($payment->getOrderId());
        $checkout_flow = $order->get('checkout_flow')->first()->get('entity')->getTarget()->getValue()->getPlugin();
        $step_id = $checkout_flow->getPane('payment_information')->getStepId();
        // Redirect to payment method form.
        $checkout_flow->redirectToStep($step_id);
      }
    }
    catch (\PaymentGatewayAPI\Exception $e) {
      throw new PaymentGatewayException('Payment gateway error');
    }
    catch (DeclineException $e) {
      $this->messenger()->addError($message);
      $order = \Drupal\commerce_order\Entity\Order::load($payment->getOrderId());
      $checkout_flow = $order->get('checkout_flow')->first()->get('entity')->getTarget()->getValue()->getPlugin();
      $step_id = $checkout_flow->getPane('payment_information')->getStepId();
      $checkout_flow->redirectToStep($step_id);
    }
    catch (PaymentGatewayException $e) {
      $this->messenger()->addError($message);
      $order = \Drupal\commerce_order\Entity\Order::load($payment->getOrderId());
      $checkout_flow = $order->get('checkout_flow')->first()->get('entity')->getTarget()->getValue()->getPlugin();
      $step_id = $checkout_flow->getPane('payment_information')->getStepId();
      $checkout_flow->redirectToStep($step_id);
    }

  }

  /**
   * @inheritDoc
   */
  public function createPaymentMethod(PaymentMethodInterface $payment_method, array $payment_details) {
    // TODO: Implement createPaymentMethod() method.
    $required_keys = [
      'type', 'number', 'expiration',
    ];
    foreach ($required_keys as $required_key) {
      if (empty($payment_details[$required_key])) {
        throw new \InvalidArgumentException(sprintf('$payment_details must contain the %s key.', $required_key));
      }
    }
    $owner_id = $payment_method->getOwnerId();
    $search_customer = \Drupal::service('sigma_payment.customer_manager')->searchCustomerByUid($owner_id);
    $owner = $payment_method->getOwner();
    if (!$search_customer) {
      $email = $owner->getEmail();
      $new_customer = \Drupal::service('sigma_payment.customer_manager')->createCustomer($owner_id, $email);
      if (!$new_customer) {
        throw new \InvalidArgumentException(sprintf(t('Tuvimos un problema en este proceso, por favor comunicarse con el administrador.')));
      }
      $this->setRemoteCustomerId($owner, $new_customer->CustomerId);
    }
    else {
      $this->setRemoteCustomerId($owner, $search_customer->CustomerId);
    }

    $year = substr($payment_details['expiration']['year'], -2);
    $expiration = $payment_details['expiration']['month'] . $year;
    $card_token = \Drupal::service('sigma_payment.card_manager')->addCard($owner_id, $payment_details['card_name'] ? $payment_details['card_name'] : '',
      $payment_details['number'], $expiration, $payment_details['security_code']);
    $payment_method->setRemoteId($card_token);

    $payment_method->save();
  }

  /**
   * @inheritDoc
   */
  public function deletePaymentMethod(PaymentMethodInterface $payment_method) {
    // TODO: Implement deletePaymentMethod() method.
    // Delete the remote record.
    $owner_id = $payment_method->getOwnerId();

    try {
      $result = \Drupal::service('sigma_payment.card_manager')->updateStatusCard($owner_id, $payment_method->getRemoteId(), 'Inactive');
      ErrorHelper::handleErrors($result);
    }
    catch (\Braintree\Exception $e) {
      ErrorHelper::handleException($e);
    }
    // Delete the local entity.
    $payment_method->delete();
  }

  /**
   * @inheritDoc
   */
  public function capturePayment(PaymentInterface $payment, Price $amount = NULL) {
    // TODO: Implement capturePayment() method.
    $this->assertPaymentState($payment, ['authorization']);
    // If not specified, capture the entire amount.
    $amount = $amount ?: $payment->getAmount();

    // Perform the capture request here, throw an exception if it fails.
    try {
      $amount = $payment->getAmount()->getNumber();
      $payment_method = $payment->getPaymentMethod();
      $response = \Drupal::service('sigma_payment.transaction_manager')->saleTransaction($payment_method->getOwnerId(),
        $payment->getOrderId(), $payment_method->getRemoteId(), $amount);
    }
    catch (\Exception $e) {
      $this->logger->log('error', 'Error message about the failure');
      throw new PaymentGatewayException('Error message about the failure');
    }

    $payment->setState('completed');
    $payment->setAmount($amount);
    $payment->save();
  }

  /**
   * @inheritDoc
   */
  public function refundPayment(PaymentInterface $payment, Price $amount = NULL) {
    // TODO: Implement refundPayment() method.
    $this->assertPaymentState($payment, ['completed', 'partially_refunded']);
    // If not specified, refund the entire amount.
    $amount = $amount ?: $payment->getAmount();
    $this->assertRefundAmount($payment, $amount);

    // Perform the refund request here, throw an exception if it fails.
    try {
      $remote_id = $payment->getRemoteId();
      $decimal_amount = $amount->getNumber();
      $result = \Drupal::service('sigma_payment.transaction_manager')->refundTransaction($remote_id,
        $decimal_amount, $payment->getAmount()->getCurrencyCode());
    }
    catch (\Exception $e) {
      $this->logger->log('error', 'Error message about the failure');
      throw new PaymentGatewayException('Error message about the failure');
    }

    // Determine whether payment has been fully or partially refunded.
    $old_refunded_amount = $payment->getRefundedAmount();
    $new_refunded_amount = $old_refunded_amount->add($amount);
    if ($new_refunded_amount->lessThan($payment->getAmount())) {
      $payment->setState('partially_refunded');
    }
    else {
      $payment->setState('refunded');
    }

    $payment->setRefundedAmount($new_refunded_amount);
    $payment->save();
  }

  /**
   * @inheritDoc
   */
  public function voidPayment(PaymentInterface $payment) {
    // TODO: Implement voidPayment() method.
    $this->assertPaymentState($payment, ['authorization']);
    // Perform the void request here, throw an exception if it fails.
    $payment->setState('authorization_voided');
    $payment->save();
  }
}
