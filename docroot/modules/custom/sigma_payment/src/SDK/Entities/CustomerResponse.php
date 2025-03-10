<?php
namespace Drupal\sigma_payment\SDK\Entities;

use Drupal\sigma_payment\SDK\Entities\ValidationError;
/**
 * CustomerResponse short summary.
 * Customer Response model
 *
 * CustomerResponse description.
 * Customer Response model definition
 *
 * @version 1.0
 * @author Raza
 */
class CustomerResponse
{
    public $ValidationErrors = null;
    public $IsSuccess = false;
    public $ResponseSummary = "";
    public $ResponseCode = "";
    public $CustomerId = "";
    public $CustomerToken = "";
    public $UniqueIdentification = "";
    function __construct() {
        $this->ValidationErrors  = new ValidationError();
    }
    function __destruct() {
        unset($this->ValidationErrors);           
    }
}
