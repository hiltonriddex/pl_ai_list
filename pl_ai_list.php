<?php
/* Plugin Name: PL_AI_LIST
   Plugin URI: http://riddex.me.uk
   description: Connector for pl_ai_list chatbot
   Version: 0.1.0
   Author: Hilton Riddex
   Author URI: http://riddex.me.uk
   License: GPL2
*/

  session_start();

  //File includes
  include_once __DIR__."/includes/globals.php";
  include_once __DIR__."/includes/fulfillmentHandler.php";
  include_once __DIR__."/includes/fulfillmentUtilityFunctions.php";
  //.............

  //Set default timezone
  date_default_timezone_set('Europe/London');

  //Capture SPOTIFY Access Token
  $json_SpotifyTokenAccessCall = SPOTIFYaccessToken();
  if($json_SpotifyTokenAccessCall){
  	$arr_decodedSpotifyAccessToken = json_decode($json_SpotifyTokenAccessCall,true);
  	if($arr_decodedSpotifyAccessToken['access_token'] && $arr_decodedSpotifyAccessToken['token_type'] === 'Bearer') $_SESSION['ACCESS_TOKEN'] = $arr_decodedSpotifyAccessToken['access_token'];
  }

  //Capture headers from received calls
  $arr_capturedHeaders = print_r(json_decode(json_encode(getallheaders())), true);

  //Capture POST body contents. It is received in JSON formatting.
  $arr_postContents = json_decode(file_get_contents('php://input'), true);

  //If the origin of the call is through our DIAGFLOW, continue to fulfillment
  if($arr_capturedHeaders){
  	//Call to JSON fulfillment creation function
  	$arr_returnData = Fulfillment::processInitialData($arr_postContents);

  	//Return json fulfillment to calling service
  	if($arr_returnData){
	    header(TRANSMISSION_CONTENT_TYPE);
	    echo $arr_returnData['jsonConstruct'];
  	}
  }

