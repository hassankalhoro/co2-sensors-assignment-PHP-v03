<?php 
/*
global variable $uuid is definde for sensor_id  and $action_name for what action should be called like mesurements and alert api's
*/
// include database and sensor files
global $uuid,$action_name;
$GLOBALS["uuid"]="";
$GLOBALS["action_name"]="";
include_once 'api/v1/config/database.php';/* db connection object*/
include_once 'api/v1/objects/sensor.php';/* sensor class  object*/

custom_parse_url();/* parse url to get query string information to populate */
// get database connection
$database = new Database();

$db = $database->getConnection();
$sensor = new Sensor($db);
$sensor->threshold=3;
if($GLOBALS["action_name"]=="mesurements" && $GLOBALS["uuid"]!="")
{
	/* Collect sensor mesurements api*/
	// Takes raw data from the request
	$json = file_get_contents('php://input');

	// Converts it into a PHP object
	$post_data = json_decode($json);
	
	$date=date_create();
	$dormated_date = date_format($date,"Y-m-d\TH:i:s");
	$sensor->co2 = isset($post_data->co2)?$post_data->co2:'';
	$sensor->created = isset($post_data->time)?$post_data->time:$dormated_date;
	$sensor->sense_id = $uuid;
	 
	// store sensor request information
	if($sensor->collect()){
		$sensor_arr=array(
			"status" => true,
			"message" => "Successfully stored!",
			"id" => $sensor->id,
			"co2" => $sensor->co2
		);
	}
	else{
		$sensor_arr=array(
			"status" => false,
			"message" => "Failure!"
		);
	}
	
	echo json_encode($sensor_arr);
}
elseif($GLOBALS["action_name"]=="sensors" && $GLOBALS["uuid"]!="")
{

	/* get sensor status /api/v1/sensors/{uuid} */	
	$sensor->sense_id = isset($GLOBALS['uuid'])?$GLOBALS['uuid']:'';
 
	$stmt = $sensor->status();
	if($stmt->rowCount() > 0){
		// get retrieved row
		$status="";
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			
			if(isset($row["sensor_status"]))
			{
				$status=$row["sensor_status"];
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
	
	echo json_encode($sensor_arr);
}
elseif($GLOBALS["action_name"]=="metrics" && $GLOBALS["uuid"]!="")
{
	/* get metrics information  /api/v1/sensors/{uuid}/metrics */
	$sensor->sense_id = isset($GLOBALS['uuid'])?$GLOBALS['uuid']:'';
 
	$stmt = $sensor->metrics();
	if($stmt->rowCount() > 0){
		// get retrieved row
		$status="";
		$maxLast30Days="";
		$avgLast30Days="";
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			
			if(isset($row["maxLast30Days"]))
			{
				$maxLast30Days=$row["maxLast30Days"];
			}
			if(isset($row["avgLast30Days"]))
			{
				$avgLast30Days=$row["avgLast30Days"];
			}
		}
		// create array
		$sensor_arr=array(
			"maxLast30Days" => $maxLast30Days,
			"avgLast30Days" => $avgLast30Days
		);
	}
	else{
		$sensor_arr=array(
			"status" => false,
			"message" => "Data not available!",
		);
	}	
	
	echo json_encode($sensor_arr);
}
elseif($GLOBALS["action_name"]=="alerts" && $GLOBALS["uuid"]!="")
{
	/* list all alert information for specific sensor by uuid /api/v1/sensors/{uuid}/alerts */
	$sensor->sense_id = isset($GLOBALS['uuid'])?$GLOBALS['uuid']:'';
 
	$stmt = $sensor->alerts();
	if($stmt->rowCount() > 0){
		// get retrieved row
		$status="";
		$startTime="";
		$endTime="";
		$count=0;
		$alerts=array();
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			
			if($count==0)
			{
				$startTime = $row["created"];// start time
				
			}
			$endTime = $row["created"];// end time
			$count++;
			$alerts['mesurement'.$count] = $row["co2"];// mesurement1,2,3...
			
			
		}
		// create array
		$sensor_arr=array(
			"startTime" => $startTime,
			"endTime" => $endTime,
			"mesurement" => $alerts
		);
	}
	else{
		$sensor_arr=array(
			"status" => false,
			"message" => "Data not available!",
		);
	}	
	
	echo json_encode($sensor_arr);
}
elseif($GLOBALS["action_name"]=="dashboard")
{
	/* for population of data for testing perpose */
	include_once 'api/v1/sensors/dashboard.php';
}
elseif($GLOBALS["action_name"]=="sensor-mesurements")
{
	/* storing sensor information */
	$date=date_create();
	$dormated_date = date_format($date,"Y-m-d\TH:i:s");
	if(isset($_REQUEST["cuuid"]) && $_REQUEST["cuuid"]>0)
	{
		$sensor->sense_id = $_REQUEST["cuuid"];
		$stmt = $sensor->status();
		if($stmt->rowCount() > 0){
			// get retrieved row
			$status="";
			while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				
				if(isset($row["sensor_status"]))
				{
					$status=$row["sensor_status"];
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
		print_r(json_encode($sensor_arr));die;
	}
	if(isset($_REQUEST["ccount"]) && $_REQUEST["ccount"]>0)
	{
		$ccount = intval($_REQUEST["ccount"]);
		if($ccount<=0)
		{
			$ccount=10;// default count is 10 for sample data 
		}
		$sensor_data="";;
		for($i=1;$i<=$ccount;$i++)
		{
			$comma=",";
			if($i==$ccount)
			{
				$comma="";
			}
			
			$responseAlert = $sensor->get_last_three_sensors_alert($i);
			$responseOk = $sensor->get_last_three_sensors_ok($i);
		
			$rand_val = rand(5000,1);
			if($rand_val>=2000)
			{
				$sensor_status = "WARN";
			}
			else
			{
				$sensor_status="OK";
			}
			
			if($responseOk>=$sensor->threshold && $rand_val<2000)
			{
				//When the sensor reaches to status ALERT it stays in this state until it receives 3 consecutive measurements lower than 2000; then it moves to OK
				$sensor_status="OK";
			}
			elseif($responseAlert>=$sensor->threshold)
			{
				//• When the sensor reaches to status ALERT an alert should be stored
				$sensor_status="ALERT";
			}
			
			$sensor_data.="(".$rand_val.",'".$dormated_date."',".$i.",'".$sensor_status."')".$comma;// make a single query for multiple records
		}
		if($sensor->dump($sensor_data))
		{
			$response=array(
			"status" => true,
			"message" => "Successfully stored!"
			);
		}
		else
		{
			$response=array(
			"status" => false,
			"message" => "failure!"
			);
		}
		print_r(json_encode($response));
		
	}					
}
?>