<?php
include_once("../Configuration/MetropagoGateway.php");
include_once("../Managers/CustomerManager.php");
include_once("../Managers/TransactionManager.php");

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

$sdk = new  MetropagoGateway("SANDBOX","100177","100177001","","");
$CustManager  = new CustomerManager($sdk);
$TrxManager  = new TransactionManager($sdk);

echo "Add Customer<br/>";
$customer = new Customer();
$customer->UniqueIdentifier =mt_rand();
$customer->FirstName = "John";
$customerResult = $CustManager->AddCustomer($customer);
if($customerResult->ResponseDetails->IsSuccess === true)
{
    $customer->CustomerId = $customerResult->ResponseDetails->CustomerId;
    echo "Customer Added successfully<br/>";
}
else
{
    echo $customerResult->ResponseDetails->ResponseSummary. '<br/>';
}

echo "<br/>Search Customer<br/>";
$customerFilters =new CustomerSearch();
$customerFilters->CustomerId =$customerResult->CustomerId; //Customer Token
$customerFilters->UniqueIdentifier=$customer->UniqueIdentifier;
$response_customers = $CustManager->SearchCustomer($customerFilters);
echo print_r($response_customers, true);

echo "<br/><br/>Update Customer";
$ucustomer = new Customer();
$ucustomer->UniqueIdentifier =$customer->UniqueIdentifier;
$ucustomer->Status="INACTIVE";
$customerUpdatedResult  = $CustManager->UpdateCustomer($customer);
if($customerUpdatedResult->ResponseDetails->IsSuccess === true)
{
    echo "<br/>Customer Updated successfully<br/>";
}
else
{
    echo $customerUpdatedResult->ResponseDetails->ResponseSummary. '<br/>';
}

echo "<br/>Adding Customer Additional Data";
$ucustomer = new Customer();
$ucustomer->UniqueIdentifier =$customer->UniqueIdentifier;
$ucustomer->CustomerId=$customer->CustomerId;//Customer Token
$ucustomer->CustomFields = array();
$ucustomer->CustomFields["PhoneOffice"] = "95401011100";
$customerUpdatedResult = $CustManager->UpdateCustomer($ucustomer);
if($customerUpdatedResult->ResponseDetails->IsSuccess === true)
{
    echo "<br/>Customer Additional Field added successfully<br/>";
}
else
{
    echo $customerUpdatedResult->ResponseDetails->ResponseSummary. '<br/>';
}

echo "<br/>Adding Customer Credit Card";
$customer->CreditCards =array();
$card = new CreditCard();
$card->CardholderName="John Snow";
$card->Status="ACTIVE";
$card->ExpirationDate = "0118";
$card->Number="4111111111111111";
$customer->CreditCards[]=$card;
$cardResponse = $CustManager->UpdateCustomer($customer);
if($cardResponse->CreditCards[0]->ResponseDetails->IsSuccess === true)
{
    $card->Token = $cardResponse->CreditCards[0]->ResponseDetails->Id;    
    echo "<br/>Customer Credit Card added successfully. Card Token = ".$card->Token."<br/>";
}
else
{
    echo '<br/>'.$cardResponse->CreditCards[0]->ResponseDetails->ResponseSummary. '<br/>';
}

echo "<br/>Updating Customer Credit Card";
$ucustomer = new Customer();
$ucustomer->UniqueIdentifier =$customer->UniqueIdentifier;
$ucustomer->CustomerId=$customer->CustomerId;//Customer Token
$ucard = new CreditCard();
$ucard->Token=$card->Token;
$ucard->ExpirationDate = "0118";
$ucustomer->CreditCards =array();
$ucustomer ->CreditCards[]=$ucard; 
$cardResponse = $CustManager->UpdateCustomer($ucustomer);
if($cardResponse->CreditCards[0]->ResponseDetails->IsSuccess === true)
{
    echo "<br/>Customer Credit Card updated successfully<br/>";
}
else
{
    echo '<br/>'.$cardResponse->CreditCards[0]->ResponseDetails->ResponseSummary. '<br/>';
}

echo "<br/>Add Account";
$customer->CustomerEntities =array();
$account = new CustomerEntity();
$account->Id = "0";
$account->AccountNumber="65656565";
$account->CustomerId=$customer->CustomerId;
$account->FriendlyName="Primary Acc";
$account->ServiceTypeName="Servicio Prueba A";
$account->Status="ACTIVE";
$customer->CustomerEntities[]=$account;
$accountResponse = $CustManager->UpdateCustomer($customer);
if($accountResponse->CustomerEntities[0]->ResponseDetails->IsSuccess === true)
{
    $account->Id = $accountResponse->CustomerEntities[0]->ResponseDetails->Id;
    echo "<br/>Account added successfully. Account Token = ". $account->Id ."<br/>";
}
else
{
    echo '<br/>'.$accountResponse->CustomerEntities[0]->ResponseDetails->ResponseSummary. '<br/>';
}

echo "<br/>Update Account";
$ucustomer = new Customer();
$ucustomer->UniqueIdentifier = $customer->UniqueIdentifier;
$ucustomer->CustomerId=$customer->CustomerId;//Customer Token
$uaccount = new CustomerEntity();
$uaccount->Id = $account->Id;//Account Token
$uaccount->Status = "INACTIVE";
$ucustomer->CustomerEntities[]=$uaccount;
$accountResponse = $CustManager->UpdateCustomer($ucustomer);
if($accountResponse->CustomerEntities[0]->ResponseDetails->IsSuccess === true)
{    
    echo "<br/>Account Updated successfully<br/>";
}
else
{
    echo '<br/>'.$accountResponse->CustomerEntities[0]->ResponseDetails->ResponseSummary. '<br/>';
}

echo "<br/>Add Payment Instruction";
$customer->PaymentInstructions =array();
$instruction=new Instruction();
$instruction->InstrumentToken=$card->Token;
$instruction->CustomerEntityValue=$account->AccountNumber;
$instruction->ScheduleDay="25";
$instruction->CustomerId=$customer->CustomerId;
$instruction->Status="Active";
$instruction->ExpirationDate="12/02/2018";   //date format: MM/dd/yyyy
$customer->PaymentInstructions[]=$instruction;
$customerPaymentInstructionResult = $CustManager->UpdateCustomer($customer);
if($customerPaymentInstructionResult->PaymentInstructions[0]->Response->IsSuccess === true)
{    
    $instruction->Id = $customerPaymentInstructionResult->PaymentInstructions[0]->Response->Id;
    echo "<br/>Payment Instruction Added successfully. Instruction Token = ".$instruction->Id."<br/>";
}
else
{
    echo '<br/>'.$customerPaymentInstructionResult->PaymentInstructions[0]->Response->ResponseSummary. '<br/>';
}

echo "<br/>Update Payment Instruction";
$ucustomer = new Customer();
$ucustomer->UniqueIdentifier = $customer->UniqueIdentifier;
$ucustomer->CustomerId=$customer->CustomerId;//Customer Token
$uinstruction=new Instruction();
$uinstruction->Id = $instruction->Id; //Instruction Token
$uinstruction->Status = "INACTIVE";
$ucustomer->PaymentInstructions[]=$uinstruction;
$customerPaymentInstructionResult = $CustManager->UpdateCustomer($ucustomer);
if($customerPaymentInstructionResult->PaymentInstructions[0]->Response->IsSuccess === true)
{        
    echo "<br/>Payment Instruction Updated successfully<br/>";
}
else
{
    echo '<br/>'.$customerPaymentInstructionResult->PaymentInstructions[0]->Response->ResponseSummary. '<br/>';
}

echo "<br/>Perform Sale";
$transRequest = new Transaction();
$transRequest->CustomerData = new Customer();
$transRequest->CustomerData->CreditCards =array();
$transRequest->CustomerData->CustomerEntities=array();
$tcard = new CreditCard();
$taccount = new CustomerEntity();

$tcard->Token = $card->Token;//Card Token
$taccount->Id = $account->Id; //Account Token
$transRequest->CustomerData->CustomerId = $customer->CustomerId;//Customer Token
$transRequest->CustomerData->CreditCards[] = $tcard;
$transRequest->CustomerData->CustomerEntities[]=$taccount;
$transRequest->Amount = "1.00";
$transRequest->OrderTrackingNumber="9AA555471";
$sale_response = $TrxManager->Sale($transRequest);

if($sale_response->ResponseDetails->IsSuccess === true)
{      
    $transRequest->TransactionId =  $sale_response->ResponseDetails->TransactionId; 
    echo "<br/>Transaction Processed Successfully: Transaction Id = " .$transRequest->TransactionId. "<br/>";
}
else
{
    echo '<br/>'.$sale_response->ResponseDetails->ResponseSummary. '<br/>';
}

echo "<br/>Perform Refund";
$rtransRequest = new Transaction();
$rtransRequest->TransactionId = $transRequest->TransactionId; //Reference Transaction Id
$rtransRequest->Amount = "1.00";
$refund_response = $TrxManager->Refund($rtransRequest);
if($refund_response->ResponseDetails->IsSuccess === true)
{           
    echo "<br/>Transaction Refunded Successfully<br/>";
}
else
{
    echo '<br/>'.$refund_response->ResponseDetails->ResponseSummary. '<br/>';
}

echo "<br/>Get All Cards of Customer<br/>";
$customerFilters =new CustomerSearch();
$customerFilters->CustomerId =$customerResult->CustomerId; //Customer Token
$customerFilters->UniqueIdentifier=$customer->UniqueIdentifier;

$customerSearchOptions = new CustomerSearchOption();
$customerSearchOptions->IncludeCardInstruments=true;
$customerFilters->SearchOption=$customerSearchOptions;
$response_customers = $CustManager->SearchCustomer($customerFilters);
echo print_r($response_customers[0]->CreditCards, true);

echo "<br/><br/>Get All Accounts (Services) of Customer<br/>";
$customerFilters =new CustomerSearch();
$customerFilters->CustomerId =$customerResult->CustomerId; //Customer Token
$customerFilters->UniqueIdentifier=$customer->UniqueIdentifier;

$customerSearchOptions = new CustomerSearchOption();
$customerSearchOptions->IncludeAssociatedEntities=true;
$customerFilters->SearchOption=$customerSearchOptions;
$response_customers = $CustManager->SearchCustomer($customerFilters);
echo print_r($response_customers[0]->CustomerEntities, true);
?>