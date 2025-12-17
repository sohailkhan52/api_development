<?php
include "db.php";
// geting token through proper authorization 
$headers = getallheaders();
$auth = $headers['Authorization'] ?? '';

if  (!preg_match('/Bearer\s+(\S+)/i', trim($auth), $matches)) {

    echo json_encode(["error" => "Token missing"]);
    exit;
}

$token=$matches[1];

// JWT libraries 
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// data coming through post method 
$iso=$_POST["iso"];
$name=$_POST["name"];
$nicename=$_POST["nicename"];
$iso3=$_POST["iso3"];
$numcode=$_POST["numcode"];
$phonecode=$_POST["phonecode"];
$status=$_POST["status"]??"active";


//arranging string according to the requirment
$iso =trim($iso);
$iso =strtoupper($iso);
$name =trim($name);
$name =strtoupper($name);
$nicename =trim($nicename);
$nicename =ucwords(strtolower($nicename));
$iso3 =trim($iso3);
$iso3 =strtoupper($iso3);
$numcode =trim($numcode);
$phonecode =trim($phonecode);
$status=trim($status);


//checks the  country existance
$country_exist=$db->get("countries","*",["name"=>$name]);
if($country_exist){
echo json_encode(["error"=>"country already exist"]);
exit;
}

//   validation
 if(!$iso ||!$name ||!$nicename ||!$iso3 ||!$numcode ||!$phonecode ||!$status){
    echo json_encode(["error"=>"All fields are required"]);
    exit;
 }


// decoding the token to extract user id from the token 
$decoded = JWT::decode($token, new Key($jwt_secret, 'HS256')); 
$user_id = $decoded->id;   // object access
 
$user=$db->get("users","*",["id"=>$user_id]);
if(!$user){
    echo json_encode(["error"=>"user identity did not found"]);
    exit;
}
if($user){
$result=$db->insert("countries",[
    "id"=>null,
    "iso"=>$iso,
    "name"=>$name,
    "nicename"=>$nicename,
    "iso3"=>$iso3,
    "numcode"=>$numcode,
    "phonecode"=>$phonecode,
    "status"=>$status
]);

if(!$result){
    echo json_encode(["error"=>"adding country error"]);
    exit;
}else{
    echo json_encode([
        "status"=>"Country added successfully",
        "data"=>$result
    ]);
    exit;
}
}


?>
