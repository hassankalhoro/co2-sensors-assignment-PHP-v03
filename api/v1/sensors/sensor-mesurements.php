<?php
// include database and object files
include_once '../config/database.php';
include_once '../objects/sensor.php';
 
// get database connection
$database = new Database();
$db = $database->getConnection();
$sensor = new Sensor($db);
 
// set user property values
$date=date_create();
$dormated_date = date_format($date,"Y-m-d\TH:i:s");
if(isset($_REQUEST["ccount"]) && $_REQUEST["ccount"]>0)
{
	$ccount = intval($_REQUEST["ccount"]);
	if($ccount<=0)
	{
		$ccount=10;
	}
	$sensor_data="";;
	for($i=1;$i<=$ccount;$i++)
	{
		$comma=",";
		if($i==$ccount)
		{
			$comma="";
		}
		$sensor_data.="(".rand(3000,1).",'".$dormated_date."',".$i.")".$comma;
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
else
{
	$date=date_create();
$dormated_date = date_format($date,"Y-m-d\TH:i:s");
$sensor->co2 = isset($_REQUEST['co2'])?$_REQUEST['co2']:'';
$sensor->created = isset($_REQUEST['time'])?$dormated_date:'';
$sensor->sense_id = rand(10000000,1);
 
// create the user
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
print_r(json_encode($sensor_arr));
}

?>