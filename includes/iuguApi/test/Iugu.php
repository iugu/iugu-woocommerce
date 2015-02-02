<?php

//$_SERVER["IUGU_API_KEY"] = "3b863c67d62d7232c7407046bc5e7fdc";

/*echo "<pre>";
print_r($_SERVER);
echo "</pre>";*/

putenv("IUGU_API_KEY=3b863c67d62d7232c7407046bc5e7fdc");

include_once(dirname(__FILE__)."/../vendor/simpletest/simpletest/autorun.php");

error_reporting( E_ALL | E_STRICT );

echo "Running iugu PHP Test Suite\r\n";

$apiKey = getenv('IUGU_API_KEY');
if (!$apiKey) {
  echo "MISSING IUGU_API_KEY in Environment. $ export IUGU_API_KEY=<your_api_key>.";
  exit(1);
}

include_once(dirname(__FILE__)."/../lib/Iugu.php");

include_once(dirname(__FILE__)."/Iugu/TestCase.php");
include_once(dirname(__FILE__)."/Iugu/CustomerTest.php");
include_once(dirname(__FILE__)."/Iugu/PaymentMethodTest.php");
include_once(dirname(__FILE__)."/Iugu/PaymentTokenTest.php");
include_once(dirname(__FILE__)."/Iugu/ChargeTest.php");
include_once(dirname(__FILE__)."/Iugu/SubscriptionTest.php");
include_once(dirname(__FILE__)."/Iugu/PlanTest.php");

?>
