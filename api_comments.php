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
$db->where("lot_id", $data_to_store['lot_id']);
$db->orderBy('ID', 'DESC');
$last_id = $db->get('avis');

// print message
if (true || $last_id)
    response(true, $last_id, "success: data added successfully");
else
    response(false, $last_id, "Error: when submiting data");
