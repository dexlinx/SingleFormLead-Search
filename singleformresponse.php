<html>
<head>
<title>Lead Sign-Up and Search</title>
</head>
<body>

<?php


//Variables
//-------------------------------------------------------------
$apiKey = ""; //Client API Key
$idxID = "b092"; //MLS ID
$idxUrl = ""; //IDX Url


//Sanitize the POST variable
//-------------------------------------------------------------
function purica_array ($data = array()) {
	if (!is_array($data) || !count($data)) {
		return array();
	}
	foreach ($data as $k => $v) {
		if (!is_array($v) && !is_object($v)) {
			$data[$k] = htmlspecialchars(trim($v));
		}
		if (is_array($v)) {
			$data[$k] = purica_array($v);
		}
	}
	return $data;
}

$sanPost = purica_array($_POST);



//API Call Function
//-------------------------------------------------------------
function apiCall($apiKey,$callType,$endPoint,$data){
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "https://api.idxbroker.com/".$endPoint,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 60,
  CURLOPT_POSTFIELDS => $data,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => $callType,
  CURLOPT_HTTPHEADER => array(
    "accesskey: ".$apiKey,
    "apiversion: null",
    "cache-control: no-cache",
    "content-type: application/x-www-form-urlencoded",
    "outputtype: json"
    ),
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  echo "cURL Error #:" . $err;
} else {
$decode = json_decode($response,true);
return $decode;
}
}

//Lead Creation
//-------------------------------------------------------------

    $data = array(
      'firstName' => $sanPost["firstName"],
      'lastName' => $sanPost["lastName"],
      'email' => $sanPost["email"],
	  'phone' => $sanPost["phone"],
	  'password' => $sanPost["password"]
    );	
	
    $data = http_build_query($data); // encode and & delineate


$createLead = apiCall($apiKey,'PUT','leads/lead',$data);

if ($createLead != 'Lead already exists.'){ //Lead Doesn't Exist - We create it

	$leadId = $createLead[newID]; //Save the Returned Lead ID

}else{ //Lead Exists, Now we have to find its ID
	
	
//Get chunks of 500 and Locate Lead ID
//-------------------------------------------------------------
$offset = 0;

do{
	
	$currentLeads = apiCall($apiKey,'GET','leads/lead?rf[]=email&rf[]=id&offset='.$offset.'&limit=500',$data);
	$offset+=500;

if($currentLeads != NULL){
	foreach($currentLeads as $key => $value){ //Loop Through This Set of Leads
		if ($sanPost[email] == $value["email"]){
		$match = 'yes';
		$leadId = $value["id"]; //Keep the Existing Lead ID for later
		//echo $leadId;
		//echo $match;
		}
	}
}

}while($match != 'yes' && $currentLeads != NULL);
}


//Getting the List of Cities
//-----------------------------------------------------------------------------------
$cities = $sanPost['cities'];

//Add the Saved Search
$stripLp = str_replace(",", "",$sanPost['lp']);
$stripHp = str_replace(",", "",$sanPost['hp']);

//-----------------------------------------------------------------------------------
$searchArray = array('idxID' => $idxID,'lp' => $stripLp, 'hp' => $stripHp,'bd' => $sanPost['bd'],'tb' => $sanPost['ba'],'sqft' => $sanPost['sqFt'],'acres' => $sanPost['acres'],'city' => $cities);

$data = array(
 'searchName' => 'My Saved Search',
 'search' => $searchArray
);
	
    $data = http_build_query($data); // encode and & delineate

	
$endPoint = 'leads/search/'.$leadId;
apiCall($apiKey,'PUT',$endPoint,$data);



//Create City list for Redirect URL
//-----------------------------------------------------------------------------------
if (isset($sanPost['cities'])){
$myCities = implode("&city[]=",$cities);
$searchCities = "&city[]=".$myCities;
}

//Redirect to IDX Search
//-----------------------------------------------------------------------------------
$redirectUrl = $idxUrl."/idx/results/listings?idxID=".$idxID."&lp=".$sanPost['lp']."&hp=".$sanPost['hp']."&bd=".$sanPost['bd']."&tb=".$sanPost['ba']."&sqft=".$sanPost['sqFt']."&acres=".$sanPost['acres'].$searchCities;

echo "<center>Performing Search . . .";

echo "<script>";
echo "window.location.href= '".$redirectUrl."';";
echo "</script>";

?>

</body>
</html>
