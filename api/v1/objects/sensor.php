<?php
/*Create at 12-24-2020
By Muhammad Hassan
Sensor object 
*/
class Sensor{
 
    // database connection and table name
    private $conn;
    private $table_name = "sensors";
 
    // object properties
    public $id;
    public $co2;
    public $created;
    public $sense_id;
    public $sensor_status;
    public $threshold ;
 
    // constructor with $db as database connection
    public function __construct($db){
        $this->conn = $db;
    }
	
	/* last three sensors information for alert status */
	function get_last_three_sensors_alert($uuid=0)
	{
		 $query = "SELECT
                    `id`, `co2`, `sense_id`, `created`,sensor_status
                FROM
                    " . $this->table_name . " 
                WHERE
                    sense_id='".$uuid."'  ORDER BY id DESC LIMIT ".$this->threshold;
        // prepare query statement
        $stmt = $this->conn->prepare($query);
        // execute query
        $stmt->execute();
		$status=0;
	   while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			// if co2 ppm is greate than 2000 mark is queue for status OK ALERT or WARN
			if(isset($row["co2"]) && $row["co2"]>=2000)
			{
				$status++;
			}
			
		}
		return $status;
	}
	
	
	/* last three sensors information count for ok status */
	function get_last_three_sensors_ok($uuid=0)
	{
		 $query = "SELECT
                    `id`, `co2`, `sense_id`, `created`,sensor_status
                FROM
                    " . $this->table_name . " 
                WHERE
                    sense_id='".$uuid."'  ORDER BY id DESC LIMIT ".$this->threshold;
        // prepare query statement
        $stmt = $this->conn->prepare($query);
        // execute query
        $stmt->execute();
		$status=0;
	   while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			// count status for ok if it reaches to max threshold 
			if(isset($row["co2"]) && $row["co2"]<2000)
			{
				$status++;
			}
			
		}
		return $status;
	}
	
	
	
    /* Collect mesurements for givin requirments */ 
    function collect(){
    
        $query = "INSERT INTO
                    " . $this->table_name . "
                SET
                    co2=:co2, created=:created, sense_id=:sense_id, sensor_status=:sensor_status";
    
        // prepare query
        $stmt = $this->conn->prepare($query);
    
        // sanitize
        $this->co2=htmlspecialchars(strip_tags($this->co2));
        $this->created=htmlspecialchars(strip_tags($this->created));
        $this->sense_id=htmlspecialchars(strip_tags($this->sense_id));
		$responseAlert = $this->get_last_three_sensors_alert($this->sense_id);
		$responseOk = $this->get_last_three_sensors_ok($this->sense_id);
		if($this->co2<=2000)
		{
			
			if($responseAlert<=0)
			{
				$this->sensor_status="OK";
			}
			else
			{
				//If the service receives 3 or more consecutive measurements higher than 2000 the sensor status should be set to ALERT
				$this->sensor_status="ALERT";
			}
			
		}
		elseif($this->co2>2000)
		{
			//• If the CO2 level exceeds 2000 ppm the sensor status should be set to WARN
			$this->sensor_status="WARN";
		}
		
		
		if($responseOk>=$this->threshold && $this->co2<2000)
		{
			//When the sensor reaches to status ALERT it stays in this state until it receives 3 consecutive measurements lower than 2000; then it moves to OK
			$this->sensor_status="OK";
		}
		elseif($responseAlert>=$this->threshold)
		{
			//• When the sensor reaches to status ALERT an alert should be stored
			$this->sensor_status="ALERT";
		}
    
        // bind values
        $stmt->bindParam(":co2", $this->co2);
        $stmt->bindParam(":created", $this->created);
        $stmt->bindParam(":sense_id", $this->sense_id);
        $stmt->bindParam(":sensor_status", $this->sensor_status);
    
        // execute query
        if($stmt->execute()){
            $this->id = $this->conn->lastInsertId();
            return true;
        }
    
        return false;
        
    }
	
	
    /* get status of sensor by uuid*/
    function status(){
        // select all query
        $query = "SELECT
                    `id`, `co2`, `sense_id`, `created`,sensor_status
                FROM
                    " . $this->table_name . " 
                WHERE
                    sense_id='".$this->sense_id."' ORDER BY ID DESC LIMIT 1";
        // prepare query statement
        $stmt = $this->conn->prepare($query);
        // execute query
        $stmt->execute();
        return $stmt;
    }
	
	
	/*
		 - Average CO2 level for the last 30 days
		 - Maximum CO2 Level in the last 30 days
	*/
		 
	function metrics(){
        
        $query = "SELECT
                    MAX(co2) as maxLast30Days,AVG(co2) as avgLast30Days
                FROM
                    " . $this->table_name . " 
                WHERE
                    sense_id='".$this->sense_id."' AND created BETWEEN NOW() - INTERVAL 30 DAY AND NOW()";
        // prepare query statement
        $stmt = $this->conn->prepare($query);
        // execute query
        $stmt->execute();
        return $stmt;
    }
	
	 /* list all the alerts for a given sensor */
	function alerts(){
       
        $query = "SELECT
                    *
                FROM
                    " . $this->table_name . " 
                WHERE
                    sense_id='".$this->sense_id."' AND sensor_status='ALERT'  ORDER BY id";
        // prepare query statement
        $stmt = $this->conn->prepare($query);
        // execute query
        $stmt->execute();
        return $stmt;
    }
	
	
	/*dump testing values */
    function dump($values=""){
        $query = "INSERT INTO " . $this->table_name . " (co2, created, sense_id,sensor_status)
VALUES ".$values.";";
  
        // prepare query
        $stmt = $this->conn->prepare($query);
		$stmt->execute();
        return $stmt;
    }
}
// end of file