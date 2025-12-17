<?php

include "db.php";

// JWT libraries 
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$headers=getallheaders();
$auth=$headers['Authorization'];
if(!preg_match('/Bearer\s+(\S+)/i', trim($auth), $matches)){
    echo json_encode(["error"=>"token is missing"]);
    exit;
}
$token=$matches[1];
$input = json_decode(file_get_contents("php://input"), true);
$country_id = $input['id'] ?? null;


$decode=JWT::decode($token,new key($jwt_secret,"HS256"));
$user_id=$decode->id;
$user=$db->get("users","*",['id'=>$user_id]);
if($user){
 $result=$db->delete("countries",["id"=>$country_id]);
 if(!$result){
    echo json_encode(["error"=>"deleting error occure"]);
    exit;
 }


    echo json_encode([
        "success"=>"country deleted successfully"
     ]);

    exit;
}
?>