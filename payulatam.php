<?php
/**
 * Payment Gateway Module: PayULatam
 *
 * Developer: Nayemur Rahman Sufi
 * Contact: nayemur@rahmansu.fi
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

/**
 * Define module related meta data
 *
 * @return array
 */
function payulatam_MetaData()
{
    return array(
        'DisplayName' => 'PayU Latam',
        'APIVersion' => '1.1', // Use API Version 1.1
        'DisableLocalCredtCardInput' => true,
        'TokenisedStorage' => false,
    );
}

/**
 * Define gateway configuration options
 *
 * @return array
 */
function payulatam_config()
{
    return array(
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'PayU Latam',
        ),
        'account_id' => array(
            'FriendlyName' => 'Account ID',
            'Type' => 'text',
            'Size' => '25',
            'Default' => '',
            'Description' => 'Enter your account ID here',
        ),
        'merchant_id' => array(
            'FriendlyName' => 'Merchant ID',
            'Type' => 'text',
            'Size' => '25',
            'Default' => '',
            'Description' => 'Enter your merchant ID here',
        ),
        'api_key' => array(
            'FriendlyName' => 'API Key',
            'Type' => 'text',
            'Size' => '25',
            'Default' => '',
            'Description' => 'Enter your API key here',
        ),
        'test' => array(
            'FriendlyName' => 'Test Mode',
            'Type' => 'yesno',
            'Description' => 'Tick to enable test mode',
        ),
    );
}

/**
 * Send Payment Data
 * @param type $params
 * @return string
 */
function payulatam_link($params) {
    //Invoice Parameters
    $invoiceId = $params['invoiceid'];
    $description = $params["description"];
    $amount = $params['amount'];
    $currencyCode = $params['currency'];

    //Client Parameters
    $firstname = $params['clientdetails']['firstname'];
    $lastname = $params['clientdetails']['lastname'];
    $email = $params['clientdetails']['email'];
    $address1 = $params['clientdetails']['address1'];
    $address2 = $params['clientdetails']['address2'];
    $city = $params['clientdetails']['city'];
    $postcode = $params['clientdetails']['postcode'];
    $country = $params['clientdetails']['country'];
    $phone = $params['clientdetails']['phonenumber'];

    //System Parameters
    $companyName = $params['companyname'];
    $systemUrl = $params['systemurl'];
    $returnUrl = $params['returnurl'];
    $langPayNow = $params['langpaynow'];
    $moduleDisplayName = $params['name'];
    $moduleName = $params['paymentmethod'];
    $whmcsVersion = $params['whmcsVersion'];

    //Gateway Configuration Parameters
    $gateway_test_mode = $params['test'];
    $tax = 0;
    $taxReturnBase = 0;

    //defining test/live gateway
    if ($gateway_test_mode == "on") {//TEST
        $gateway_url = 'https://sandbox.gateway.payulatam.com/ppp-web-gateway';
        $test = 1;
        $invoiceId = 'sufiXcVgh' . $params['invoiceid'];//making referenceCode/invoiceId unique for test mode
    } else {//LIVE
        $gateway_url = 'https://gateway.payulatam.com/ppp-web-gateway';
        $test = 0;
    }
    
    $account_id = $params['account_id'];
    $merchant_id = $params['merchant_id'];
    $api_key = $params['api_key'];

    //generating signature
    $signature = md5( $api_key . '~' . $merchant_id . '~' . $invoiceId . '~' . $amount . '~' . $currencyCode );

    //form fields
    $postfields = array();
    $postfields['merchantId'] = $merchant_id;
    $postfields['accountId'] = $account_id;
    $postfields['description'] = $description;
    $postfields['referenceCode'] = $invoiceId;
    $postfields['amount'] = $amount;
    $postfields['tax'] = $tax;
    $postfields['taxReturnBase'] = $taxReturnBase;
    $postfields['currency'] = $currencyCode;
    $postfields['signature'] = $signature;
    $postfields['test'] = $test;
    $postfields['buyerFullName'] = $firstname . ' ' . $lastname;
    $postfields['buyerEmail'] = $email;
    $postfields['telephone'] = $phone;
    $postfields['shippingAddress'] = $address1 . ' ' . $address2;
    $postfields['billingCity'] = $city;
    $postfields['zipCode'] = $postcode;
    $postfields['billingCountry'] = $country;

    $postfields['responseUrl'] = $returnUrl;
    $postfields['confirmationUrl'] = $systemUrl . 'modules/gateways/callback/' . $moduleName . '.php';

    $htmlOutput = '<form method="post" action="' . $gateway_url . '">';
    foreach ($postfields as $key => $value) {
        $htmlOutput .= '<input type="hidden" name="' . $key . '" value="' . $value . '" />';
    }
    $htmlOutput .= '<input type="submit" value="' . $langPayNow . '" />';
    $htmlOutput .= '</form>';

    return $htmlOutput;
}
