<?php $base_url="http://".$_SERVER['SERVER_NAME'].dirname($_SERVER["REQUEST_URI"].'?').'/';?>
<form >
  <label for="fname">Populate sensors data upto 100 to 100000:</label><br>
  <input type="number" id="ccount" name="ccount" value=""><br><br>
  <input type="button" id="populate" value="Populate">
</form>
<br><br>
<form >
  <label for="fname">Get sensor status for example UUID 1,2,3...:</label><br>
  <input type="text" placeholder="UUID" id="cuuid" name="cuuid" value=""><br><br>
  <input type="button" id="sensor_data" value="sensor_data">
</form>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
$("#populate").click(function(){
	$('body').append('<div style="" id="loadingDiv"><div class="loader">Loading...</div></div>');
	var ccount = $("#ccount").val();
  $.post("<?php echo $base_url; ?>sensor-mesurements",
  {
    ccount: ccount
  },
  function(data, status){
	  removeLoader();
	  var obj = jQuery.parseJSON(data);

    alert("Data: " + obj.message + "\nStatus: " + status);
  });
});

$("#sensor_data").click(function(){
	$('body').append('<div style="" id="loadingDiv"><div class="loader">Loading...</div></div>');
	var cuuid = $("#cuuid").val();
  $.post("<?php echo $base_url; ?>sensor-mesurements",
  {
    cuuid: cuuid
  },
  function(data, status){
	  removeLoader();
	  var obj = jQuery.parseJSON(data);

    alert("Sensor Status: " + obj.status + "\nStatus: " + status);
  });
});


function removeLoader(){
    $( "#loadingDiv" ).fadeOut(500, function() {
      // fadeOut complete. Remove the loading div
      $( "#loadingDiv" ).remove(); //makes page more lightweight 
  });  
}

</script>