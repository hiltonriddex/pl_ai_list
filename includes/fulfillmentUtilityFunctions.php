<?php

//Function takes URI and a boolean that decides to return a URL or URL components
function spotifyURLconstruct($str_URI = '', $bln_returnDeconstructed = false){
	//Initialize vars for URL build and return
	$str_urlBuild = '';
	$arr_URIdeconstructed = [];

	//Explode the supplied array using ":" to extract the pieces to build the required Spotify URL
	if($str_URI !== ''){
		$arr_URIdeconstructed = explode(":", $str_URI);
		if($bln_returnDeconstructed === true){
			return $arr_URIdeconstructed;
		}else{
			//If array and target element ID present, duild the URL
			if($arr_URIdeconstructed[2] && $arr_URIdeconstructed[4]){
				$str_urlBuild = "https://open.spotify.com/user/".$arr_URIdeconstructed[2]."/playlist/".$arr_URIdeconstructed[4];
			}

			//Return the constructed URL
			return $str_urlBuild;
		}
	}

	return false;
}

//Function used to acquire the access token for a spotify account.
function SPOTIFYaccessToken(){
	$str_client_id = SPOTIFY_CLIENT_ID;
	$str_client_secret = SPOTIFY_SECRET;

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, SPOTIFY_ACCESS_URL );
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt($ch, CURLOPT_POST, 1 );
	curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials' );
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Basic '.base64_encode($str_client_id.':'.$str_client_secret)));

	$json_result = curl_exec($ch);
	$int_status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

	return $json_result;
}

//Function used to get playlists from SPOTIFY, used in events where the SOLR lookup fails completely.
function SPOTIFYplaylistQuery($arr_querySet = [], $int_rows_returned = 50){
	//Return array
	$arr_ReturnARRset = array();

	if($arr_querySet){
		//Genre search string
		$str_genre = '';
		$str_genreNoPreText = '';

		if($arr_querySet['primary_genre']) {
			if(str_word_count($arr_querySet['primary_genre']) > 1){
				$str_genre .= '"'.$arr_querySet['primary_genre'].'"';
				$str_genreNoPreText .= '"'.$arr_querySet['primary_genre'].'"';
			}else{
				$str_genre .= $arr_querySet['primary_genre'];
				$str_genreNoPreText .= $arr_querySet['primary_genre'];
			}
		}
		if($arr_querySet['sub_genre'] && $arr_querySet['sub_genre'] !== $arr_querySet['primary_genre']){
			if($arr_querySet['sub_genre']) $str_genre .= ',';
			if(str_word_count($arr_querySet['sub_genre']) > 1){
				$str_genre .= '"'.$arr_querySet['sub_genre'].'"';
				$str_genreNoPreText .= '"'.$arr_querySet['sub_genre'].'"';
			}else{
				$str_genre .= $arr_querySet['sub_genre'];
				$str_genreNoPreText .= $arr_querySet['sub_genre'];
			}
		}

		//Base SPOTIFY url
		$str_spotifyURL = 'https://api.spotify.com/v1/search?q='.$str_genre.'&type=track&limit=50';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $str_spotifyURL);
		curl_setopt($ch, CURLOPT_TIMEOUT, 4);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:x.x.x) Gecko/20041107 Firefox/x.x");
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$_SESSION['ACCESS_TOKEN']));
		$arr_jsonReturn = curl_exec($ch);
		$int_status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		$arr_curlReturn = (array)json_decode($arr_jsonReturn, true);

		//If return array
		if($arr_curlReturn && $int_status_code === 200){
			foreach($arr_curlReturn['tracks']['items'] AS $playlistUnit){
				$arr_ReturnARRset[] = array("id"=>$playlistUnit['album']['id'], "title"=>$playlistUnit['album']['name'], "image"=>$playlistUnit['album']["images"][0]['url'], "description"=>'', "description_featured"=>'', "playlist_uri"=>$playlistUnit['album']['external_urls']['spotify']);
			}
		}

		//Close connection
		curl_close($ch);
	}

	return $arr_ReturnARRset;
}

//Function used to fetch target data from the specified users playlist
function SPOTIFYplaylistDetails($str_username = '', $str_playlistId = '', $str_targetElement = ''){
	//Return array
	$arr_return = [];

	//Base SPOTIFY url
	$str_spotifyURL = "https://api.spotify.com/v1/users/".$str_username."/playlists/".$str_playlistId;

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $str_spotifyURL);
	curl_setopt($ch, CURLOPT_TIMEOUT, 4);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:x.x.x) Gecko/20041107 Firefox/x.x");
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$_SESSION['ACCESS_TOKEN']));
	$arr_jsonReturn = curl_exec($ch);
	$int_status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

	$arr_return['curlResult'] = (array)json_decode($arr_jsonReturn, true);
	$arr_return['statusCode'] = $int_status_code;

	if($arr_return['statusCode'] === 200 && $arr_return['curlResult']){
		if($str_targetElement !== ''){
			switch ($str_targetElement){
				case "image":
					if(isset($arr_return['curlResult']['images'][0]['url'])) $arr_return['targetElement'] = $arr_return['curlResult']['images'][0]['url'];
					break;
				case "followers":
					if(isset($arr_return['curlResult']['followers']['total'])) $arr_return['targetElement'] = $arr_return['curlResult']['followers']['total'];
					break;
				default:
			}
		}
	}

	return $arr_return;
}

//Function used to truncate text to a certain length.
function truncateText($str_text, $int_length, $str_dots = "...") {
    return (strlen($str_text) > $int_length) ? substr($str_text, 0, $int_length - strlen($str_dots)) . $str_dots : $str_text;
}

//This function loops through a set of viable results from the playlist.net DB and check all the rich text required fields are present.
function locateViableRichTextElement($int_resultCountTotal = 0, $arr_resultsSet = []){
	$bln_viableEncountered = false;

	if($int_resultCountTotal && $arr_resultsSet){
		for ($i=1; $i<=5; $i++){
			if(!$bln_viableEncountered){
				//Randomly fetch one of the playlists to return to the user
				$int_RandomQueryFetch = rand(0, ($int_resultCountTotal - 1));
				if($arr_resultsSet[$int_RandomQueryFetch]['playlist_uri'] && $arr_resultsSet[$int_RandomQueryFetch]['description'] && $arr_resultsSet[$int_RandomQueryFetch]['title']){
					$bln_viableEncountered = true;
					return $int_RandomQueryFetch;
				}
			}
		}
		return false;
	}
}