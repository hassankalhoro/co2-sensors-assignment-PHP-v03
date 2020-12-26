# co2-sensors-assignment-PHP-v03
Downlad repository and place code in apropriate direcotriy folder to run on localhost my folder name is "CO2"
create database with name "db_co2" optional you can create with any name
create table with name "sensors", you can import the table  I have provided inside this repositry with name co2.sql, this table contains sample data for testing perpose 
All the apis' are tested on postman api tool and gives resoponse as required 

According to the requirments sensors are in millions of number and my collection measurment api may hit concurrently so this system is not accepting millions of concurrent hits at once 
for increasing concurrency rate We can implment a elastic container service with load balancing techniques 
although at this level I can provide archive methodology mysql for retrieving perpose and genrate query based views so before inserting data we check for sensor status only hit last three records 
I have created index column sensor_id for retrieving selection faster 


1- POST http://localhost/CO2/api/v1/sensors/1/mesurements 
with sample data
{
 "co2" : 266,
 "time" : "2019-02-01T18:55:47+00:00"
}


2- GET http://localhost/CO2/api/v1/sensors/1
3- GET http://localhost/CO2/api/v1/sensors/1/metrics
4- GET http://localhost/CO2/api/v1/sensors/1/alerts

I have provided simple interface for dump data for testing perpose url
http://localhost/CO2/api/v1/sensors/dashboard




mysql query for discussion 

SELECT
    id,
    sense_id,co2,
    sensor_status
FROM
(
    SELECT
        id,
    sense_id,co2,
    sensor_status,
        @rn := IF(@prev = sense_id, @rn + 1, 1) AS rn,
        @prev := sense_id
    FROM sensors
    JOIN (SELECT @prev := NULL, @rn := 0) AS vars
    ORDER BY sense_id, id DESC, id
) AS T1
WHERE rn <= 3
