<?php

define('__ROOT__', dirname(__FILE__));
error_reporting(E_ALL);

// package
require_once(__ROOT__.'/class/CollectExData.php');

// Load AWS PHP SDK LIB
require '../vendor/autoload.php';
use Aws\Common\Aws;
use Aws\Kinesis\KinesisClient;


// Set Config Paramete to Array
$ini_array = parse_ini_file(__ROOT__."/conf/collector.ini",true);

// Create AWS SDK Instance
$aws = Aws::factory('../vendor/aws/aws-sdk-php/src/Aws/Common/Resources/aws-config.php');

// SET AWS Kinesis Region
$aws = Aws::factory(array('region' => $ini_array['AWS']['kinesis_region']));

// Create Kinesis Client
$kinesis_client = $aws->get('Kinesis');


//
// Request Currency Data
//

// Get target currency from INI
$currency_array = $ini_array['Currency']['type'];

// Loop to execute currency data
foreach ($currency_array as $currency) {
 
	// Initialize currency object
	$exData_obj = new CollectExData($currency);
	
	// Request to execute to collect Excahge data
	try {
		$exData_obj->requestData();
	} catch (Exception $e) {
		echo "Error occuerd at execut request Exchange Data from Web operation.\n";
		var_dump($e->getMessage());
	}

	// Create JSON Format Data
	$exData_hash = array (
 		"currency" => $exData_obj->getExCurrency(),
 		"pub_date" => $exData_obj->getPubDate(),
 		"pub_time" => $exData_obj->getPubTime(),
		"pub_timezone" => $exData_obj->getPubTimeZone(),
		"ex_rate" => $exData_obj->getExRate()
	);
	$exData_json = json_encode($exData_hash);
	print($exData_json);	
	
	//Put exData to Kinesis
	try {
		$kinesis_res = $kinesis_client->putRecord( array (
			'StreamName' => $ini_array['AWS']['kinesis_StreamName'],
			'Data' => $exData_json,
			'PartitionKey' => $exData_obj->getExCurrency()
		));
	} catch (Exception $e) {
		echo "Error occuerd at Kinesis Put operation. \n";
		var_dump($e->getMessage());
	}
	
	print($kinesis_res);
	

} 


?>
