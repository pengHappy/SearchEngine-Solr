<?php

include 'SpellCorrector.php';

// make sure browsers see this page as utf-8 encoded HTML
// header('Content-Type: text/html; charset=utf-8');

$limit = 500;
$query = isset($_GET["keyword"]) ? $_GET["keyword"] : false;
$requestHandler = isset($_GET["requestHandler"]) ? $_GET["requestHandler"] : false;
$algorithm = isset($_GET["algorithm"]) ? $_GET["algorithm"] : false;
$results = false;

if ($query)
{
	
	$res = array();
	
	// auto correct
	if(strlen($query) > 0) {
		$query_correct = SpellCorrector::correct($query);
		$res["auto_correct"] = $query_correct;
	}
	
	$responseArray = array();
	
	// The Apache Solr Client library should be on the include path
	// which is usually most easily accomplished by placing in the
	// same directory as this script ( . or current directory is a default
	// php include path entry in the php.ini)
	require_once('Apache/Solr/Service.php');

	// create a new solr service instance - host, port, and webapp
	// path (all defaults in this example)
	$solr = new Apache_Solr_Service('localhost', 8983, '/solr/myexample');
	if($requestHandler) {
		$solr->setSearchUrl('suggest');
	}
	else {
		$solr->setSearchUrl('select');
	}


	// if magic quotes is enabled then stripslashes will be needed
	if (get_magic_quotes_gpc() == 1)
	{
		$query = stripslashes($query);
	}

	// in production code you'll always want to use a try /catch for any
	// possible exceptions emitted  by searching (i.e. connection
	// problems or a query parsing error)
	try {
		if($algorithm == "pagerank") {
			$results = $solr->search($query, 0, $limit, array('sort' => 'id desc'));
		}
		else {
			$results = $solr->search($query, 0, $limit);
		}

	} catch (Exception $e) {
		// in production you'd probably log or email this error to an admin
		// and then show a special message to the user but for this example
		// we're going to show the full exception
		die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
	}
	
	if($requestHandler == 'suggest') {
		$temp = $results->response; // ??
		$pd = $results->_getParsedData();
		$suggestArray = $pd->suggest->suggest->$query->suggestions;
		$index = 0;
		foreach ($suggestArray as $suggest) {
			$responseArray[$index]["term"] = $suggest->term;
			$index = $index + 1;
		}
// 		echo json_encode($responseArray);
	}
	else {
		$docs = $results->response->docs;
// 		$responseArray = array();
		$index = 0;
		foreach ($docs as $doc) {
			$docFields = $doc->_getFields();
			$responseArray[$index]["date"] = $docFields["date"];
			$responseArray[$index]["title"] = $docFields["og_title"];
			$responseArray[$index]["url"] = $docFields["og_url"];
			$responseArray[$index]["description"] = $docFields["description"];
			$index = $index + 1;
		}
// 		echo json_encode($responseArray);
	}
	
	$res["results"] = $responseArray;
	
	echo json_encode($res);
	
}

?>



