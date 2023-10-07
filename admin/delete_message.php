<?php 
session_start();
require_once 'includes/auth_validate.php';
require_once './config/config.php';

$del_id = filter_input(INPUT_POST, 'del_id');
if ($del_id && $_SERVER['REQUEST_METHOD'] == 'POST') 
{

	if($_SESSION['admin_type']!='super'){
		$_SESSION['failure'] = "You don't have permission to perform this action";
    	header('location: terrains.php');
        exit;

	}

    $db = getDbInstance();
    $db->where('id', $del_id);
    $status = $db->delete('message');
    
    if ($status) 
    {
        $_SESSION['info'] = "message supprimé avec succès!";
        header('location: index.php');
        exit;
    }
    else
    {
    	$_SESSION['failure'] = "Impossible de supprimer messag";
    	header('location: index.php');
        exit;

    }
    
}