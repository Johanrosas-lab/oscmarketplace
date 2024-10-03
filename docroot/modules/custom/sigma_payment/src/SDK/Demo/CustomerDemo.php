<?php
include_once("../Configuration/MetropagoGateway.php");
include_once("../Managers/CustomerManager.php");

include_once("../Entities/Customer.php");
include_once("../Entities/Address.php");
include_once("../Entities/CreditCard.php");
include_once("../Entities/CustomerSearch.php");
include_once("../Entities/ParameterFilter.php");
include_once("../Entities/CustomerEntity.php");
include_once("../Entities/Instruction.php");
include_once("../Entities/CustomerSearch.php");
include_once("../Entities/CustomerSearchOption.php");
include_once("../Entities/Instruction.php");

$Gateway = new  MetropagoGateway("SANDBOX","100177","100177001","","");
$CustManager  = new CustomerManager($Gateway);

#Creating Customer With Unique Identification
$customer = new Customer();
$customer->CustomerId = "0";
$customer->Company = "Sigma123";
$customer->Email = "customer@sigmaprocessing.net";
$customer->Fax = "50712345678";
$customer->FirstName = "John";
$customer->LastName = "Smith";
$customer->Phone = "50712345679";
$customer->UniqueIdentifier = mt_rand() . "121";
$customer->Website = "http://www.sigmaprocessing.net";
#Adding Extra detail to Customer Model
$customer->CustomFields = array();
$customer->CustomFields["Profession"] = "Software Developer";
#Adding Shipping Address to Customer Model;
$customer->ShippingAddress = array();
$ShippingAddress = new Address();
$ShippingAddress->AddressId = "0";
$ShippingAddress->AddressLine1 = "Collin Road";
$ShippingAddress->AddressLine2 = "Suite XYZ";
$ShippingAddress->City = "PA";
$ShippingAddress->CountryName = "PA";
$ShippingAddress->SubDivision = "XYZ";
$ShippingAddress->State = "PA";
$ShippingAddress->ZipCode = "123456";
$customer->ShippingAddress[] =$ShippingAddress;
#Adding Billing Address to Customer Model;
$customer->BillingAddress = array();
$BillingAddress =new  Address();
$BillingAddress->AddressId = "0";
$BillingAddress->AddressLine1 = "Collin Road";
$BillingAddress->AddressLine2 = "Suite XYZ";
$BillingAddress->City = "PA";
$BillingAddress->CountryName = "PA";
$BillingAddress->SubDivision = "XYZ";
$BillingAddress->State = "PA";
$BillingAddress->ZipCode = "123456";
$customer->BillingAddress[] =$BillingAddress;

echo "\r<br/>* Create Customer *<br/>";
$customerRe = $CustManager->UpdateCustomer($customer);

if($customerRe->ResponseDetails->IsSuccess === true)
{
    echo "New Customer added successfully<br/>";
}
else
{
    echo $customerRe->ResponseDetails->ResponseSummary. '<br/>';
}


#UPDATE Customer
$customer = new Customer();
$customer->Website="http://www.croem.net";
$customer->CustomerId = $customerRe->CustomerId;
$customerUpdateRe = $CustManager->UpdateCustomer($customer);
echo "\r<br/>* UPDATE Customer *<br/>";
if($customerRe->ResponseDetails->IsSuccess === true)
{
    echo "Customer Updated Successfully<br/>";
}
else
{
    echo $customerRe->ResponseDetails->ResponseSummary. '<br/>';
}

#Adding Card to Customer Model
$customer->CustomerId=$customerRe->CustomerId;
$customer->CreditCards =array();
$card = new CreditCard();
$card->CardholderName="John Snow";
$card->Status="Active";
$card->ExpirationMonth="01";
$card->ExpirationYear="18";
$card->ExpirationDate = "0118";
$card->Number="4532280253448309";
$card->CustomerId=$customerRe->CustomerId;
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
$customer->CreditCards[]=$card;

echo "\r<br/>* ADD/UPDATE Credir Card *<br/>";
$customerSavedWithCardResult = $CustManager->UpdateCustomer($customer);

if($customerSavedWithCardResult->ResponseDetails->IsSuccess === true)
{
    echo "Card Added/Updated successfully<br/>";
}
else
{
    echo $customerSavedWithCardResult->ResponseDetails->ResponseSummary. '<br/>';
}


#Adding Account to Customer Model
$customer->CustomerId=$customerRe->CustomerId;
$customer->CustomerEntities =array();
$accounts = new CustomerEntity();
$accounts->AccountNumber="65656565";
$accounts->Id=$customerRe->CustomerId;
$accounts->CustomerId=$customerRe->CustomerId;
$accounts->FriendlyName="Primary Acc";
$accounts->ServiceTypeName="Servicio Prueba B";
$accounts->Status="ACTIVE";
$customer->CustomerEntities[]=$accounts;
echo "\r<br/>* ADD/UPDATE Account *<br/>";
$customerSavedWithAccountResult = $CustManager->UpdateCustomer($customer);

if($customerSavedWithAccountResult->ResponseDetails->IsSuccess === true)
{
    echo "Account Added/Updated successfully<br/>";
}
else
{
    echo $customerSavedWithAccountResult->ResponseDetails->ResponseSummary. '<br/>';
}

#Searching for Customer
$customerFilters =new CustomerSearch();
$customerFilters->FirstName =new TextFilter();
$customerFilters->FirstName->StartsWith("Bill");
$customerSearchOptions = new CustomerSearchOption();
$customerSearchOptions->IncludeCardInstruments=true;
$customerSearchOptions->IncludeShippingAddress=true;
$customerFilters->SearchOption=$customerSearchOptions;
$response_customers = $CustManager->SearchCustomer($customerFilters);
print("\r<br/>* Searching Customer *<br/>");
print("Total "+ count($response_customers) . " customer found matching search filters<br/>");

#Adding Payment Instruction to Customer Model
$customer->PaymentInstructions =array();
$instruction=new Instruction();
$instruction->InstrumentToken=$customerSavedWithCardResult->Token;
$instruction->CustomerEntityValue=$accounts->AccountNumber;
$instruction->ScheduleDay="25";
$instruction->CustomerId=$customerRe->CustomerId;
$instruction->Status="Active";
$instruction->ExpirationDate="12/02/2018";   //date format: MM/dd/yyyy
$customer->PaymentInstructions[]=$instruction;
echo "\r<br/>* Adding Payment Instruction to Customer using Account Number *";
$customerPaymentInstructionResult = $CustManager->UpdateCustomer($customer);

if($customerPaymentInstructionResult->ResponseDetails->IsSuccess === true)
{
    echo "<br/>Payment Instruction Added/Updated successfully<br/>";
}
else
{
    echo $customerPaymentInstructionResult->ResponseDetails->ResponseSummary. '<br/>';
}

?>