<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include_once("../Configuration/MetropagoGateway.php");
include_once("../Managers/TransactionManager.php");
include_once("../Managers/CustomerManager.php");

include_once("../Entities/Customer.php");
include_once("../Entities/Transaction.php");
include_once("../Entities/TransactionOptions.php");
include_once("../Entities/CreditCard.php");
include_once("../Entities/CustomerSearch.php");
include_once("../Entities/CustomerSearchOption.php");

$Gateway = new  MetropagoGateway("SANDBOX","100177","100177001","","");
$CustManager  = new CustomerManager($Gateway);
$TrxManager  = new TransactionManager($Gateway);

#Processing Sale Request
$transRequest = new Transaction();
$transRequest->CustomerData = new Customer();
$transRequest->CustomerData->CustomerId = "11555";
$transRequest->CustomerData->CreditCards =array();
$card = new CreditCard();
$card->ExpirationDate = "0118";
$card->Token="ae52e744-4ea6-4ce8-a067-e2ff293eec90";
$transRequest->CustomerData->CreditCards[] = $card;
$transRequest->Amount = "1";
$transRequest->OrderTrackingNumber=rand(); //random number
echo "\r<br/><br/>* Sale *<br/>";
$sale_response = $TrxManager->Sale($transRequest);
if($sale_response->ResponseDetails->IsSuccess === true) {
    echo "Response: " . $sale_response->ResponseDetails->ResponseSummary;
    echo "<br/>Transaction ID#: " . $sale_response->ResponseDetails->TransactionId;
} else {
    echo "Response: " . $sale_response->ResponseDetails->ResponseSummary;
}

# Get Cards for a Customer
echo "<br/>Get All Cards of Customer<br/>";
$customerFilters =new CustomerSearch();
$customerFilters->CustomerId = "11555"; //Customer ID

$customerSearchOptions = new CustomerSearchOption();
$customerSearchOptions->IncludeCardInstruments=true;
$customerFilters->SearchOption=$customerSearchOptions;
$response_customers = $CustManager->SearchCustomer($customerFilters);
echo print_r($response_customers[0]->CreditCards, true);
?>

