<?php

require('../AvaTax4PHP/AvaTax.php');     // location of the AvaTax.PHP Classes - Required

$client = new TaxServiceRest(
	"", // TODO: Enter service URL
	"", //TODO: Enter Username or Account Number
	""); //TODO: Enter Password or License Key
	
$request = new GetTaxRequest();

//Document Level Setup  
//     R: indicates Required Element
//     O: Indicates Optional Element
//
    $dateTime = new DateTime();                                  // R: Sets dateTime format 
    $request->setCompanyCode("SDKsss");                    // R: Company Code from the accounts Admin Console
    $request->setDocType(DocumentType::$SalesOrder);                           // R: Typically SalesOrder,SalesInvoice, ReturnInvoice
    $request->setDocCode("INV123123");                          // R: Invoice or document tracking number - Must be unique
    $request->setDocDate(date_format($dateTime, "Y-m-d"));  // R: Date the document is processed and Taxed - See TaxDate
    $request->setCustomerCode("CUST123123");             // R: String - Customer Tracking number or Exemption Customer Code
    $request->setCustomerUsageType("L");      // O: String   Entity Usage
    $request->setDiscount(0);                   // O: Decimal - amount of total document discount
    $request->setPurchaseOrderNo("234234234");    // O: String 
    $request->setExemptionNo("EXEMPT");           // O: String   if not using ECMS which keys on customer code
    $request->setDetailLevel(DetailLevel::$Tax);     // R: Chose $Summary, $Document, $Line or $Tax - varying levels of results detail 
    $request->setCommit(FALSE);                    // O: Default is FALSE - Set to TRUE to commit the Document

	$taxOverride = new TaxOverride();
	$taxOverride->setTaxOverrideType('TaxDate');
	$taxOverride->setReason('override testing');
	$taxOverride->setTaxDate("2013-07-01");
	
	$request->setTaxOverride($taxOverride);

	$addresses = array();
//Add Origin Address
    $origin = new Address();                      // R: New instance of an origin address
    $origin->setLine1("PO Box 123");              // O: It is not required to pass a valid street address however the 
    $origin->setCity("Seattle");                  // R: City
    $origin->setRegion("WA");              // R: State or Province
    $origin->setPostalCode("98101");      // R: String (Expects to be NNNNN or NNNNN-NNNN or LLN-LLN)
    $origin->setAddressCode("01");            // O: String Country, Country Code, etc.
	$addresses[] = $origin;

// Add Destination Address
    $destination = new Address();                 // R: New instance of an destination address
    $destination->setLine1("General Delivery");         // O: It is not required to pass a valid street address however the 
    $destination->setRegion("CA");         // R: State or Province
    $destination->setPostalCode("90210"); // R: String (Expects to be NNNNN or NNNNN-NNNN or LLN-LLN)
    $destination->setAddressCode("02");       // O: String Country, Country Code, etc.
	$addresses[] = $destination;
	
	$request->setAddresses($addresses);
//
// Line level processing
    
    $lines = array();                                     // array of lines for the invoice
    //$i = 0;                                            // sets counter to 0 (multiple lines)
    $line1 = new Line();                                // New instance of a line  
    $line1->setLineNo("01");                            // R: string - line Number of invoice - must be unique.
    $line1->setItemCode("SKU123");                   // R: string - SKU or short name of Item
    $line1->setDescription("Blue widget");               // O: string - Longer description of Item - R: for SST
    $line1->setTaxCode("PC040100");               // O: string - Tax Code associated with Item
    $line1->setQty(3);                          // R: decimal - The number of items 
    $line1->setAmount(500);                   // R: decimal - the "NET" amount of items 
    $line1->setDiscounted(false);                //O: boolean - Set to true if line item is to discounted - see Discount
	$line1->setOriginCode("01");
	$line1->setDestinationCode("02");

    $request->setLines(array($line1));             // sets line items to $lineX array    



// GetTaxRequest and Results
    
    try {
        $getTaxResult = $client->getTax($request);
        echo 'GetTax is: ' . $getTaxResult->getResultCode() . "\n";

// Error Trapping

        if ($getTaxResult->getResultCode() == SeverityLevel::$Success) {

// Success - Display GetTaxResults to console
            
            //Document Level Results

            echo "DocCode: " . $request->getDocCode() . "\n";
            echo "TotalAmount: " . $getTaxResult->getTotalAmount() . "\n";
            echo "TotalTax: " . $getTaxResult->getTotalTax() . "\n";
            
            //Line Level Results (from a TaxLines array class)
            //Displayed in a readable format
            
            foreach ($getTaxResult->getTaxLines() as $ctl) {
               echo "     Line: " . $ctl->getLineNo() . " Tax: " . $ctl->getTax() . " TaxCode: " . $ctl->getTaxCode() . "\n";
 
            //Line Level Results (from a TaxDetails array class)
            //Displayed in a readable format
                foreach ($ctl->getTaxDetails() as $ctd) {
                    echo "          Juris Type: " . $ctd->getJurisType() . "; Juris Name: " . $ctd->getJurisName() . "; Rate: " . $ctd->getRate() . "; Amt: " . $ctd->getTax() . "\n";
                }
                echo"\n";
            }
            
// If NOT success - display error messages to console
// it is important to itterate through the entire message class        
                      
            } else {
            foreach ($getTaxResult->getMessages() as $msg) {
                echo $msg->getSummary() . "\n";
            }
        }
    } catch (SoapFault $exception) {
        $msg = "Exception: ";
        if ($exception)
            $msg .= $exception->faultstring;

// If you desire to retrieve SOAP IN / OUT XML
//  - Follow directions below
//  - if not, leave as is
       
        echo $msg . "\n";
//    }   //UN-comment this line to return SOAP XML
    echo $client->__getLastRequest() . "\n";
    echo $client->__getLastResponse() . "\n";
//}   //Comment this line to return SOAP XML
}
?>
