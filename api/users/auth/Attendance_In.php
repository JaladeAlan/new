<?php
header('Content-Type: application/json');
use Config\Utility_Functions;

require_once '../../../config/bootstrap_file.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the request body
    $decodedToken = $api_status_code_class_call->ValidateAPITokenSentIN();
    $user_pubkey = $decodedToken->usertoken;
    
    $user_id = $api_users_table_class_call::checkIfIsUser($user_pubkey);
    echo $user_id;  
    // Unauthorized user
    if ( !$user_id ){
        $text = $api_response_class_call::$unauthorized_token;
        $errorcode = $api_error_code_class_call::$internalHackerWarning;
        $maindata = [];
        $hint = ["Please log in to access."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondUnauthorized($maindata, $text, $hint, $linktosolve, $errorcode);
    }
    
    $request_body = file_get_contents('php://input');
    $data = json_decode($request_body);

    // Extract data from the request
    $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : '';
    $latitude = isset($data->latitude) ? floatval($data->latitude) : null;
    $longitude = isset($data->longitude) ? floatval($data->longitude) : null;
    $mac_address = isset($data->mac_address) ? $utility_class_call::escape($data->mac_address) : "";
    $company_id = isset($data->company_id) ? $utility_class_call::escape($data->company_id) : "";

    // Validate input data
    if (!is_numeric($latitude) || !is_numeric($longitude) || empty($mac_address)) {
        $text = $api_response_class_call::$attendanceFailed;
        $errorcode = $api_error_code_class_call::$internalUserWarning;
        $maindata = [];
        $hint = ["Ensure to send valid data to the API fields: latitude, longitude, and mac_address."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondBadRequest($maindata, $text, $hint, $linktosolve, $errorcode);
    }
    $latitudeValue = null;
    $longitudeValue = null;
    $validLatitude =$api_users_table_class_call::latitudeSelector($company_id, $latitudeValue);
    $validLongitude =$api_users_table_class_call::longitudeSelector($company_id, $longitudeValue);
    
$validLocation = Utility_Functions::validateAttendanceLocation($latitude, $longitude, $validLatitude, $validLongitude);

if (!$validLocation) {
    // Location validation failed
    $text = $api_response_class_call::$invalidLocation;
    $errorcode = $api_error_code_class_call::$internalUserWarning;
    $maindata = [];
    $hint = ["Invalid location for clock in. Make sure you are at the correct location."];
    $linktosolve = "https://";
    $api_status_code_class_call->respondBadRequest($maindata, $text, $hint, $linktosolve, $errorcode);
}
 

    $user_id =$api_users_table_class_call::getUserByIdOrEmail($user_id); 
    $attendance_result = Utility_Functions ::takeAttendance($user_id, $mac_address);

    if ($attendance_result) {
        // Attendance recorded successfully
        $text = $api_response_class_call::$attendanceRecorded;
        $text = Utility_Functions::addNotification($user_id, $text);
        $api_status_code_class_call->respondOK([], $text);
    } else {
        // Error recording attendance
        $text = $api_response_class_call::$attendanceFailed;
        $errorcode = $api_error_code_class_call::$internalServerError;
        $maindata = [];
        $hint = ["Error recording attendance. Please try again in location."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondInternalServerError($maindata, $text, $hint, $linktosolve, $errorcode);
    }
} else {
    $text = $api_response_class_call::$methodUsedNotAllowed;
    $errorcode = $api_error_code_class_call::$internalHackerWarning;
    $maindata = [];
    $hint = ["Ensure to use the POST method for taking attendance."];
    $linktosolve = "https://";
    $api_status_code_class_call->respondMethodNotAllowed($maindata, $text, $hint, $linktosolve, $errorcode);
}
?>