<?php

include_once("../Configuration/MetropagoGateway.php");
include_once("../Managers/TransactionManager.php");

include_once("../Entities/Customer.php");
include_once("../Entities/Transaction.php");
include_once("../Entities/TransactionOptions.php");
include_once("../Entities/TransactionSearchRequest.php");
include_once("../Entities/Address.php");
include_once("../Entities/CreditCard.php");
include_once("../Entities/CustomerEntity.php");
include_once("../Entities/ParameterFilter.php");

$Gateway = new  MetropagoGateway("SANDBOX","100177","100177001","","");
$TrxManager  = new TransactionManager($Gateway);

#Processing Sale Request
$transRequest = new Transaction();
$transRequest->CustomerData = new Customer();
$transRequest->CustomerData->CustomerId = "4946";
$transRequest->CustomerData->CreditCards =array();
$card = new CreditCard();
$card->CardholderName="John Snow";
$card->Status="Active";
$card->ExpirationMonth="01";
$card->ExpirationYear="18";
$card->ExpirationDate = "0118";
$card->Number="4532280253448309";
$card->CustomerId="4946";
$card->Address=array();
$Address =new  Address();
$Address->AddressId = "0";
$Address->AddressLine1 = "Collin Road";
$Address->AddressLine2 = "Suite XYZ";
$Address->City = "PA";
$Address->CountryName = "PA";
$Address->SubDivision = "XYZ";
$Address->State = "PA";
$Address->ZipCode = "123456";
$card->Address =$Address;
$card->Token="3051ab49-c007-4f36-9538-725f8e6b6fe6";
$transRequest->CustomerData->CreditCards[] =$card;
$transRequest->Amount = "1";
$transRequest->OrderTrackingNumber=rand(); //random number
$transRequest->TerminalId="100177001";
$transRequest->TransactOptions =new TransactionOptions();
$transRequest->TransactOptions->Operation = "Sale";
echo "\r<br/><br/>* Sale *<br/>";
$sale_response = $TrxManager->Sale($transRequest);
echo "Response: " . $sale_response->ResponseDetails->ResponseSummary;
echo "<br/>Authorization #: " . $sale_response->ResponseDetails->AuthorizationNumber;

#Processing Reversal Request
$transRequest = new Transaction();
$transRequest->CustomerData = new Customer();
$transRequest->CustomerData->CustomerId = "4946";
$transRequest->CustomerData->CreditCards =array();
$card = new CreditCard();
$card->CardholderName="John Snow";
$card->Status="Active";
$card->ExpirationMonth="01";
$card->ExpirationYear="18";
$card->ExpirationDate = "0118";
$card->Number="4532280253448309";
$card->CustomerId="4946";
$card->Address=array();
$Address =new  Address();
$Address->AddressId = "0";
$Address->AddressLine1 = "Collin Road";
$Address->AddressLine2 = "Suite XYZ";
$Address->City = "PA";
$Address->CountryName = "PA";
$Address->SubDivision = "XYZ";
$Address->State = "PA";
$Address->ZipCode = "123456";
$card->Address =$Address;
$card->Token="3051ab49-c007-4f36-9538-725f8e6b6fe6";
$transRequest->CustomerData->CreditCards[] =$card;
$transRequest->Amount = "1";
$transRequest->TransactionId=$sale_response->ResponseDetails->TransactionId;
$transRequest->TerminalId="100177001";
$transRequest->TransactOptions =new TransactionOptions();
$transRequest->TransactOptions->Operation = "Refund";
echo "\r<br/><br/>* Refund *<br/>";
$refund_response = $TrxManager->Refund($transRequest);
echo "Response: " . $refund_response->ResponseDetails->ResponseSummary;
echo "<br/>Authorization #: " . $refund_response->ResponseDetails->AuthorizationNumber;


#Processing PreAuthorization Request
$transRequest = new Transaction();
$transRequest->CustomerData = new Customer();
$transRequest->CustomerData->CustomerId = "4946";
$transRequest->CustomerData->CreditCards =array();
$card = new CreditCard();
$card->CardholderName="John Snow";
$card->Status="Active";
$card->ExpirationMonth="01";
$card->ExpirationYear="18";
$card->ExpirationDate = "0118";
$card->Number="4532280253448309";
$card->CustomerId="4946";
$card->Address=array();
$Address =new  Address();
$Address->AddressId = "0";
$Address->AddressLine1 = "Collin Road";
$Address->AddressLine2 = "Suite XYZ";
$Address->City = "PA";
$Address->CountryName = "PA";
$Address->SubDivision = "XYZ";
$Address->State = "PA";
$Address->ZipCode = "123456";
$card->Address =$Address;
$card->Token="3051ab49-c007-4f36-9538-725f8e6b6fe6";
$transRequest->CustomerData->CreditCards[] =$card;
$transRequest->Amount = "1";
$transRequest->TerminalId="100177001";
$transRequest->TransactOptions =new TransactionOptions();
$transRequest->TransactOptions->Operation = "PreAuthorization";
echo "\r<br/><br/>* Pre Authorization *<br/>";
$preAuthorization_response = $TrxManager->PreAuthorization($transRequest);
echo "Response: " . $preAuthorization_response->ResponseDetails->ResponseSummary;
echo "<br/>Authorization #: " . $preAuthorization_response->ResponseDetails->AuthorizationNumber;

#Processing Adjustment Request
$transRequest = new Transaction();
$transRequest->CustomerData = new Customer();
$transRequest->CustomerData->CustomerId = "4946";
$transRequest->CustomerData->CreditCards =array();
$card = new CreditCard();
$card->CardholderName="John Snow";
$card->Status="Active";
$card->ExpirationMonth="01";
$card->ExpirationYear="18";
$card->ExpirationDate = "0118";
$card->Number="4532280253448309";
$card->CustomerId="4946";
$card->Address=array();
$Address =new  Address();
$Address->AddressId = "0";
$Address->AddressLine1 = "Collin Road";
$Address->AddressLine2 = "Suite XYZ";
$Address->City = "PA";
$Address->CountryName = "PA";
$Address->SubDivision = "XYZ";
$Address->State = "PA";
$Address->ZipCode = "123456";
$card->Address =$Address;
$card->Token="3051ab49-c007-4f36-9538-725f8e6b6fe6";
$transRequest->CustomerData->CreditCards[] =$card;
$transRequest->Amount = "1";
$transRequest->TransactionId=$preAuthorization_response->ResponseDetails->TransactionId;
$transRequest->TerminalId="100177001";
$transRequest->TransactOptions =new TransactionOptions();
$transRequest->TransactOptions->Operation = "Adjustment";
echo "\r<br/><br/>* Adjustment *<br/>";
$adjustment_response = $TrxManager->Adjustment($transRequest);
echo "Response: " . $adjustment_response->ResponseDetails->ResponseSummary;
echo "<br/>Authorization #: " . $adjustment_response->ResponseDetails->AuthorizationNumber;

#Searching for Transaction Record by Filters
$searchFilter = new TransactionSearchRequest();
$searchFilter->TransactionId = $preAuthorization_response->ResponseDetails->TransactionId;
$searchFilter->Amount =new AmountRangeFilter();
$searchFilter->Amount->Between(1, 200000);
$searchFilter->DateCreated = new DateRangeFilter();
$searchFilter->DateCreated->BETWEEN("2016-01-16","2016-04-19");
$transactionlst = $TrxManager->SearchTransaction($searchFilter);

echo "\r<br/><br/>* Transaction Search *<br/>";
echo "<br/>Total "+ count($transactionlst) . " transaction found matching search filters";

?>