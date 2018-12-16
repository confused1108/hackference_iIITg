
<!DOCTYPE html>
<html>
<head>
	<title>Report!</title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>


  <script>
function myFunction() {
   //alert ($( "#name" ).val()) ;
}
</script>
</head>
<body>


	<div class="container">
          <div class="wrapper">
        	<h1 align="center">Report</h1>
         
<form action="new.php" method="get" >
      
         
</form>


 <?php


$servername = "localhost";
$username = "ezyprsrs_hitesh";
$password = "HfRezBSUa2vf";
$dbname = "ezyprsrs_hitesh";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 

$test = $_GET["group_id"];
//echo "<script>alert($test)</script>";
$sql = "SELECT param_id,name FROM parameter WHERE para_group_id =$test AND age = 1 AND gender='male'";
$result = $conn->query($sql) or die("invalid data");

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) { ?>
      
           
       <label style="text-align: center; border:1px solid black;width:100%"><?php echo $row["name"]; ?></label><br>
       <input type='hidden' name="id" value="<?php echo $row['param_id'];?>" />
       
   <?php }
} else {
    echo "0 results";
}



$conn->close();
?> 

<div class="row">
  <div class="col-lg-6">
  <label style="text-align: center; border:1px solid black;width:100%">Male</label><br>
<div class="row">
  <div class="col-lg-4">
 

  <label style="text-align: center; border:1px solid black;width:100%">8-9</label><br>
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
$username = "ezyprsrs_hitesh";
$password = "HfRezBSUa2vf";
$dbname = "ezyprsrs_hitesh";

// Create connection




$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 


 
 

$test = $_GET["group_id"];
//echo "<script>alert($test)</script>";
$sql = "SELECT param_id,name FROM parameter WHERE para_group_id =$test AND age = 1 AND gender='male'";
$result = $conn->query($sql) or die("invalid data");

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) { 


  $id = $row['param_id'];
$sql = "SELECT lower_limit,upper_limit, score FROM norms WHERE param_id =$id";


$result1 = $conn->query($sql) or die("invalid data");

if ($result1->num_rows > 0) {
    // output data of each row
    while($row = $result1->fetch_assoc()) { ?>
       <tr>
       <td><?php echo $row["lower_limit"]; ?></td> 
       <td><?php echo $row["upper_limit"]; ?></td>  
       <td><?php echo $row["score"]; ?></td> </tr>
   <?php }
} else {
    echo "0 results";
}
}
}

$conn->close();
?> 



     
    </tbody>
  </table>
</div>
<div class="col-lg-4">
 <label style="text-align: center; border:1px solid black;width:100%">9-10</label><br>


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
$username = "ezyprsrs_hitesh";
$password = "HfRezBSUa2vf";
$dbname = "ezyprsrs_hitesh";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 

$test = $_GET["group_id"];
//echo "<script>alert($test)</script>";
$sql = "SELECT param_id,name FROM parameter WHERE para_group_id =$test AND age = 2 AND gender='male'";
$result = $conn->query($sql) or die("invalid data");

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) { 


  $id = $row['param_id'];
$sql = "SELECT lower_limit,upper_limit, score FROM norms WHERE param_id =$id";


$result1 = $conn->query($sql) or die("invalid data");

if ($result1->num_rows > 0) {
    // output data of each row
    while($row = $result1->fetch_assoc()) { ?>
       <tr>
       <td><?php echo $row["lower_limit"]; ?></td> 
       <td><?php echo $row["upper_limit"]; ?></td>  
       <td><?php echo $row["score"]; ?></td> </tr>
   <?php }
} else {
    echo "0 results";
}
}
}

$conn->close();
?> 



     
    </tbody>
  </table>
</div>
<div class="col-lg-4">


 <label style="text-align: center; border:1px solid black;width:100%">10-11</label><br>


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
$username = "ezyprsrs_hitesh";
$password = "HfRezBSUa2vf";
$dbname = "ezyprsrs_hitesh";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 
 

$test = $_GET["group_id"];
//echo "<script>alert($test)</script>";
$sql = "SELECT param_id,name FROM parameter WHERE para_group_id =$test AND age = 3 AND gender='male'";
$result = $conn->query($sql) or die("invalid data");

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) { 


  $id = $row['param_id'];
$sql = "SELECT lower_limit,upper_limit, score FROM norms WHERE param_id =$id";


$result1 = $conn->query($sql) or die("invalid data");

if ($result1->num_rows > 0) {
    // output data of each row
    while($row = $result1->fetch_assoc()) { ?>
       <tr>
       <td><?php echo $row["lower_limit"]; ?></td> 
       <td><?php echo $row["upper_limit"]; ?></td>  
       <td><?php echo $row["score"]; ?></td> </tr>
   <?php }
} else {
    echo "0 results";
}
}
}

$conn->close();
?> 




     
    </tbody>
  </table>
</div>
</div>
</div>
<div class="col-lg-6">

   <label style="text-align: center; border:1px solid black;width:100%">female</label><br>
   <div class="row">
<div class="col-lg-4">


 <label style="text-align: center; border:1px solid black;width:100%">8-9</label><br>

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
$username = "ezyprsrs_hitesh";
$password = "HfRezBSUa2vf";
$dbname = "ezyprsrs_hitesh";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 
$test = $_GET["group_id"];
//echo "<script>alert($test)</script>";
$sql = "SELECT param_id,name FROM parameter WHERE para_group_id =$test AND age = 1 AND gender='female'";
$result = $conn->query($sql) or die("invalid data");

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) { 


  $id = $row['param_id'];
$sql = "SELECT lower_limit,upper_limit, score FROM norms WHERE param_id =$id";


$result1 = $conn->query($sql) or die("invalid data");

if ($result1->num_rows > 0) {
    // output data of each row
    while($row = $result1->fetch_assoc()) { ?>
       <tr>
       <td><?php echo $row["lower_limit"]; ?></td> 
       <td><?php echo $row["upper_limit"]; ?></td>  
       <td><?php echo $row["score"]; ?></td> </tr>
   <?php }
} else {
    echo "0 results";
}
}
}

$conn->close();
?> 




     
    </tbody>
  </table>
</div>
<div class="col-lg-4">
 <label style="text-align: center; border:1px solid black;width:100%">9-10</label><br>

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
$username = "ezyprsrs_hitesh";
$password = "HfRezBSUa2vf";
$dbname = "ezyprsrs_hitesh";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 
 

$test = $_GET["group_id"];
//echo "<script>alert($test)</script>";
$sql = "SELECT param_id,name FROM parameter WHERE para_group_id =$test AND age = 2 AND gender='female'";
$result = $conn->query($sql) or die("invalid data");

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) { 


  $id = $row['param_id'];
$sql = "SELECT lower_limit,upper_limit, score FROM norms WHERE param_id =$id";


$result1 = $conn->query($sql) or die("invalid data");

if ($result1->num_rows > 0) {
    // output data of each row
    while($row = $result1->fetch_assoc()) { ?>
       <tr>
       <td><?php echo $row["lower_limit"]; ?></td> 
       <td><?php echo $row["upper_limit"]; ?></td>  
       <td><?php echo $row["score"]; ?></td> </tr>
   <?php }
} else {
    echo "0 results";
}
}
}

$conn->close();
?> 




     
    </tbody>
  </table>
</div>
<div class="col-lg-4">
 <label style="text-align: center; border:1px solid black;width:100%">10-11</label><br>

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
$username = "ezyprsrs_hitesh";
$password = "HfRezBSUa2vf";
$dbname = "ezyprsrs_hitesh";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 

$test = $_GET["group_id"];
//echo "<script>alert($test)</script>";
$sql = "SELECT param_id,name FROM parameter WHERE para_group_id =$test AND age = 3 AND gender='female'";
$result = $conn->query($sql) or die("invalid data");

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) { 


  $id = $row['param_id'];
$sql = "SELECT lower_limit,upper_limit, score FROM norms WHERE param_id =$id";


$result1 = $conn->query($sql) or die("invalid data");

if ($result1->num_rows > 0) {
    // output data of each row
    while($row = $result1->fetch_assoc()) { ?>
       <tr>
       <td><?php echo $row["lower_limit"]; ?></td> 
       <td><?php echo $row["upper_limit"]; ?></td>  
       <td><?php echo $row["score"]; ?></td> </tr>
   <?php }
} else {
    echo "0 results";
}
}
}

$conn->close();
?> 




     
    </tbody>
  </table>
</div>
</div>
</div>

</div>
</div>
</body>
</html>