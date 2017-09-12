<html>
<head>
<title>Lead Sign-Up and Search</title>
</head>
<body>

<?php

//Variables
//-------------------------------------------------------------
$apiKey = "IrTr-Nt3YBkA8GG3vorSuC"; //Client API Key
$idxID = "d025"; //MLS ID
$idxUrl = "http://trappa.idxbroker.com";

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

if ($_POST[email] == $value["email"]){
	$match = 'yes';
}
}

//If No Match - Insert the Lead
//-----------------------------------------------------------------------------------
if ($match != 'yes'){
	
    $data = array(
      'firstName' => $_POST["firstName"],
      'lastName' => $_POST["lastName"],
      'email' => $_POST["email"],
	  'phone' => $_POST["phone"],
	  'password' => $_POST["password"]
    );	
	
    $data = http_build_query($data); // encode and & delineate

//Create Lead
apiCall($apiKey,'PUT','leads/lead',$data);
}


//Get The Existing or New Lead ID
//-----------------------------------------------------------------------------------
$updatedLeads = apiCall($apiKey,'GET','leads/lead',$data);


foreach($updatedLeads as $key => $value){

if ($_POST[email] == $value["email"]){
	$leadId = $value["id"];
}
}
if (is_null($leadId)){
	Echo "No lead found";
}

//Getting the List of Cities
//-----------------------------------------------------------------------------------
$cities = $_POST['cities'];

//Add the Saved Search
$stripLp = str_replace(",", "",$_POST['lp']);
$stripHp = str_replace(",", "",$_POST['hp']);

//-----------------------------------------------------------------------------------
$searchArray = array('idxID' => $idxID,'lp' => $stripLp, 'hp' => $stripHp,'bd' => $_POST['bd'],'tb' => $_POST['ba'],'sqft' => $_POST['sqFt'],'acres' => $_POST['acres'],'city' => $cities);

$data = array(
 'searchName' => 'My Saved Search',
 'search' => $searchArray
);
	
    $data = http_build_query($data); // encode and & delineate

	
$endPoint = 'leads/search/'.$leadId;
apiCall($apiKey,'PUT',$endPoint,$data);



//Create City list for Redirect URL
//-----------------------------------------------------------------------------------
if (isset($_POST['cities'])){
$myCities = implode("&city[]=",$cities);
$searchCities = "&city[]=".$myCities;
}

//Redirect to IDX Search
//-----------------------------------------------------------------------------------
$redirectUrl = $idxUrl."/idx/results/listings?idxID=".$idxID."&lp=".$_POST['lp']."&hp=".$_POST['hp']."&bd=".$_POST['bd']."&tb=".$_POST['ba']."&sqft=".$_POST['sqFt']."&acres=".$_POST['acres'].$searchCities;

echo "<center>Performing Search . . .";

//echo "<script>";
//echo "window.location.href= '".$redirectUrl."';";
//echo "</script>";

?>

</body>
</html>
