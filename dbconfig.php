<?php
$con = mysqli_connect("localhost","root","","dbscholarship");
if(!$con) {
	echo "Error No:". mysqli_connect_errno();
	echo "Error DescriptionL:". my_connect_error();
	exit;
}
?>