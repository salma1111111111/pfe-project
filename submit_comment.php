<?php

session_start();
require_once 'admin/config/config.php';

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
$last_id = $db->insert('avis', $data_to_store);

sleep(1);

// print message
if (true || $last_id)
    response(true, $data_to_store, "success: data added successfully");
else
    response(false, $last_id, "Error: when submiting data");
