<?php

$_SESSION['ACCESS_TOKEN'] = "";
//.......................

//DIAGFLOW
define('CLIENT_ACCESS_TOKEN', "Authorization: Bearer xxxxxxxxxxxxxxxxxxxxxxxxxxxxxx");
define('DEVELOPER_ACCESS_TOKEN', "Authorization: Bearer xxxxxxxxxxxxxxxxxxxxxxxxxxxxx");

define('API_AI_BASE_URL', "https://api.api.ai/v1/");
define('API_TRANSMISSION_MEDIUM', "POST");
define('TRANSMISSION_CONTENT_TYPE', "Content-Type: application/json");
//.....

//PL_AI_LIST
define('RETURN_COUNT', 50);
define('BOT_OUTPUT_MIN_COUNT', 1);
define('BOT_OUTPUT_MAX_COUNT', 5);
define('USER_CONVERSATION_RETENTION_COUNT', 2);
//.......

//SPOTIFY
define('SPOTIFY_CLIENT_ID', "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxx");
define('SPOTIFY_SECRET', "xxxxxxxxxxxxxxxxxxxxxxxxxxx");
define('SPOTIFY_ACCESS_URL', "https://accounts.spotify.com/api/token");
//.......