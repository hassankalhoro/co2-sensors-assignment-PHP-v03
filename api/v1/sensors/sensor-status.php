<?php
// include database and object files
include_once '../config/database.php';
include_once '../objects/sensor.php';
 
// get database connection
$database = new Database();
$db = $database->getConnection();
$sensor = new Sensor($db);
 
// set user property values

$sensor->sense_id = isset($_REQUEST['uuid'])?$_REQUEST['uuid']:'';
 
$stmt = $sensor->status();
if($stmt->rowCount() > 0){
    // get retrieved row
	$status="";
	while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
		if(isset($row["co2"]) && $row["co2"]>=2000)
		{
			$status="OK";
		}
	}
    // create array
    $sensor_arr=array(
        "status" => $status
    );
}
else{
    $sensor_arr=array(
        "status" => false,
        "message" => "Data not available!",
    );
}
print_r(json_encode($sensor_arr));
?>