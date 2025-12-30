<?php

header("content-type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
// --------------------
// JWT libraries
// --------------------
use Firebase\JWT\JWT;
use Firebase\JWT\Key;


// --------------------
// Get Bearer token
// --------------------
$header = getallheaders();
$auth = $header['Authorization'];
if (!preg_match("/Bearer\s(\S+)/", $auth, $matches)) {
  http_response_code(401);
  echo json_encode(["error" => "token missing"]);
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
  echo json_encode(["error" => "Invalid or expired token"]);
  exit;
}

$user_id = $decoded->id;



// --------------------
// Routing logic
// --------------------
$method = $_SERVER['REQUEST_METHOD'];
if (!$method) {
  http_response_code(404);
  echo json_encode([
    "status" => false,
    "error" => "method missing"
  ]);
  exit;
}
switch ($method) {

  // --------------------
// READ LANGUAGES
// --------------------
  case 'GET':


    $results = $db->select("languages", "*");
    if (!$results) {
      http_response_code(404);
      echo json_encode([
        "status" => false,
        "error" => "results does not exist"
      ]);
      exit;
    }

    $language = [];

    // i am using foreach loop to print all the results
    foreach ($results as $result) {

      $language[] = [

        "lang_id" => $result['id'],
        "lang_status" => $result['status'],
        "lang_default" => $result['default'],
        "lang_name" => ucwords(trim($result['name'])),
        "lang_type" => strtoupper(trim($result['type'])),
        "lang_country" => strtoupper(trim($result['country'])),
        "lang_lang_code" => strtolower(trim($result['lang_code']))
      ];

    }
    if ($language) {
      echo json_encode([
        "status" => true,
        "data" => $language
      ]);

      exit;
    } else {

      http_response_code(401);
      echo json_encode(["error" => "Data getting error"]);
      exit;
    }
    break;


  // --------------------
  // CREATE LANGUAGES
  // --------------------

  case 'POST':

    // data coming through post method and properly arranged according to the requirement
    $status = $_POST['status'] ?? 1;
    $default = $_POST['default'] ?? null;
    $name = ucwords(trim($_POST['name'] ?? null));
    $type = strtoupper(trim($_POST['type'] ?? null));
    $country = strtoupper(trim($_POST['country'] ?? null));
    $lang_code = strtolower(trim($_POST['lang_code'] ?? null));

    //---------------
    //INPUT VALIDATION
    //----------------
    if ($name === null || $type === null || $country === null || $lang_code === null) {
      http_response_code(404);
      echo json_encode([
        "status" => false,
        "error" => "All fields are required"
      ]);
      exit;

    }
    //checks the  language existance

    $language_exist = $db->get("languages", "*", ["name" => $name]);


    if ($language_exist) {
      http_response_code(404);
      echo json_encode([
        "status" => false,
        "error" => "$name already exist"
      ]);
      exit;
    }


    $result = $db->insert("languages", [
      "status" => $status,
      "default" => $default,
      "name" => $name,
      "type" => $type,
      "country" => $country,
      "lang_code" => $lang_code
    ]);

    // if the addition of new country fails then show the response
    if (!$result) {
      http_response_code(404);
      echo json_encode([
        "status" => false,
        "error" => "adding language error"
      ]);
      exit;

    } else {

      // if the addition of new country adds successfully then show the reponse
      http_response_code();
      echo json_encode([
        "status" => true,
        "success" => "Language added successfully",
        "data" => $result
      ]);
      exit;

    }

    break;

  //---------------------
  // update country
  //---------------------
  case 'PUT':

    $input = json_decode(file_get_contents("php://input"), true);
    $lang_id = $input['id'] ?? null;


    // it checks the id  and find the existance of the currency 
    $language = $db->get("currencies", "*", ['id' => $lang_id]);

    if (!$language) {
      http_response_code(404);
      echo json_encode([
        "status" => false,
        "error" => "invalid language id"
      ]);
      exit;
    }

    //   i ma using $laguagedata array which stores the input fields  which can be used to updated country data in countries table 

    $languagedata = [];


    if (isset($input['status'])) {
      $languagedata['status'] = $input['status'] ?? 1;
    }
    if (isset($input['default'])) {
      $languagedata['default'] = $input['default'] ?? null;
    }
    if (isset($input['name'])) {
      $languagedata['name'] = ucwords(trim($input['name'] ?? null));
    }
    if (isset($input['type'])) {
      $languagedata['type'] = strtoupper(trim($input['type'] ?? null));
    }
    if (isset($input['country'])) {
      $languagedata['country'] = strtoupper(trim($input['country'] ?? null));
    }
    if (isset($input['lang_code'])) {
      $languagedata['lang_code'] = strtolower(trim($input['lang_code'] ?? null));
    }

    //using medoo 
    $result = $db->update("languages", $languagedata, ['id' => $lang_id]);

    if (!$result) {
      http_response_code(401);
      echo json_encode(['error' => "updating language file unsuccessful"]);
      exit;
    } else {
      echo json_encode(['success' => "upadating language successfully"]);
      exit;
    }

    break;

  //---------------------
  // Delete country
  //---------------------
  case 'DELETE':
    $input = json_decode(file_get_contents("php://input"), true);
    $id = $input['id'] ?? null;


    //  taking user id from the decoded token 

    if (!$id) {

      http_response_code(404);
      echo json_encode(["error" => "require input id"]);
      exit;
    }

    $language = $db->get("language", 'country', ['id' => $id]);

    $result = $db->delete("languages", ['id' => $id]);
    if ($result) {
      http_response_code(200);
      echo json_encode([
        "status" => true,

        "success" => "$language language deleted successfully"
      ]);
      exit;
    }

    break;

  default:
    http_response_code(405);
    echo json_encode(["error" => "Invalid request method"]);
}
?>
