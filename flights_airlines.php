<?php

// --------------------
// DB.PHP in clude database connection and $jwt_secret
// --------------------
include "db.php";
require "vendor/autoload.php";

use Medoo\Medoo;

header("content-type: application/json");

//-----------------
//JWT libraries
//-----------------
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

//-----------------
//GEt Bearer token 
//-----------------

$header=getallheaders();
$auth=$header['Authorization']??"";
if(!preg_match('/Bearer\s(\S+)/',$auth,$matches)){
    http_response_code(401);
    echo json_encode(['error'=>'token missing']);
    exit;

}$token=$matches[1];

//-----------------
//verifing JWT token
//-----------------

try {
    $decoded=JWT::decode($token,new key($jwt_secret,"HS256"));

} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['error'=>"token decoding error"]);
    exit;

}

// --------------------
// Routing logic
// --------------------

$method=$_SERVER['REQUEST_METHOD'];

switch ($method) {
        //--------------------
    //READ FLIGHT AIR LINES
    //--------------------
    case 'GET':

        $flights=$db->select("flights_airlines","*");

    // i am using foreach loop to print all the flights airlines

        foreach($flights as $flight){

                 // using $flightdata array to store the data of each currency in each iteration
          $flightdata[]=[
         $flight_id=$flight['id']??"",
         $flight_name=ucwords(trim($flight['name']??"")),
         $flight_code=$flight['code']??"",
         $flight_iata=strtoupper(trim($flight['iata']??"")),
         $flight_sign=strtoupper(trim($flight['sign']??"")),
         $flight_country=ucwords(trim($flight['country']??"")),
         $flight_status=$flight['status']??""
          ];

        }
        
        echo json_encode(['status'=>true,
        "data"=>$flightdata]);
        exit;
                break;
    //-----------------------
    // CREATE FLIGHTS AIRLINES
    //-----------------------    

     case "POST" :
                // data coming through post method
         $flight_name=ucwords(trim($_POST['name']??""));
         $flight_code=$_POST['code']??"";
         $flight_iata=strtoupper(trim($_POST['iata']??""));
         $flight_sign=strtoupper(trim($_POST['sign']??""));
         $flight_country=ucwords(trim($_POST['country']??""));
         $flight_status=$_POST['status']??1;
        

        if(!$flight_name|| !$flight_code || !$flight_iata|| !$flight_sign  ||!$flight_country){
         echo json_encode(["error"=>"All fields are required"]);
          exit;
       }

       //extracting the user id from verified jwt token
       $user_id = $decoded->id;

       // getting user data with the help of user id

      $user=$db->get("users","*",["id"=>$user_id]);
      if(!$user){
          echo json_encode(["error"=>"user identity did not found"]);
          exit;
      }
    //   if the user is valid then allow to add new currency 


        
            $result=$db->insert("flights_airlines",[
                "name"=>$flight_name,
                "code"=>$flight_code,
                "iata"=>$flight_iata,
                "sign"=>$flight_sign,
                "country"=>$flight_country,
                "status"=>1
            ]);
        // if the addition of new Currency fails then show the response
            if(!$result){
                http_response_code(401);
                echo json_encode(['error'=>"data insertion error"]);
                exit;
            }
        // if the addition of new Currency adds successfully then show the reponse

            echo json_encode(["success"=>"data added successfully"]);
            exit;

         
        break;


    //---------------------
    // update FLIGHTS AIRLINES
    //---------------------

    case "PUT":

        $input=json_decode(file_get_contents("php://input"),true);

         $id=$input['id']?? NULL;
        // it checks the id  and find the existance of the currency 
         $airlines=$db->get("flights_airlines","*",['id'=>$id]);
         if(!$airlines){
            echo json_encode(['error'=>"flight id not found"]);
            exit;
         }
        //   i ma using $FLIGHTdata array which stores the input fields  which can be used to updated currency data in countries table 
         $flightdata=[];
         if(isset($input['name'])){
         $flightdata['name']=ucwords(trim($input['name']??""));}

         if(isset($input['code'])){
          $flightdata['code']=$input['code']??"";}

         if(isset($input['iata'])){
         $flightdata['iata']=strtoupper(trim($input['iata']??""));}
         
         if(isset($input['sign'])){
         $flightdata['sign']=strtoupper(trim($input['sign']??""));}
         
         if(isset($input['country'])){
         $flightdata['country']=ucwords(trim($input['country']??""));}
         
         if(isset($input['status'])){
         $flightdata['status']=1;}



        

          //   medoo update query 

            $result=$db->update("flights_airlines",$flightdata,['id'=>$id]);

            if(!$result){
                http_response_code(401);
                echo json_encode(['error'=>"updating error"]);
                exit;
            }
            echo json_encode(['seccess'=>"data updated successfully"]);
                exit;
         
        # code...
        break;
         //---------------------
         // Delete currency
         //---------------------

    case "DELETE":
        $input=json_decode(file_get_contents("php://input"),true);
        $id=$input['id'];
  
            $result=$db->delete("flights_airlines",['id'=>$id]);
            echo json_encode(["success"=>"flights airlines id deleted successfully"]);
            exit;
       
         echo json_encode(["error"=>"flights airlines id deleting error "]);
            exit;
        break;
    default:
        # code...
        break;
}






?>
