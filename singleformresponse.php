<html>
<head>
<title>Lead Sign-Up and Search</title>
</head>
<body>

<?php


//Variables
//-------------------------------------------------------------
$apiKey = ""; //Client API Key
$idxID = ""; //MLS ID
$idxUrl = ""; //Example: http://yoursub.idxbroker.com


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
  CURLOPT_TIMEOUT => 30,
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

//Get List of Leads
//-------------------------------------------------------------
$currentLeads = apiCall($apiKey,'GET','leads/lead',$data);

//Does Posted Lead Exist?
//-------------------------------------------------------------
foreach($currentLeads as $key => $value){

if ($sanPost[email] == $value["email"]){
	$match = 'yes';
}
}

//If No Match - Insert the Lead
//-----------------------------------------------------------------------------------
if ($match != 'yes'){
	
    $data = array(
      'firstName' => $sanPost["firstName"],
      'lastName' => $sanPost["lastName"],
      'email' => $sanPost["email"],
	  'phone' => $sanPost["phone"],
	  'password' => $sanPost["password"]
    );	
	
    $data = http_build_query($data); // encode and & delineate

//Create Lead
apiCall($apiKey,'PUT','leads/lead',$data);
}


//Get The Existing or New Lead ID
//-----------------------------------------------------------------------------------
$updatedLeads = apiCall($apiKey,'GET','leads/lead',$data);


foreach($updatedLeads as $key => $value){

if ($sanPost[email] == $value["email"]){
	$leadId = $value["id"];
}
}
if (is_null($leadId)){
	Echo "No lead found";
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
