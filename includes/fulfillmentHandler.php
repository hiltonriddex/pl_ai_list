<?php

//file includes
include_once "fulfillmentUtilityFunctions.php";
//.............

class Fulfillment {

	//If a fulfillment request is initialted, responce is processed and fulfillment returned
	function processInitialData($arr_data) {
		//Initialize JSON return structure
		$str_jsonConstruct = '';
		$arr_returnData = [];
		$arr_botRecommendationSet = array("Recommend","Suggest","Find","Play");
		$str_directAction = 'defaultAction';
                            
		//Check for the presence of the dataset
		if($arr_data){      
			//Fetch logic variables out of the captured POST variable
			if(isset($arr_data['id'])) $str_sessionId = $arr_data['id'];
			if(isset($arr_data['result']['parameters']['Action'])){
				if(is_array($arr_data['result']['parameters']['Action'])){
					if(isset($arr_data['result']['parameters']['Action'][0])) $str_directAction = $arr_data['result']['parameters']['Action'][0];
				}else{      
					$str_directAction = $arr_data['result']['parameters']['Action'];
				}           
			}               
			$str_genre = $arr_data['queryResult']['parameters']['Genre'];
			$str_mood = $arr_data['queryResult']['parameters']['Mood'];
                            
			if(in_array($str_directAction,$arr_botRecommendationSet)){
				$str_globalDefaultNew = 'New Music';
			}else{          
				$str_globalDefaultNew = $arr_data['result']['parameters']['GlobalDefaultNew'];
			}               
			$str_globalUplifting = $arr_data['result']['parameters']['GlobalUplifting'];
			$str_negatingStatement = $arr_data['result']['parameters']['Negating_triggers'];
                            
			//If negated statement found, supply generic awesome playlists, text and menu
			if($str_negatingStatement !== '' && $str_negatingStatement === 'NegateStatement'){
				$str_genre = '';
				$str_mood = '';
				$str_globalDefaultNew = 'New Music';
				$str_globalUplifting = '';
			}               
                            
			//Set the search slug to be used in playlist recovery
			$str_searchSlug = ($str_genre ? $str_genre : $str_mood);
			//Capture all supplied Artists and use in fetch query
			$arr_capturedArtists = [];
			//String to hold search construct
			$str_SPOTIFYSearchArtist = '';
                            
			//Diagflow supplied artist either in Array or string form
			if(is_array($arr_data['result']['parameters']['music-artist'])){
				if(count($arr_data['result']['parameters']['music-artist']) >= 1){
					foreach($arr_data['result']['parameters']['music-artist'] AS $snaggedArtist){
						if(trim($snaggedArtist) !== '') $arr_capturedArtists[] = ucwords($snaggedArtist);
					}       
				}           
			}else{          
				if(count($arr_data['result']['parameters']['music-artist']) >= 1){
					if(trim($arr_data['result']['parameters']['music-artist']) !== ''){
						$arr_capturedArtists[] = ucwords($arr_data['result']['parameters']['music-artist']);
					}       
				}           
			}              
                            
			//Fulfillment target service: APIAI
			$_SESSION['FULFILLMENT'] = "APIAI";
			//...................
                            
			$str_masterActionSet = "Recommendation";
                            
			//This is the global call to action from the DIAGFLOW agent, this can be extended if required to fulfill other user requests
			if($str_masterActionSet === 'Recommendation'){
				//If genre detected, fetch out the value and ID from tables, this will be used in playlist filtering
				if(true){   
					$bln_recordSetPresent = false;
					$arr_LookupGenparams = array("sub_genre"=>($str_searchSlug !== '' ? ($str_searchSlug === "R&amp;B" ? "RnB" : $str_searchSlug) : ''), "primary_genre"=>(isset($arr_ParentGenreMoodset['name']) ? ($arr_ParentGenreMoodset['name'] === 'R&amp;B' ? "RnB" : $arr_ParentGenreMoodset['name']) : ($str_searchSlug !== '' ? ($str_searchSlug === "R&amp;B" ? "RnB" : $str_searchSlug) : 'All')), false, "SPOTIFYartists"=>($str_SPOTIFYSearchArtist !== '' ? $str_SPOTIFYSearchArtist : ''));
                            
					//SPOTIFY API call
					if(!$bln_recordSetPresent){
						$arr_resultSet = SPOTIFYplaylistQuery($arr_LookupGenparams, RETURN_COUNT);
					}       
				}           
			}               
		}                   
                            
		if($arr_resultSet){ 
			$int_count = count($arr_resultSet);
			$int_random_selection = 1;
			if($int_count >= 2){
				$int_random_selection = rand(1,$int_count);
			}               
                            
			$arr_respose_pre = array("Here you go, I found this for you!","Awesome music served hot!","Top tunes, my gift to you.","Enjoy!...","Just for you, top tunes!","Look what I found for you!");
			$int_count_reponse = count($arr_respose_pre);
			$int_random_responce = rand(1,$int_count_reponse);
                            
			$str_jsonConstruct = '{
			  "fulfillmentText": "' . $arr_respose_pre[$int_random_responce] .'",
			  "fulfillmentMessages": [
			    {           
			      "simpleResponses": {
			        "simpleResponses": [
			          {     
			            "textToSpeech": "' . $arr_respose_pre[$int_random_responce] . '",
			            "displayText": "' . $arr_respose_pre[$int_random_responce] . '"
			          }     
			        ]       
			      }         
			    }           
			  ],            
			  "source": "riddex.me.uk",
			  "payload": {  
			    "google": { 
			      "expectUserResponse": true,
			      "richResponse": {
			        "items": [
			          {     
			            "simpleResponse": {
			              "textToSpeech": "' . $arr_respose_pre[$int_random_responce] . '"
			            }   
			          }     
			        ]       
			      }         
			    },          
			    "facebook": {
			      "text": "Hello, Facebook!"
			    },          
			    "slack": {  
			      "text": "' . $arr_respose_pre[$int_random_responce] . '",
			      "attachments": [
			        {       
			          "fallback": "' . $arr_respose_pre[$int_random_responce] . '",
			          "color": "#36a64f",
			          "pretext": "",
			          "author_name": "",
			          "author_link": "' . $arr_resultSet[$int_random_selection]["playlist_uri"] . '",
			          "author_icon": "",
			          "title": "' . $arr_resultSet[$int_random_selection]["title"] . '",
			          "title_link": "' . $arr_resultSet[$int_random_selection]["playlist_uri"] . '",
			          "text": "' . $arr_resultSet[$int_random_selection]["description"] . '",
			          "image_url": "' . $arr_resultSet[$int_random_selection]["image"] . '",
			          "thumb_url": "' . $arr_resultSet[$int_random_selection]["image"] . '",
			          "footer": "pl_ai_list"
			        }       
			      ]         
			    }           
			  }             
			}';             
		}                   
                            
		//Final constructs added to the return data array
		$arr_returnData['jsonConstruct'] = $str_jsonConstruct;
                            
		//return JSON formatted fullfilment request
		return $arr_returnData;
	}                       
}                           
                            