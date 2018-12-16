
<!DOCTYPE html>
<html>
<head>
	<title>Report!</title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>
<body>


	<div class="container">
          <div class="wrapper">
        	<h1 align="center">Report</h1>
         </div>  
<form action="" method="get" >
      
</form>


 <?php


$servername = "localhost";
$username = "root";
$password = "";
$dbname = "talent";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 
 

$test = $_GET["pram_id"];
//echo "<script>alert($test)</script>";
$sql = "SELECT param_id, name,gender,age FROM parameter WHERE param_id =$test";
$result = $conn->query($sql) or die("invalid data");

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) { ?>
      
           
       <label style="text-align: center; border:1px solid black;width:100%"><?php echo $row["name"]; ?></label><br>
        <label style="text-align: center; border:1px solid black;width:100%"><?php echo $row["age"]; ?></label><br>
         <label style="text-align: center; border:1px solid black;width:100%"><?php echo $row["gender"]; ?></label><br>
   <?php }
} else {
    echo "0 results";
}



$conn->close();
?> 


 


<div class="row">
 


<div class="col-lg-12">
 <table class="table table-striped">
    <thead>
      <tr>
        <th>lowar</th>
        <th>upper</th>
        <th>score</th>
      </tr>
    </thead>
    <tbody>
    


     <?php



     $servername = "localhost";
     $username = "root";
     $password = "";
     $dbname = "talent";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 
 

$test = $_GET["pram_id"];
//echo "<script>alert($test)</script>";
$sql = "SELECT lower_limit,upper_limit, score FROM norms WHERE param_id =$test";
$result = $conn->query($sql) or die("invalid data");

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) { ?>
       <tr>
       <td><?php echo $row["lower_limit"]; ?></td> 
       <td><?php echo $row["upper_limit"]; ?></td>  
       <td><?php echo $row["score"]; ?></td> </tr>
   <?php }
} else {
    echo "0 results";
}


$conn->close();
?> 



     
    </tbody>
  </table>
</div>
</div>


</body>
</html>