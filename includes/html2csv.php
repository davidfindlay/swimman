<?php 

require_once("simple_html_dom.php");

function html2csv($uploadfile, $tempCsvFile) {

	$htmlFile = fopen ( $uploadfile, "r" );
	$table = fread ( $htmlFile, filesize ( $uploadfile ) );
	
	$html = str_get_html($table);
	
	$tempCsv = fopen ( $tempCsvFile, "w" );
	
	foreach($html->find('tr') as $element)
	{
		$td = array();
		foreach( $element->find('th') as $row)
		{
			$td [] = $row->plaintext;
		}
		fputcsv($tempCsv, $td);
	
		$td = array();
		foreach( $element->find('td') as $row)
		{
			$td [] = $row->plaintext;
		}
		fputcsv($tempCsv, $td);
	}
	
	fclose($htmlFile);
	fclose($tempCsv);

}

?>