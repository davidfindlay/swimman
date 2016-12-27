<?php
require_once("includes/classes/MeetProgramMobile.php");
require('includes/aws/aws-autoloader.php');

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

uploadToAws();

function uploadToAws() {
	
	$bucket = 'msq-eprogram';
	$keyname = 'test.txt';
	
	// Instantiate the client.
	$s3 = S3Client::factory(array(
    	'credentials' => array(
        	'key'    => '',
        	'secret' => '',
    	)
	));
	
	try {
		// Upload data.
		$result = $s3->putObject(array(
				'Bucket' => $bucket,
				'Key'    => $keyname,
				'Body'   => 'Hello, world!',
				'ACL'    => 'public-read'
		));
	
		// Print the URL to the object.
		echo $result['ObjectURL'] . "\n";
	} catch (S3Exception $e) {
		echo $e->getMessage() . "\n";
	}
	
}