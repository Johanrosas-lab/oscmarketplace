<?php
namespace Drupal\sigma_payment\Services;

use Drupal\sigma_payment\SDK\Entities\Customer;
use Drupal\sigma_payment\SDK\Managers\CustomerManager;
use Drupal\sigma_payment\SDK\Entities\CustomerSearch;
use Drupal\sigma_payment\SDK\Entities\CustomerSearchOption;


class SigmaCustomerManager implements SigmaCustomerManagerInterface {

  /**
   * Registers and Update customer in SIGMA GATEWAY platform.
   *
   * @param $uid
   *    User ID ( Drupal )
   * @param $email
   *    User Email.
   * @param $first_name
   *    User First name
   * @param $last_name
   *    User last name
   * @param $phone
   *    User phone
   *
   * @return mixed
   */
  function createCustomer($uid, $email, $first_name = '', $last_name = '', $phone = '') {
    // TODO: Implement createCustomer() method.
    $gateway = \Drupal::service('sigma_payment.config_onsite')->setUp();
    $customerManager = new CustomerManager($gateway);
    $customer = new Customer();
    $customer->Email = $email;
    $customer->FirstName = $first_name;
    $customer->LastName = $last_name;
    $customer->Phone = $phone;
    $customer->UniqueIdentifier = $uid;
    $response = $customerManager->UpdateCustomer($customer);
    if ($response->ResponseDetails->IsSuccess === true) {
      return $response;
    }
    else {
      \Drupal::logger('SIGMA_PAYMENT')
        ->error('<pre>' . print_r('Error create customer: ' . $response, 1) . '</pre>');
      return FALSE;
    }
  }

  /**
   * Searches for a customer based on the provided filters.
   *
   * @param $uid
   *    User ID ( Drupal )
   * @return mixed
   */
  function searchCustomerByUid($uid) {
    // TODO: Implement searchCustomer() method.
    $gateway = \Drupal::service('sigma_payment.config_onsite')->setUp();
    $customerManager = new CustomerManager($gateway);
    $customerFilters = new CustomerSearch();
    $customerFilters->UniqueIdentifier = $uid;
    $customerSearchOptions = new CustomerSearchOption();
    $customerSearchOptions->IncludeCardInstruments = true;
    $customerSearchOptions->IncludeShippingAddress = true;
    $customerFilters->SearchOption = $customerSearchOptions;

    return array_shift($customerManager->SearchCustomer($customerFilters));
  }

  /**
   * Active or inactive customer.
   * @param $uid
   *    User ID ( Drupal )
   * @param $status
   *    New status: Active/Inactive
   *
   * @return mixed
   */
  function updateStatusCustomer($uid, $status) {
    // TODO: Implement updateStatusCustomer() method.
    if ($status === 'Active' || $status === 'Inactive') {
      $gateway = \Drupal::service('sigma_payment.config_onsite')->setUp();
      $customerManager = new CustomerManager($gateway);
      $customer = new Customer();
      $customer->UniqueIdentifier = $uid;
      $response = $customerManager->UpdateCustomer($customer);
      if ($response->ResponseDetails->IsSuccess === true) {
        return $response;
      }
      else {
        \Drupal::logger('SIGMA_PAYMENT')
          ->error('<pre>' . print_r('Error update customer: ' . $response, 1) . '</pre>');
        return FALSE;
      }
    }
    \Drupal::logger('SIGMA_PAYMENT')
      ->error('<pre>' . print_r('The $status only accept "Active" or "Inactive" value.', 1) . '</pre>');
    return NULL;
  }
}
