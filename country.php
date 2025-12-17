<?php
include "db.php";
require 'vendor/autoload.php';
use Medoo\Medoo;

header("Content-Type: application/json");

// --------------------
// JWT libraries
// --------------------
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
// --------------------
// Get Bearer token
// --------------------
$headers = getallheaders();
$auth = $headers['Authorization'] ?? '';
if (!preg_match('/Bearer\s(\S+)/', $auth, $matches)) {
    http_response_code(401);
    echo json_encode(["error" => "Token missing"]);
    exit;
}
$token = $matches[1];
// --------------------
// Verify JWT
// --------------------
try {
    $decoded = JWT::decode($token, new Key($jwt_secret, 'HS256'));
    

} catch (Exception $e) {
    http_response_code(401);
    
    echo json_encode(["error" => "Invalid token"]);
    exit;
}

// --------------------
// Routing logic
// --------------------
$method = $_SERVER['REQUEST_METHOD'];

switch($method) {

     //---------------------
     // Create country
     //---------------------
    case 'GET': // Read countries

     $country=$db->select("countries","*");

      foreach($country as $country){
        $result[]=[
      $country_id=$country['id'],
      $country_name=$country['nicename'],
      $country_phone_code=$country['phonecode']];
    
     }

     echo json_encode([
        "status"=>true,
        "count"=>count($result),
        "data"=>$result
        ]);


        break;
    //---------------------
    // Create country
    //---------------------
    case 'POST': 
       
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




        break;
    //---------------------
    // update country
    //---------------------
    case 'PUT': // Update country
       
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


        

        break;
    //---------------------
    // Delete country
    //---------------------
    case 'DELETE': // Delete country
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

        break;

    default:
        http_response_code(405);
        echo json_encode(["error"=>"Invalid request method"]);
}
