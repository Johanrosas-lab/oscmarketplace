<?php


namespace Drupal\sigma_payment\Services;


interface SigmaCustomerManagerInterface {

  /**
   * Registers customer in SIGMA GATEWAY platform.
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
  function createCustomer($uid, $email, $first_name = '', $last_name = '', $phone = '');

  /**
   * Searches for a customer based on the provided filters.
   *
   * @param $uid
   *    User ID ( Drupal )
   * @return mixed
   */
  function searchCustomerByUid($uid);

  /**
   * Active or inactive customer.
   * @param $uid
   *    User ID ( Drupal )
   * @param $status
   *    New status: Active/Inactive
   *
   * @return mixed
   */
  function updateStatusCustomer($uid, $status);
}
