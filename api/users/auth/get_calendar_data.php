<?php
header('Content-Type: application/json');
use Config\Utility_Functions;

require_once '../../../config/bootstrap_file.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $decodedToken = $api_status_code_class_call->ValidateAPITokenSentIN();
    $user_pubkey = $decodedToken->usertoken;
    
    $user_id = $api_users_table_class_call::checkIfIsUser($user_pubkey);

    // Unauthorized user
    if ( !$user_id ){
        $text = $api_response_class_call::$unauthorized_token;
        $errorcode = $api_error_code_class_call::$internalHackerWarning;
        $maindata = [];
        $hint = ["Please log in to access."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondUnauthorized($maindata, $text, $hint, $linktosolve, $errorcode);
    }
  
    $userid ="";
    // Assuming you want to fetch attendance for the current month
    $startDate = date('Y-m-01');
    $endDate = date('Y-m-t');

    // Fetch the user's attendance data for the specified date range
    $attendanceData = Utility_Functions::getUserAttendanceByDateRange($userid, $startDate, $endDate);

    // Prepare the response
    $maindata = [
        'status' => 'success',
        'data' => $attendanceData,
    ];

    $text = $api_response_class_call::$getCalendar;
    $api_status_code_class_call->respondOK($maindata, $text);
} else {
    // Handle invalid HTTP method
    $text = $api_response_class_call::$methodUsedNotAllowed;
    $errorcode = $api_error_code_class_call::$internalHackerWarning;
    $maindata = [];
    $hint = ["Ensure to use the POST method for getting data."];
    $linktosolve = "https://";
    $api_status_code_class_call->respondMethodNotAllowed($maindata, $text, $hint, $linktosolve, $errorcode);

}

