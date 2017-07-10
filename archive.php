<?php
session_start();
error_reporting(E_ALL);
$site_root ="stevensonco";
define('BASE_PATH', realpath(dirname(__FILE__)));
define('BASE_URL', 'http://'.$_SERVER['HTTP_HOST']."/".$site_root."/");
//CONSTANTS
define('DATABASE','stevensonco');
define('HOST','localhost');
define('USERNAME','root');
//LOCAL
define('PASSWORD','root');
//REMOTE
//define('PASSWORD','password_here');

class Model {
	private $database;
	private $host;
	private $username;
	private $password;
	function __construct() {
		$this -> database = DATABASE;
		$this -> host = HOST;
		$this -> username = USERNAME;
		$this -> password = PASSWORD;
	}

	public function getDatabase() {
		 
		return $this -> database;
	}

	public function setDatabase($database) {
		$this -> database = $database;
	}

	public function connect() {
		//mysql_connect($this -> host, $this -> username, $this -> password);
		$con = mysqli_connect($this -> host, $this -> username, $this -> password);
		return $con;
	}

	public function exe_sql($con, $sql, $return = "") {
		$res = mysqli_query($con, $sql);
		
		if (mysqli_connect_errno())
		  {
		  //echo "Failed to connect to MySQL: " . mysqli_connect_error();
		   $result_e =  "Failed to connect to MySQL: " . mysqli_connect_error();
		  return ($result_e );
		  }
		  
		if (gettype($res) == "boolean") {
			//INSERT creates a boolean for $res
			//return last id
			return mysqli_insert_id($con);
		}
		 
		$data = "";
		while ($row = mysqli_fetch_assoc($res)) {
			$data[] = $row;
		}
		// Free result set
		mysqli_free_result($res);
		mysqli_close($con);
		
		if ($return == "json") {
			return json_encode($data);
			
		} else {
			return $data;
		}
	}

}

class people extends Model  { 

	private $id;
	private $firstname;
	private $lastname;

	function __construct(){
		parent::__construct();
	} 
	public function model_connect() {
		return parent::connect();
	}
	function init($id, $firstname, $lastname){
		$this -> id = $id;
		$this -> firstname = $firstname;
		$this -> lastname = $lastname;
	} 
	public function set_id($id){
		$this -> id = $id;
	}
	public function get_id(){
		return $this -> id; 
	}
	 
	public function set_firstname($firstname){
		$this -> firstname = $firstname;
	}
	public function get_firstname(){
		return $this -> firstname; 
	}
	public function set_lastname($lastname){
		$this -> lastname = $lastname;
	}
	public function get_lastname(){
		return $this -> lastname; 
	}

	public function read($return = "") {
		$con = $this -> model_connect();
		$sql = " SELECT * FROM " . $this -> getDatabase() . ".people ;";
		$data = $this -> exe_sql($con, $sql, $return);
		return $data;
	 
	} 

//---SQL INSERT -------------------------------

	function create($people ) {
		$con = $this -> model_connect();
		$sql = "INSERT INTO ".$this -> getDatabase().".people (id,firstname,lastname)
		VALUES('".$people->get_id()."'  , '".$people->get_firstname()."' , '".$people->get_lastname()."' );"; 
		$data = $this -> exe_sql($con,$sql, $return);
		 // in the case of an insert , the return data will be the "last id inserted".
		//echo($data);
	 
	 } 
	function update($people) {
		$con = $this -> model_connect();
		$sql = "UPDATE ".$this -> getDatabase().".people set firstname = '".$people->get_firstname()."' , lastname = '".$people->get_lastname()."'  WHERE id = '".$people->get_id()."'";	
		$data = $this -> exe_sql($con, $sql, $return);
 		//echo($sql);
	}
	 
	function delete($people){
		$con = $this -> model_connect();
		$sql = "DELETE FROM " . $this -> getDatabase() . ".people WHERE id = " . $people -> get_id() . "  ;";
		$data = $this -> exe_sql($con, $sql, $return);
		//echo($data);
	}
	 
}

?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset='utf-8'>
         <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
		 
 
		<title>Stevenson Co</title>
		<style>
		html,body{width:100%;margin:0 auto;}
		
		 #content{width:90%;margin:5%;}
		 table input{background-color:#ddd;}
		  div,input{margin-top:1%;}
		</style>
	</head>
	<body>
		
		 <div id="content">
			<h1>Stevenson Co.</h1>
		<?php
			//Properties
			$table ="";
			$current_id ="";
			$current_firstname = "";
			$current_lastname = "";

			//Requests
			if (isset($_POST['method'])) {
				switch ($_POST['method']) {
					case 'add' :
						add($_POST);
						$_id ="";
						$_firstname = "";
						$_lastname = "";
						$people = new people(); 
						$_data = $people->read();
						$table = createTable($_data);
						

						break;
					case 'edit' :
						 
						$_id=$_POST['id'];
						$_firstname=$_POST['firstname'];
						$_lastname=$_POST['lastname'];

						$people = new people(); 
						$_data = $people->read();
						$table = createTable($_data);
						break;
					case 'update' :

						update($_POST);
						$people = new people(); 
						$_data = $people->read();
						$table = createTable($_data);
						break;
					 
					case 'remove' :
						remove($_POST);
						 
						$_id="";
						$_firstname="";
						$_lastname="";

						$people = new people(); 
						$_data = $people->read();
						$table = createTable($_data);

						break;
					case 'delete' :
						extracting($_POST);
						
						$_id=$_POST['id'];
						$_firstname=$_POST['firstname'];
						$_lastname=$_POST['lastname'];

						$people = new people(); 
						$_data = $people->read();
						$table = createTable($_data);
						break;
					case 'cancel' :
						 
						$_data['id'] =$_SESSION['d_id'];
						$_data['firstname'] = $_SESSION['d_firstname'];
						$_data['lastname'] = $_SESSION['d_lastname'];

						add($_data);
						$people = new people(); 
						$_data = $people->read();
						$table = createTable($_data);
						

						break;
					default :
						$_id ="";
						$_firstname = "";
						$_lastname = "";
						$people = new people(); 
						$_data = $people->read();
						$table = createTable($_data);
						
						break;
				}
			} else{
				// no request method
				$people = new people(); 
				$_data = $people->read();
				$table = createTable($_data);
			}


function createTable($_data){
	$table  = "<div class='table_container'>";
	$table .= "<table class='people_table' >";
	$table .= "<thead>";
	$table .= "</thead>";
	$table .= "<tbody>";
	foreach( $_data as $_row ) {
		$table .= "<tr><form action=".$_SERVER['PHP_SELF']." method='post'>";
	    if( is_array( $_row ) ) {
	    	$id="";
	        foreach( $_row as $k=>$v ) {
	        	 if($k=="id"){
	        	 	$table .= "
	        	 	<td>
	        	 	<input type='hidden' name='id' value='".$v."'>
					<input type='submit' name='method' value='edit'>
					<input type='submit' name='method' value='delete'>
	        	 	</td>";
	        	 }else{
	        	 	$table .= "<td>";
	           		$table .= "<input type='text' name='".$k."' value='".$v."'   >";
	            	$table .= "<td>";
	        	 }
	     	 }
	    }  
	    $table .= "</form></tr>";
	}
	$table .="</tbody>";
	$table .= "</table>";
	$table .="</div>";
	return $table;
}


$form ="
	<div class='form_container'>";
 if($_POST['method'] == 'delete'   ){
 	$form .="<div style='color:red;' >Are you sure?</div>";
 }else{
 	$form .="<div>&nbsp;</div>";
 }
$form .="
	<form action=".$_SERVER['PHP_SELF']." method='post'>
	<input type='hidden' name='id' value='".$_id."' >
	<input type='text' name='firstname' placeholder='first name' value='".$_firstname."'>
	<br>
	<input type='text' name='lastname' placeholder='last name' value='".$_lastname."'>
	<br>";




	 
	 if($_POST['method'] == 'edit'   ){
$form .="
	<input type='submit' name='method' value ='update'>";
	 } else if($_POST['method'] == 'delete'){
$form .=" 
	<input type='submit' name='method' value ='cancel'>";

	 } else{
$form .= "
	<input type='submit' name='method' value ='add'>";
	 }



	 if($_POST['method'] == 'delete'   ){
	 	$form .=" <input type='submit' name='method' value ='remove' style='color:red;' >";
	 }else{
	 	$form .=" <input type='submit' name='method' value ='cancel'>";
	 }



$form .="
	</form>
	</div>
	";

echo($form);
echo($table);
//echo('<pre>');print_r($_POST);echo('</pre>');
?>

		 </div>
		 
	</body>
</html>



<?php

function add($_data){
	$people = new people(); 
	$people->init($_data['id'],$_data['firstname'],$_data['lastname']);
	$people ->create( $people);
}
 
function update($_data){
	$people = new people(); 
	$people->init($_data['id'],$_data['firstname'],$_data['lastname']);
	$people->set_id($_data['id']);
	$people ->update($people);
	unset($people);
	$data = $_data;
}
function remove($_data){
	$people = new people();
	$people->set_id($_data['id']);
	$people ->delete( $people);
	unset($people);	 
}
function extracting($_data){

	$people = new people(); 
	$people->init($_data['id'],$_data['firstname'],$_data['lastname']);
	$_SESSION['d_id']		= $_data['id'];
	$_SESSION['d_firstname']= $_data['firstname'];
	$_SESSION['d_lastname']	= $_data['lastname'];

	$people ->delete( $people);
	unset($people);	


}
?>

 


