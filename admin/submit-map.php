<?php

session_start();
require_once 'config/config.php';
require_once BASE_PATH . '/includes/auth_validate.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: *");
header("Content-type:application/json");
header("Access-Control-Allow-Headers: *");

function response($status, $data, $message)
{
    $data = [
        "success" => $status,
        "data" => $data,
        "message" => $message
    ];

    echo json_encode($data);
    return $data;
}

// prosess add space in database
$data_to_store = array_filter($_POST);
//Insert timestamp
// $data_to_store['created_at'] = date('Y-m-d H:i:s');
$db = getDbInstance();
$last_id = $db->insert('lotissment', $data_to_store);

// print message
if ($last_id)
    response(true, json_decode($_POST['cords']), "success: data added successfully");
else
    response(false, $last_id, "Error: when submiting data");
