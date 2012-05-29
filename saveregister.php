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

// Add registered attendee
$parameters = array(
    'session' => $sessionId,
    'module' => 'pos_Attendees',
    'name_value_list' => array(
        array('name' => 'first_name', 'value' => $_REQUEST['first_name']),
        array('name' => 'last_name', 'value' => $_REQUEST['last_name']),
        array('name' => 'suffix', 'value' => $_REQUEST['suffix']),
        array('name' => 'salutation', 'value' => $_REQUEST['salutation']),
        array('name' => 'title', 'value' => $_REQUEST['title']),
        array('name' => 'department', 'value' => $_REQUEST['organization']),
        array('name' => 'phone_work', 'value' => "({$_REQUEST['area_code']}) {$_REQUEST['phone1']}-{$_REQUEST['phone2']}"),
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
    die("Error handling result.\n");
}
if ( !isset($result->id) ) {
    die("Error: {$result->name} - {$result->description}\n.");
}
// Get the newly created record id
$attendeeId = $result->id;

// Now relate the attendee to the event
$parameters = array(
    'session' => $sessionId,
    'module_name' => 'pos_Attendees',
    'module_id' => $attendeeId,
    'link_field_name' => 'pos_attendees_pos_events',
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

header('Location: registered.html');
