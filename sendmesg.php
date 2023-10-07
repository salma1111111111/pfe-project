<?php




define('DB_HOST', "localhost");
define('DB_USER', "root");
define('DB_PASSWORD', "");
define('DB_NAME', "corephpadmin");


$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);



if($_SERVER['REQUEST_METHOD'] == "POST")
{    
     $name = $_POST['name'];
     $email = $_POST['email'];
     $objet = $_POST['objet'];
     $message = $_POST['message'];

     
     $sql = "INSERT INTO message VALUES (NULL,'$name','$email','$objet','$message')";

     if (mysqli_query($conn, $sql)) {
        echo "succes !";
     
     } else {
        echo "Error: " . $sql . ":-" . mysqli_error($conn);
     }
     mysqli_close($conn);
}

header("location:http://localhost/e/contactus.php");
?>