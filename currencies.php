<?php

// --------------------
// DB.PHP in clude database connection and $jwt_secret
// --------------------
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
// End POINT
// --------------------
        
$end_point="http://localhost/project_medoo/currencies.php";

// --------------------
// Routing logic
// --------------------
$method = $_SERVER['REQUEST_METHOD'];


switch($method){
    //--------------------
    //READ CURRENCY
    //--------------------

    case "GET";
    $currencies =$db->select("currencies","*");

    // i am using foreach loop to print all the currencies 

    foreach($currencies as $currency){
     // using $rsult array to store the data of each currency in each iteration

     

        $result[]=[
            $currency_id=$currency['id'],
            $currency_name=$currency['name'],
            $currency_country=$currency['country'],
            $currency_default=$currency['default']?? 1,
            $currency_status=$currency['status'],
            $currency_rate=$currency['rate']
        ];
        }

        echo json_encode([
            "status"=>true,
            "count"=>count($result),
            "data"=>$result
        ]);
        break;


    //-----------------------
    // CREATE CURRENCY
    //-----------------------
    case "POST":
        // data coming through post method

       $name=strtoupper(trim($_POST["name"]));
       $country=strtoupper(trim($_POST["country"]));
       $status=trim($_POST["status"]);
       $rate=trim($_POST["rate"]);



       if(!$name|| !$country || !$status || !$rate){
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

    if ($user){
        $result=$db->insert("currencies",[
            "name"=>$name,
            "country"=>$country,
            "status"=>$status,
            "rate"=>$rate,
            "default"=>$default??""
        ]);
        // if the addition of new Currency fails then show the response
        if(!$result){
          echo json_encode(["error"=>"adding currency error"]);
          exit;
      }else{
        // if the addition of new Currency adds successfully then show the reponse
          echo json_encode([
              "status"=>"Currency added successfully",
              "data"=>$result
          ]);
          exit;
      }
    }

    break;

    //---------------------
    // update currency
    //---------------------
    case "PUT":
        $input=json_decode(file_get_contents("php://input"),true);
        $id =$input['id']??null;
       
        // it checks the id  and find the existance of the currency 
        $currency=$db->get("currencies","*",['id'=>$id]);

        if(!$currency){
            echo json_encode(["error"=>"invalid currency id"]);
            exit;
        }
         //   i ma using $updatedata array which stores the input fields  which can be used to updated currency data in countries table 
         $updatedata=[];
         if(isset($input['name'])){
            $updatedata['name']=strtoupper(trim($input['name']));
         }
         if(isset($input['country'])){
            $updatedata['country']=strtoupper(trim($input['country']));
         }
         if(isset($input['status'])){
            $updatedata['status']=trim($input['status']);
         }
         if(isset($input['rate'])){
            $updatedata['rate']=trim($input['rate']);
         }


        //   medoo update query 

        $result=$db->update("currencies",$updatedata,['id'=>$id]);
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
    // Delete currency
    //---------------------


    case "DELETE";
  
     $input = json_decode(file_get_contents("php://input"),true);
     $currency_id =$input['id']??null;
     //  taking user id from the decoded token 


     $user_id=$decoded->id;

    // check that the deleting country is a proper user or not 
     $user=$db->get("users","*",['id'=>$user_id]);
     if($user){
        $result=$db->delete("currencies",['id'=>$currency_id]);
        if(!$result){
        echo json_encode(["error"=>" currency deleting error occure"]);
     exit;
         }

         echo json_encode([
            "success"=>"currency deleted successfully"
         ]);
         exit;
     }
 
     break;
     default:
     http_response_code(405);
     echo json_encode(["error"=>"Invalid request method"]);

}