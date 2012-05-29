<?php
$eventId = '5082c45a-a0d0-6d2c-8a10-4f6bded8163b';

// specify the REST web service to interact with
$url = 'http://localhost/~jmertic/sugarplatformdemo/service/v4/rest.php';

// Open a curl session for making the call
$curl = curl_init($url);

// Tell curl to use HTTP POST
curl_setopt($curl, CURLOPT_POST, true);

// Tell curl not to return headers, but do return the response
curl_setopt($curl, CURLOPT_HEADER, false);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

// Set the POST arguments to pass to the Sugar server
$parameters = array(
    'user_auth' => array(
        'user_name' => 'admin',
        'password' => md5('sugar'),
        ),
    );
$json = json_encode($parameters);
$postArgs = array(
                'method' => 'login',
                'input_type' => 'JSON',
                'response_type' => 'JSON',
                'rest_data' => $json
                );
curl_setopt($curl, CURLOPT_POSTFIELDS, $postArgs);

// Make the REST call, returning the result
$response = curl_exec($curl);

// Make the REST call, returning the result
$response = curl_exec($curl);
if (!$response) {
    die("Connection Failure.\n");
}

// Convert the result from JSON format to a PHP array
$result = json_decode($response);
if ( !is_object($result) ) {
    var_dump($response);
    die("Error handling result.\n");
}
if ( !isset($result->id) ) {
    die("Error: {$result->name} - {$result->description}\n.");
}

// Get the session id
$sessionId = $result->id;

// First, see if the Speaker has submitted before
// Retieve the contact record we just created
$parameters = array(
    'session' => $sessionId, 
    'module_name' => 'pos_Speakers', 
    'query' => "pos_speakers.first_name = '{$_POST['first_name']}' and pos_speakers.last_name = '{$_POST['last_name']}' and pos_speakers.department = '{$_POST['organization']}'", 
    'order_by' => 'last_name', 
    'offset' => '',
    'select_fields' => array('first_name','last_name'),
    'link_name_to_fields_array' => array(),
    );

$json = json_encode($parameters);
$postArgs = array(
                'method' => 'get_entry_list',
                'input_type' => 'JSON',
                'response_type' => 'JSON',
                'rest_data' => $json
                );
curl_setopt($curl, CURLOPT_POSTFIELDS, $postArgs);

// Make the REST call, returning the result
$response = curl_exec($curl);
if (!$response) {
    die("Connection Failure.\n");
}

// Convert the result from JSON format to a PHP array
$result = json_decode($response);
if ( !is_object($result) ) {
    var_dump($response);
    die("Error handling result.\n");
}
if ( !isset($result->result_count) ) {
    die("Error: {$result->name} - {$result->description}\n.");
}

if ( $result->result_count > 0 ) {
    // we found them!
    $speakerId = $result->entry_list[0]->id;
}
else {
    // not found, add a new record
    $parameters = array(
        'session' => $sessionId,
        'module' => 'pos_Speakers',
        'name_value_list' => array(
            array('name' => 'first_name', 'value' => $_REQUEST['first_name']),
            array('name' => 'last_name', 'value' => $_REQUEST['last_name']),
            array('name' => 'suffix', 'value' => $_REQUEST['suffix']),
            array('name' => 'salutation', 'value' => $_REQUEST['salutation']),
            array('name' => 'title', 'value' => $_REQUEST['title']),
            array('name' => 'department', 'value' => $_REQUEST['organization']),
            array('name' => 'email1', 'value' => $_REQUEST['email']),
            array('name' => 'primary_address_street', 'value' => $_REQUEST['address']),
            array('name' => 'primary_address_city', 'value' => $_REQUEST['city']),
            array('name' => 'primary_address_state', 'value' => $_REQUEST['state']),
            array('name' => 'primary_address_postalcode', 'value' => $_REQUEST['postal_code']),
            array('name' => 'primary_address_country', 'value' => $_REQUEST['country']),
            array('name' => 'description', 'value' => $_REQUEST['bio']),
            ),
        );
    $json = json_encode($parameters);
    $postArgs = array(
                    'method' => 'set_entry',
                    'input_type' => 'JSON',
                    'response_type' => 'JSON',
                    'rest_data' => $json
                    );
    curl_setopt($curl, CURLOPT_POSTFIELDS, $postArgs);
    
    // Make the REST call, returning the result
    $response = curl_exec($curl);
    if (!$response) {
        die("Connection Failure.\n");
    }
    
    // Convert the result from JSON format to a PHP array
    $result = json_decode($response);
    if ( !is_object($result) ) {
        var_dump($response);
        die("Error handling result.\n");
    }
    if ( !isset($result->id) ) {
        die("Error: {$result->name} - {$result->description}\n.");
    }
    $speakerId = $result->id;
}

// Now, let's add a new Session record
$parameters = array(
    'session' => $sessionId,
    'module' => 'pos_Sessions',
    'name_value_list' => array(
        array('name' => 'name', 'value' => $_REQUEST['session_title']),
        array('name' => 'description', 'value' => $_REQUEST['abstract']),
        ),
    );
$json = json_encode($parameters);
$postArgs = array(
                'method' => 'set_entry',
                'input_type' => 'JSON',
                'response_type' => 'JSON',
                'rest_data' => $json
                );
curl_setopt($curl, CURLOPT_POSTFIELDS, $postArgs);

// Make the REST call, returning the result
$response = curl_exec($curl);
if (!$response) {
    die("Connection Failure.\n");
}

// Convert the result from JSON format to a PHP array
$result = json_decode($response);
if ( !is_object($result) ) {
    var_dump($response);
    die("Error handling result.\n");
}
if ( !isset($result->id) ) {
    die("Error: {$result->name} - {$result->description}\n.");
}

// Get the newly created record id
$talkId = $result->id;

// Now relate the speaker to the session
$parameters = array(
    'session' => $sessionId,
    'module_name' => 'pos_Sessions',
    'module_id' => $talkId,
    'link_field_name' => 'pos_speakers_pos_events',
    'related_ids' => array($talkId),
    );
$json = json_encode($parameters);
$postArgs = array(
                'method' => 'set_relationship',
                'input_type' => 'JSON',
                'response_type' => 'JSON',
                'rest_data' => $json
                );
curl_setopt($curl, CURLOPT_POSTFIELDS, $postArgs);

// Make the REST call, returning the result
$response = curl_exec($curl);
if (!$response) {
    die("Connection Failure.\n");
}

// Convert the result from JSON format to a PHP array
$result = json_decode($response);
if ( !is_object($result) ) {
    var_dump($response);
    die("Error handling result.\n");
}

// Now relate the session to the event
$parameters = array(
    'session' => $sessionId,
    'module_name' => 'pos_Sessions',
    'module_id' => $talkId,
    'link_field_name' => 'pos_sessions_pos_events',
    'related_ids' => array($eventId),
    );
$json = json_encode($parameters);
$postArgs = array(
                'method' => 'set_relationship',
                'input_type' => 'JSON',
                'response_type' => 'JSON',
                'rest_data' => $json
                );
curl_setopt($curl, CURLOPT_POSTFIELDS, $postArgs);

// Make the REST call, returning the result
$response = curl_exec($curl);
if (!$response) {
    die("Connection Failure.\n");
}

// Convert the result from JSON format to a PHP array
$result = json_decode($response);
if ( !is_object($result) ) {
    var_dump($response);
    die("Error handling result.\n");
}

header('Location: submitted.html');
