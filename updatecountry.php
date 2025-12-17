<?php
include "db.php";
// JWT libraries 
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$headers=getallheaders();
$auth=$headers['Authorization'];
if (!preg_match('/Bearer\s+(\S+)/i', trim($auth), $matches)) {
    echo json_encode(["error" => "token is missing"]);
    exit;
}
$token = $matches[1];
// decode token to check the user existing 
$decode=jwt::decode($token,new key($jwt_secret,"HS256"));
$user_id=$decode->id;
$user=$db->get("users","*",["id"=>$user_id]);
if(!$user){
    echo json_encode(["error"=>"invalid token"]);
    exit;
}
$input = json_decode(file_get_contents("php://input"), true);
$id = $input['id'] ?? null;

    
$country=$db->get("countries","*",['id'=>$id]);
if(!$country){
    echo json_encode(["error"=>"invalid country id "]);
    exit;
}

$updatedata= [];

if(isset($input['iso'])){
    $updatedata['iso']=strtoupper(trim($input['iso']));
}
if(isset($input['name'])){
    $updatedata['name']=strtoupper(trim($input['name']));
}
if(isset($input['nicename'])){
    $updatedata['nicename']=ucwords(strtolower(trim($input['nicename'])));
}
if(isset($input['iso3'])){
    $updatedata['iso3']=strtoupper(trim($input['iso3']));
}
if(isset($input['numcode'])){
    $updatedata['numcode']=trim($input['numcode']);
}

if(isset($input['phonecode'])){
    $updatedata['phonecode']=trim($input['phonecode']);
}

if(isset($input['status'])){
$updatedata['status']=trim($input['status']);
}



$result=$db->update("countries",$updatedata,["id"=>$id]);

if(!$result){
    echo json_encode(["error"=>"country updating error"]);
    exit;
}
echo json_encode([
    "success"=>"country updated successfull",
    "data"=>$result
]);


?>