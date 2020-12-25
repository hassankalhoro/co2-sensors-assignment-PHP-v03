<?php
class Database{
 
    // specify your own database credentials
    private $host = "localhost";
    private $db_name = "db_co2";
    private $username = "root";
    private $password = "";
    public $conn;
 
    // get the database connection
    public function getConnection(){
 
        $this->conn = null;
 
        try{
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
        }catch(PDOException $exception){
            echo "Connection error: " . $exception->getMessage();
        }
 
        return $this->conn;
    }
}
function d($arr,$bool=false)
{
	
	if(isset($arr))
	{
		print_r($arr);
		if($bool)
		{
			die;
		}
	}
	echo "</pre>";
}

function custom_parse_url()
{
	$URL = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	$URI = "$_SERVER[REQUEST_URI]";
	$parts = explode("/",$URI);
	$request_uri_tmp = array_slice($parts, -2, 2, true);
	$request_uri_values = array_values($request_uri_tmp);
	$GLOBALS["action_name"] = isset($request_uri_values[1])?$request_uri_values[1]:'';
	$GLOBALS["uuid"] = isset($request_uri_values[0])?$request_uri_values[0]:'';
	if(isset($GLOBALS["uuid"]) && $GLOBALS["uuid"]=="sensors" && $GLOBALS["action_name"]!="dashboard" && $GLOBALS["action_name"]!="sensor-mesurements")
	{
		$GLOBALS["action_name"] = $GLOBALS["uuid"];
		$GLOBALS["uuid"] = isset($request_uri_values[1])?$request_uri_values[1]:'';
	}
	
}
?>