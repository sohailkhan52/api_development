<?php 

header("content-type: application/json");
include "db.php";


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


?>