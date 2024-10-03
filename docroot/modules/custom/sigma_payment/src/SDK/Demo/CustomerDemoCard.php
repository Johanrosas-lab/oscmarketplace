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

#Adding Card to Customer Model
$customer = new Customer();
$customer->CustomerId='11555';
$customer->CreditCards =array();
$card = new CreditCard();
$card->CardholderName="Isauro Pitti";
$card->Status="Active";
$card->ExpirationMonth="01";
$card->ExpirationYear="18";
$card->ExpirationDate = "0118";
$card->Number="5539893023578732";
$card->CustomerId='11555';
$card->Address=array();
$Address =new Address();
$Address->AddressId = "0";
$Address->AddressLine1 = "Villas del Golf";
$Address->AddressLine2 = "Casa 22";
$Address->City = "PA";
$Address->CountryName = "PA";
$Address->SubDivision = "XYZ";
$Address->State = "PA";
$Address->ZipCode = "10039";
$card->Address =$Address;
$customer->CreditCards[]=$card;

echo "\r<br/>* ADD/UPDATE Credit Card *<br/>";
$customerSavedWithCardResult = $CustManager->UpdateCustomer($customer);

if($customerSavedWithCardResult->ResponseDetails->IsSuccess === true)
{
    echo "Card Added/Updated successfully<br/>";
    echo "Card: " . $card->ResponseDetails->CardToken . "<br/>";
}
else
{
    echo $customerSavedWithCardResult->ResponseDetails->ResponseSummary. '<br/>';
}

#Searching for Customer
$customerFilters =new CustomerSearch();
$customerFilters->FirstName =new TextFilter();
$customerFilters->FirstName->StartsWith("Isa");
$customerSearchOptions = new CustomerSearchOption();
$customerSearchOptions->IncludeCardInstruments=true;
$customerSearchOptions->IncludeShippingAddress=true;
$customerFilters->SearchOption=$customerSearchOptions;
$response_customers = $CustManager->SearchCustomer($customerFilters);
print("\r<br/>* Searching Customer *<br/>");
print("Total "+ count($response_customers) . " customer found matching search filters<br/>");
?>