<?php 
header("Access-Control-Allow-Origin: *");
// was used when API address moved but apps were live on app store.
$osm = "https://www.onlinescoutmanager.com/";
$ch = curl_init();
//var_dump($_GET);
$path = $_SERVER['REQUEST_URI'];
$parameters = $_GET;
//var_dump($_GET);
//$path2=parse_str($_GET,$parameters);
//var_dump($parameters);
//if (($key = array_search('osmpath', $parameters)) !== false) {
$path_add = $parameters['osmpath'];
   unset($parameters['osmpath']);
//echo("unset");
//}
$path = http_build_query($parameters);
//echo($path_add.'?'.$path);
//echo($_POST);
//echo('https://www.onlinescoutmanager.co.uk/'.$path_add.'?'.$path);

// Insert Yours here 

$_POST['apiid'] = '41';
$_POST['token'] = 'ce3dc1dc772c059e13e615d698b81a8b';


/* Forward POST on to new API: */
curl_setopt($ch, CURLOPT_URL, 'https://www.onlinescoutmanager.co.uk/'.$path_add.'?'.$path);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($_POST));
//curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($_GET));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
echo curl_exec($ch);

exit;

?>