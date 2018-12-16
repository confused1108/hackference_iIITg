

<!DOCTYPE html>

  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
 
</head>
<body>

<div class="container">
  <h2 style="text-align:center">Report</h2>
  <p></p>            
 <form action="" method="get">
  
 
  

<table class="table table-bordered">
    <thead>
      <tr>
		<th>male</th>
        <th>Low</th>
        <th>Heigh</th>
		</tr>
		<thead>
		<tbody>
<?php
			//echo "helloo";
				if(isset($_GET['id']))

				{
					$id=$_GET['id'];
				
			 $conn = mysqli_connect("localhost","ezyprsrs_hitesh","HfRezBSUa2vf","ezyprsrs_hitesh");

				// Check connection
				if ($conn->connect_error) {
					die("Connection failed: " . $conn->connect_error);
				} 
				else
				$sql = "SELECT DISTINCT name FROM parameter WHERE para_group_id='$id' ORDER BY age";
						
				//echo $sql;exit();
				$query = mysqli_query($conn,$sql) or die("Invalid Query");
				//print_r(mysqli_fetch_array($query));
				while($res =mysqli_fetch_array($query))
				{ 
						
				echo "<input type='text' value='$res[name]'class='form-control' readonly>";


				}










					//echo "connection ";
				//echo $name;
				$para_group_id=$_GET['id'];
				//echo $para_group_id;exit();
				$sql = "SELECT * FROM parameter WHERE para_group_id='$para_group_id' ORDER BY age";
						
				//echo $sql;exit();
				$query = mysqli_query($conn,$sql) or die("Invalid Query");
				//print_r(mysqli_fetch_array($query));
				while($res =mysqli_fetch_row($query))
				{ 
						//print_r($res);exit();
				if($res[8]=="male")
					{
						if($res[7]==1)
						{
							$r="8-9";
						}
						if($res[7]==2)
						{
							$r="9-10";
						}
						if($res[7]==3)
						{
							$r="10-11";
						}
						if($res[7]==0)
						{
							$r="7-8";
						}

					//echo "<script>alert('hello')</script>";
					?>

					<tr>
					<td><?php echo $r;?></td>
					<td><?php echo $res[4];?></td>
					<td><?php echo $res[5];?></td>
					
					
					</tr>
					
			
			
			
<?php
		}
			
				}
				}
		
		
?>		

    <thead>
      <tr>
		<th>female</th>
        <th>Low</th>
        <th>Heigh</th>
		</tr>
		<thead>
		<tbody>			
		
	<?php
			if(isset($_GET['id']))

				{
		
			//echo "helloo";
				
				
				//echo $name;
			$conn = mysqli_connect("localhost","ezyprsrs_hitesh","HfRezBSUa2vf","ezyprsrs_hitesh");

				// Check connection
				if ($conn->connect_error) {
					die("Connection failed: " . $conn->connect_error);
				} 
				else
					//echo "connection ";
				//echo $name;
	     $para_group_id=$_GET['id'];
				$sql = "SELECT * FROM parameter where para_group_id='$para_group_id' ORDER BY age";
				//echo $sql;exit();
				$query = mysqli_query($conn,$sql) or die("Invalid Query");
				//print_r(mysqli_fetch_array($query));
				while($res =mysqli_fetch_row($query))
				{ 
				if($res[8]=="female")
					{
						if($res[7]==1)
						{
							$r="8-9";
						}
						if($res[7]==2)
						{
							$r="9-10";
						}
						if($res[7]==3)
						{
							$r="10-11";
						}
						if($res[7]==0)
						{
							$r="7-8";
						}

					//echo "<script>alert('hello')</script>";
					?>
					<tr>
					<td><?php echo $r;?></td>
					<td><?php echo $res[4];?></td>
					<td><?php echo $res[5];?></td>
					
					
					</tr>
					
			
			
			
<?php
		}
			
				}
				}
					
	

		?>
		
 
			 
  
</tbody>
</table>	 
</form><br><br>


</body>
</html>
