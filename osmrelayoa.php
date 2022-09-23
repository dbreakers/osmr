<?php

$authorize_url = "https://www.onlinescoutmanager.co.uk/oauth/authorize";
$token_url = "https://www.onlinescoutmanager.co.uk/oauth/token";

//	callback URL specified when the application was defined--has to match what the application says
$callback_uri = "https://www.2ndnewhawscouts.org.uk/osmr/osmrelayoa.php";

$test_api_url = "https://www.onlinescoutmanager.co.uk/oauth/resource";
$parameters = $_GET;


if ($parameters['oauthset']=='1'||$parameters['state']=='1') {
$client_id = "";
$client_secret = "";

} else {
 
$client_id = '';
$client_secret = "";
};

 


if ($_POST["authorization_code"]) {
 
//  echo("POST");
	//	what to do if there's an authorization code
    $tokens =  getAccessToken($_POST["authorization_code"]);
    $access_token = $tokens->access_token;
//	$access_token = getAccessToken($_POST["authorization_code"]);
//	$resource = getResource($access_token);
//	echo $resource;
} elseif ($_GET["code"]) {
 //   echo var_dump($_GET);
	$access_token = getAccessToken($_GET["code"]);
//	$resource = getResource($access_token->access_token);
//	echo ($resource);
 
	
	?>
    <script>
      function click_and_return(access,refresh) {
              window.opener.postMessage({access,refresh});
              window.close(); 
      }    
    </script><span style="font-size:14pt">
    <?php if ($access_token!="") { ?>    
      You have successfully logged onto OSM<br>  
    
	  <button style="width:200pt; height: 40pt" onclick="click_and_return('<?php echo $access_token->access_token; ?>','<?php echo $access_token->refresh_token; ?>') ; " type="button">Return to original window</button></span>
	<?php } else  {echo "OSMR is currently unavailable, please try again later"; } ?>
	<?php  
}  elseif ($_GET["refresh"]) {
   $token = getRefreshToken($_GET["refresh"],$_GET["oauthset"]);


} else {
	//	what to do if there's no authorization code
	$parameters = $_GET;
	getAuthorizationCode($parameters['scope'],$parameters['oauthset']);
}



//	step A - simulate a request from a browser on the authorize_url
//		will return an authorization code after the user is prompted for credentials
function getAuthorizationCode($scope,$st) {
	global $authorize_url, $client_id, $callback_uri;
	if ($_GET['access']=="false") {
    $scope = "section:member:read section:flexirecord:read section:event:read section:badge:read section:quartermaster:read section:programme:read section:attendance:read section:administration:admin section:finance:read";
	} else {
    $scope = "section:member:read section:flexirecord:read section:event:read section:badge:read section:quartermaster:read section:programme:read";
	}
	if ($_GET['access']=="noperson") {  $scope = "section:event:read section:badge:read section:quartermaster:read section:programme:read"; }
    //  $scope = "section:administration:read";
	$authorization_redirect_url = $authorize_url . "?response_type=code&client_id=" . $client_id ."&client_secret=".$client_secret ."&redirect_uri=" . $callback_uri . "&scope=".$scope.'&state='.$st; //section:member:read section:flexirecord:read section:event:read section:badge:read section:quartermaster:read";

	header("Location: " . $authorization_redirect_url);

	//	if you don't want to redirect
	// echo "Go <a href='$authorization_redirect_url'>here</a>, copy the code, and paste it into the box below.<br /><form action=" . $_SERVER["PHP_SELF"] . " method = 'post'><input type='text' name='authorization_code' /><br /><input type='submit'></form>";
}

//	step I, J - turn the authorization code into an access token, etc.
function getAccessToken($authorization_code) {
	global $token_url, $client_id, $client_secret, $callback_uri;

	$authorization = base64_encode("$client_id:$client_secret");
	$header = array("Authorization: Basic {$authorization}","Content-Type: application/x-www-form-urlencoded");
	$content = "grant_type=authorization_code&code=$authorization_code&redirect_uri=$callback_uri";

	$curl = curl_init();
	curl_setopt_array($curl, array(
		CURLOPT_URL => $token_url,
		CURLOPT_HTTPHEADER => $header,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_POST => true,
		CURLOPT_POSTFIELDS => $content
	));
	$response = curl_exec($curl);
	curl_close($curl);
//	echo("L:L:L:");
 
    if (json_decode($response)==null ) {echo "Access currently blocked by OSM this sometimes means too many people are using OSMR<br>";}
	if ($response === false) {
		echo "Failed";
		echo curl_error($curl);
		echo "Failed";
	} elseif (json_decode($response)->error) {
		echo "Error:<br />";
		echo $authorization_code;
		echo $response;
	}
//    echo(var_dump($response));//access token and refresh_token
//	return json_decode($response)->access_token;
return json_decode($response);
}

//	we can now use the access_token as much as we want to access protected resources
function getResource($access_token) {
	global $test_api_url;

	$header = array("Authorization: Bearer {$access_token}");

	$curl = curl_init();
	curl_setopt_array($curl, array(
		CURLOPT_URL => $test_api_url,
		CURLOPT_HTTPHEADER => $header,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_RETURNTRANSFER => true
	));
	$response = curl_exec($curl);
	curl_close($curl);
 //   echo $test_api_url;
  echo $response;
	return json_decode($response, true);
}


function getRefreshToken($authorization_code,$access_token) {
	global $token_url, $client_id, $client_secret, $callback_uri;
   
	$authorization = base64_encode("$client_id:$client_secret");
	$header = array("Authorization: Basic {$authorization}","Content-Type: application/x-www-form-urlencoded");
    //	$header = array("Content-Type: application/x-www-form-urlencoded");
	$content = "grant_type=refresh_token&refresh_token=".$authorization_code."&client_id=".$client_id."&client_secret=".$client_secret;
		$content = "grant_type=refresh_token&refresh_token=$authorization_code&client_id=$client_id&client_secret=$client_secret";
   // echo($content);
	$curl = curl_init();
	curl_setopt_array($curl, array(
		CURLOPT_URL => $token_url,
		CURLOPT_HTTPHEADER => $header,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_POST => true,
		CURLOPT_POSTFIELDS => $content
	));
	$response = curl_exec($curl);
	curl_close($curl);
//	echo("L:L:L:");
 
	if ($response === false) {
		echo "Failed";
		echo curl_error($curl);
		echo "Failed";
	} elseif (json_decode($response)->error) {
		echo "Error:<br />";
		echo $authorization_code;
		echo $response;
	}
  echo($response); //access token and refresh_token
//	return json_decode($response)->access_token;
return json_decode($response);
}


?>