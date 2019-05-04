<?php
require 'restful_api.php';

class api extends restful_api {
	function __construct(){
		parent::__construct();
	}
	function user(){
		if ($this->method == 'GET'){
			// Hãy viết code xử lý LẤY dữ liệu ở đây
			// trả về dữ liệu bằng cách gọi: $this->response(200, $data)
			require_once 'dbconf.php';
			$conn = new mysqli($servername, $username, $password, $dbname);
			// Check connection
			if ($conn->connect_error) {
    				$data = "Connection failed, die with " . $servername;
			} else {
				$conn->query("SET time_zone = 'Asia/Saigon'");
				$sql = "SELECT name, password FROM user where bpid='1'";
				$result = $conn->query($sql);
				$numofrow=$result->num_rows;
				if ($numofrow == 1) {
					$row = $result->fetch_assoc();
					$password=$row['password'];
					$name=$row['name'];
					$arr = array('name' => $name, 'password' => $password);
					$data = json_encode($arr,JSON_FORCE_OBJECT);
				} else {
					$data = "query error";
				}
			}
			$conn->close();
			$this->response(200, $data);
		}
		elseif ($this->method == 'POST'){
			// Hãy viết code xử lý THÊM dữ liệu ở đây
			// trả về dữ liệu bằng cách gọi: $this->response(200, $data)
		}
		elseif ($this->method == 'PUT'){
			// Hãy viết code xử lý CẬP NHẬT dữ liệu ở đây
			// trả về dữ liệu bằng cách gọi: $this->response(200, $data)
		}
		elseif ($this->method == 'DELETE'){
			// Hãy viết code xử lý XÓA dữ liệu ở đây
			// trả về dữ liệu bằng cách gọi: $this->response(200, $data)
		}
	}
	function bpdata(){
		if ($this->method == 'GET'){
			// Hãy viết code xử lý LẤY dữ liệu ở đây
			// trả về dữ liệu bằng cách gọi: $this->response(200, $data)
			$data = "bpdata";
			$this->response(200, $data);
		}
		elseif ($this->method == 'POST'){
			// Hãy viết code xử lý THÊM dữ liệu ở đây
			// trả về dữ liệu bằng cách gọi: $this->response(200, $data)
		}
		elseif ($this->method == 'PUT'){
			// Hãy viết code xử lý CẬP NHẬT dữ liệu ở đây
			// trả về dữ liệu bằng cách gọi: $this->response(200, $data)
		}
		elseif ($this->method == 'DELETE'){
			// Hãy viết code xử lý XÓA dữ liệu ở đây
			// trả về dữ liệu bằng cách gọi: $this->response(200, $data)
		}
	}
}
$my_api = new api();

?>
