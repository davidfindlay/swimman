<?php
// Retreives full results for a meet from the Results Portal and returns it as XML

// See example here:
// http://stackoverflow.com/questions/10900436/scrape-html-table-data-and-create-xml-or-json-doc
$dom = new DOMDocument();
@$dom->loadHTMLFile(url);
$xpath = new DOMXPath($dom);

$xml = new DOMDocument();
foreach($xpath->query('//table/tr') as $tr) {
	$bulletin = $xml->appendChild($xml->createElement("bulletin"));
	$title = $xpath->query('.//td[2]//a', $tr)->item(0)->nodeValue;
	$bulletin->appendChild($xml->createElement("title",$title));
	$type = $xpath->query('.//td[3]/font', $tr)->item(0)->nodeValue;
	$bulletin->appendChild($xml->createElement("type",$type));
}
echo $xml->saveXML();

?>