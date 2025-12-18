<?php 
// --------------------
// DB.PHP in clude database connection and $jwt_secret
// --------------------
include "db.php";
require "vendor/autoload.php";
use Medoo\Medoo;

header("content-type: application/json");

// --------------------
// JWT libraries
// --------------------
use Firebase\JWT\JWT;
use Firebase\JWT\Key;


// --------------------
// Get Bearer token
// --------------------
$header=getallheaders();
$auth=$header['Authorization'];
if(!preg_match("/Bearer\s(\S+)/",$auth,$matches)){
    http_response_code(401);
    echo json_encode(["error"=>"token missing"]);
    exit;
}

$token=$matches[1];


// --------------------
// Verify JWT
// --------------------
 try {
    $decoded = JWT::decode($token, new Key($jwt_secret, 'HS256'));
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(["error" => "Invalid or expired token"]);
    exit;
}

$user_id=$decoded->id;


// --------------------
// End POINT
// --------------------
        
$end_point="http://localhost/project_medoo/languges.php";


// --------------------
// Routing logic
// --------------------
$method=$_SERVER['REQUEST_METHOD'];

switch ($method) {

 // --------------------
// READ LANGUAGES
// --------------------
    case 'GET':
        

        $results=$db->select("languages","*");
        foreach ($results as $result) {
        
            $language[]=[

                $lang_id=$result['id'],
                $lang_status=$result['status'],
                $lang_default=$result['default'],
                $lang_name=ucwords(trim($result['name'])),
                $lang_type=strtoupper(trim($result['type'])),
                $lang_country=strtoupper(trim($result['country'])),
                $lang_lang_code=strtolower(trim($result['lang_code']))
            ];
            
        }
      if($language){
       echo json_encode([
        "status"=>true,
        "data"=>$language
       ]);
       
        exit;
      }else{

        http_response_code(401);
        echo json_encode(["error"=>"Data getting error"]);
        exit;
      }
       break;


      // --------------------
     // CREATE LANGUAGES
     // --------------------

    case 'POST':
     
      // data coming through post method and properly arranged according to the requirement
         $status=$_POST['status']??1;
          $default=$_POST['default']??null;
         $name=ucwords(trim($_POST['name']??null));
         $type=strtoupper(trim($_POST['type']??null));
         $country=strtoupper(trim($_POST['country']??null));
          $lang_code=strtolower(trim($_POST['lang_code']??null));
        
           //---------------
           //INPUT VALIDATION
           //----------------
          if ($name === null || $type === null || $country === null || $lang_code === null) {

            {
                echo json_encode(['error'=>"all fields are required"]);
                exit;
            }
          }
            //checks the  language existance

          $language_exist=$db->get("languages","*",["name"=>$name]);
          

          if($language_exist){
            echo json_encode(['error'=>"country already exist"]);
            exit;
          }

               //extracting the user id from verified jwt token
      $user_id = $decoded->id;   
      
      //getting user data with the help of user id

      $user=$db->get("users","*",["id"=>$user_id]);
      if(!$user){
          echo json_encode(["error"=>"user identity did not found"]);
          exit;
      }

      //if user is valid then allow to  add new country 
      if($user){
          $result=$db->insert("languages",[
            "status"=>$status,
            "default"=>$default,
            "name"=>$name,
            "type"=>$type,
            "country"=>$country,
            "lang_code"=>$lang_code
          ]);

                // if the addition of new country fails then show the response
          if(!$result){
            http_response_code(401);
            echo json_encode(['error'=>"error occure while adding new language"]);
            exit;
            
          }
          else{

             // if the addition of new country adds successfully then show the reponse
            echo json_encode(['success'=>"new language added successfully"]);
            exit;

          }
          }
        break;

           //---------------------
           // update country
          //---------------------
    case 'PUT':

        $input=json_decode(file_get_contents("php://input"),true);
        $lang_id=$input['id']??null;

         //   i ma using $updatedata array which stores the input fields  which can be used to updated country data in countries table 

        $languagedata=[];


        if(isset($input['status'])){
         $languagedata['status']=$input['status']??1;
        }
        if(isset($input['default'])){
         $languagedata['default']=$input['default']??null;
        }
        if(isset($input['name'])){
         $languagedata['name']=ucwords(trim($input['name']??null));
        }
        if(isset($input['type'])){
         $languagedata['type']=strtoupper(trim($input['type']??null));
        }
        if(isset($input['country'])){
         $languagedata['country']=strtoupper(trim($input['country']??null));
        }
        if(isset($input['lang_code'])){
         $languagedata['lang_code']=strtolower(trim($input['lang_code']??null));
        }
             //extracting the user id from verified jwt token
      $user_id = $decoded->id;   
      
      //getting user data with the help of user id

      $user=$db->get("users","*",["id"=>$user_id]);
      if(!$user){
          echo json_encode(["error"=>"user identity did not found"]);
          exit;
      }

      //if user is valid then allow to  add new country 
      if($user){
     //using medoo 
        $result=$db->update("languages",$languagedata,['id'=>$lang_id]);

        if(!$result){
            http_response_code(401);
            echo json_encode(['error'=>"updating language file unsuccessful"]);
            exit;
        }else{
            echo json_encode(['success'=>"upadating language successfully"]);
            exit;
        }
        }
        break;

    //---------------------
    // Delete country
    //---------------------
    case 'DELETE':
        $input=json_decode(file_get_contents("php://input"),true);
        $id=$input['id']??null;
     //extracting the user id from verified jwt token
      $user_id = $decoded->id;   
      
      //getting user data with the help of user id

      $user=$db->get("users","*",["id"=>$user_id]);
      if(!$user){
          echo json_encode(["error"=>"user identity did not found"]);
          exit;
      }

      //if user is valid then allow to  add new country 
      if($user){
        $result=$db->delete("languages",['id'=>$id]);
        if(!$result){
            http_response_code(401);
            echo json_encode(["error"=>"deleting language unsuccessful"]);
            exit;
        }else{
            echo json_encode(["success"=>"deleting language successfully"]);
            exit;
        }
        }
        break;
    
         default:
        http_response_code(405);
        echo json_encode(["error"=>"Invalid request method"]);
}
?>