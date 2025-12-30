<?php

header("Content-Type: application/json");

// --------------------
// JWT libraries
// --------------------
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
// --------------------
// Get Bearer token
// --------------------
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
$headers = getallheaders();
$auth = $headers['Authorization'] ?? '';
if (!preg_match('/Bearer\s(\S+)/', $auth, $matches)) {
    http_response_code(404);
    echo json_encode([
        "status" => false,
        "error" => "Token missing"
    ]);
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

    echo json_encode([
        "status" => false,
        "error" => "Invalid token"
    ]);
    exit;
}





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
    //--------------------
    //READ CURRENCY
    //--------------------

    case "GET";
        $currencies = $db->select("currencies", "*");
        if (!$currencies) {
            http_response_code(404);
            echo json_encode([
                "status" => false,
                "error" => "Currencies does not exist"
            ]);
            exit;
        }

        // i am using foreach loop to print all the currencies 
        $result = [];
        foreach ($currencies as $currency) {
            // using $rsult array to store the data of each currency in each iteration



            $result[] = [
                "currency id" => $currency['id'],
                "currency name" => $currency['name'],
                "currency country" => $currency['country'],
                "currency default" => $currency['default'] ?? 1,
                "currency status" => $currency['status'],
                "currency rate" => $currency['rate']
            ];
        }
        http_response_code(200);
        echo json_encode([
            "status" => true,
            "count" => count($result),
            "data" => $result
        ]);
        exit;
        break;


    //-----------------------
    // CREATE CURRENCY
    //-----------------------
    case "POST":
        // data coming through post method

        $name = strtoupper(trim($_POST["name"]));
        $country = strtoupper(trim($_POST["country"]));
        $status = trim($_POST["status"]);
        $rate = trim($_POST["rate"]);
        //-----------------------------------------
        //    APPLING VALIDATION TO THE INPUT FIELDS 
        //------------------------------------------
        if (!$name || !$country || !$status || !$rate) {
            http_response_code(404);
            echo json_encode([
                "status" => false,
                "error" => "All fields are required"
            ]);
            exit;
        }

        //-----------------------------------------
        //  CHECKING THE CURRENCY IN DATABASE 
        //------------------------------------------
        $check_currency = $db->get("currencies", "*", ["country" => $country]);
        if ($check_currency) {
            http_response_code(404);
            echo json_encode([
                "status" => false,
                "error" => "$name already exist"
            ]);
            exit;
        } else {
            $result = $db->insert("currencies", [
                "name" => $name,
                "country" => $country,
                "status" => $status,
                "rate" => $rate,
                "default" => $default ?? ""
            ]);
            // if the addition of new Currency fails then show the response
            if (!$result) {
                http_response_code(404);
                echo json_encode([
                    "status" => false,
                    "error" => "adding currency error"
                ]);
                exit;
            } else {
                // if the addition of new Currency adds successfully then show the reponse
                http_response_code();
                echo json_encode([
                    "status" => true,
                    "success" => "Currency added successfully",
                    "data" => $result
                ]);
                exit;
            }

        }



        break;

    //---------------------
    // update currency
    //---------------------
    case "PUT":
        $input = json_decode(file_get_contents("php://input"), true);
        $id = $input['id'] ?? null;

        // it checks the id  and find the existance of the currency 
        $currency = $db->get("currencies", "*", ['id' => $id]);

        if (!$currency) {
            http_response_code(404);
            echo json_encode([
                "status" => false,
                "error" => "invalid currency id"
            ]);
            exit;
        }
        //   i ma using $updatedata array which stores the input fields  which can be used to updated currency data in countries table 
        $updatedata = [];
        if (isset($input['name'])) {
            $updatedata['name'] = strtoupper(trim($input['name']));
        }
        if (isset($input['country'])) {
            $updatedata['country'] = strtoupper(trim($input['country']));
        }
        if (isset($input['status'])) {
            $updatedata['status'] = trim($input['status']);
        }
        if (isset($input['rate'])) {
            $updatedata['rate'] = trim($input['rate']);
        }


        //   medoo update query 

        $result = $db->update("currencies", $updatedata, ['id' => $id]);
        if (!$result) {
            http_response_code(404);
            echo json_encode([
                "status" => false,
                "error" => "country updating error"
            ]);
            exit;
        }
        http_response_code(200);
        echo json_encode([
            "status" => true,
            "success" => "country updated successfull",
            "data" => $result
        ]);

        break;

    //---------------------
    // Delete currency
    //---------------------


    case "DELETE";

        $input = json_decode(file_get_contents("php://input"), true);
        $currency_id = $input['id'] ?? null;
        //  taking user id from the decoded token 

        if (!$currency_id) {

            http_response_code(404);
            echo json_encode(["error" => "require input id"]);
            exit;
        }

        $currency = $db->get("currencies", 'country', ['id' => $currency_id]);
        $result = $db->delete("currencies", ['id' => $currency_id]);
        if ($currency) {
            http_response_code(200);
            echo json_encode([
                "status" => true,

                "success" => "$currency currency deleted successfully"
            ]);
            exit;
        }


        http_response_code(404);
        echo json_encode([
            "status" => false,
            "error" => " currency deleting error occure"
        ]);
        exit;


        break;
    default:
        http_response_code(405);
        echo json_encode([
            "status" => false,
            "error" => "Invalid request method"
        ]);

}
