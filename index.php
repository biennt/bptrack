<?php
session_start();
date_default_timezone_set('Asia/Saigon');
### "Exit" button clicked ###
if(isset($_GET['exit'])) {
	if ($_GET['exit'] == '1') {
		session_unset();
		session_destroy();
		header("Location: https://bptrack.herokuapp.com/");
		die();
	}
}
############################
### Logged in or not ? ###
if(isset($_SESSION["bpid"])) {
	$bpid=$_SESSION["bpid"];
	$name=$_SESSION["name"];
} else {
	$bpid="notset";
	$name="notset";
}

############################

### DB Connect ###
require_once 'dbconf.php';
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed, die: " . $conn->connect_error);
}
$conn->query("SET time_zone = 'Asia/Saigon'");

if (isset($_POST['uname']) && isset($_POST['psw'])) {
	$create_account = $_POST['create_account'];
	if ($create_account=="on") {
		$bpid = do_create_account($conn, $_POST['uname'], $_POST['psw']);
	} else {
		$bpid = do_login($conn, $_POST['uname'], $_POST['psw']);
	}
	if (isset($_SESSION['name'])) {
		$name = $_SESSION['name'];
	}
	if ($bpid == "notset") {
		echo "Đăng nhập không thành công!\n";
	}
}

##################
header('Content-Type: charset=utf-8');
print_header($displaytype, $conn, $bpid);

if ($bpid=="notset") {
	print_login_form();
} else {
	print_bpinputform($bpid, $name);
	if (isset($_POST['time']) && isset($_POST['systolic']) && isset($_POST['diastolic']) && isset($_POST['heart_beat'])) {
		$time=$_POST['time'];
		$systolic=$_POST['systolic'];
		$diastolic=$_POST['diastolic'];
		$heart_beat=$_POST['heart_beat'];
		if (($systolic > 0) && ($diastolic > 0) && ($heart_beat > 0)){
			savebpinfo($conn, $bpid, $time, $systolic, $diastolic, $heart_beat);
		}
	}
		echo "<hr>\n";
		echo "<h4>30 ngày trước</h4>\n";
		echo "<div id=\"30_chart\"></div>\n";
		echo "<br>\n";
		echo "<h4>180 ngày trước</h4>\n";
		echo "<div id=\"180_chart\"></div>\n";
		echo "<br>\n";
		echo "<h4>365 ngày trước</h4>\n";
		echo "<div id=\"365_chart\"></div>\n";
		get_rawdata($conn, $bpid, $name);
		echo "<br><a href=\"./?exit=1\" class=\"btn btn-warning\" role=\"button\">Thoát (Exit)</a><br>\n";
}

#### DB Close ####
$conn->close();
print_footer();
#####################################################
#####################################################
function print_header($displaytype, $conn, $id) {
	echo "<!DOCTYPE html>\n";
	echo "<html lang=\"en\">\n";
	echo "  <head>\n";
	echo "<meta charset=\"utf-8\">\n";
	echo "  <link rel=\"stylesheet\" href=\"https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css\" integrity=\"sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T\" crossorigin=\"anonymous\">\n";
	echo "  <script src=\"https://code.jquery.com/jquery-3.3.1.slim.min.js\" integrity=\"sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo\" crossorigin=\"anonymous\"></script>\n";
	echo "  <script src=\"https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js\" integrity=\"sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1\" crossorigin=\"anonymous\"></script>\n";
	echo "  <script src=\"https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js\" integrity=\"sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM\" crossorigin=\"anonymous\"></script>\n";

	get_graph($conn, $id);

	echo "  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">\n";
	echo "  </head>\n";
	echo "  <body>\n";
	echo "<br>\n";
}

#####################################################
function print_footer(){
	echo "</body>\n";
	echo "</html>\n";
}
#####################################################
function savebpinfo($conn, $id, $time, $systolic, $diastolic, $heart_beat) {
	$sql = "INSERT INTO bpmain (bpid, recordtime, systolic, diastolic, heart_beat) values('" . $id . "','" . $time . "'," . $systolic . "," . $diastolic . "," . $heart_beat . ")";
	if (mysqli_query($conn, $sql)) {
		echo "Đã lưu thành công<br>";
	} else {
		echo "Error: " . $sql . "<br>" . mysqli_error($conn);
	}
}
#####################################################
function print_bpinputform($bpid, $name) {
	echo "<b>Nhập các chỉ số huyết áp cho " . $name . "</b><br>\n";
	echo "id: " . $bpid . "<br>\n";
	echo "<form method='POST' action=\"./\">\n";
	echo "  <div class=\"form-group\">\n";
	echo "    <label for=\"Time\">Thời gian đo</label>\n";
	echo "    <input type=\"text\" class=\"form-control\" name=\"time\" id=\"time\" value='" . date('Y-m-d H:i:s') . "'>\n";
	echo "  </div>\n";
	echo "  <div class=\"form-group\">\n";
	echo "    <label for=\"systolic\">Tâm thu (Systolic)</label>\n";
	echo "    <input type=\"number\" class=\"form-control\" name=\"systolic\" id=\"systolic\">\n";
	echo "  </div>\n";
	echo "  <div class=\"form-group\">\n";
	echo "    <label for=\"diastolic\">Tâm trương (Diastolic)</label>\n";
	echo "    <input type=\"number\" class=\"form-control\" name=\"diastolic\" id=\"diastolic\">\n";
	echo "  </div>\n";
	echo "  <div class=\"form-group\">\n";
	echo "    <label for=\"heart_beat\">Nhịp tim (Heart rate)</label>\n";
	echo "    <input type=\"number\" class=\"form-control\" name=\"heart_beat\" id=\"heart_beat\">\n";
	echo "  </div>\n";
	echo "  <button type=\"submit\" class=\"btn btn-primary\">Lưu lại (Save)</button>\n";
	echo "</form>\n";
}
#####################################################
function print_login_form() {
	echo "<h3 align='center'>Sổ tay theo dõi huyết áp</h3>\n";
	echo "	 <form action=\"./\" method=\"post\">\n";
	echo "	  <div class=\"form-group\" align=\"center\">\n";
	echo "		<input type=\"text\" class=\"form-control\" name=\"uname\" placeholder=\"Tên đăng nhập\" required>\n";
	echo "<br>\n";
	echo "		<input type=\"password\" class=\"form-control\" name=\"psw\" placeholder=\"Mật khẩu\" required>\n";
	echo "<br>\n";
	echo "<div class=\"checkbox\">\n";
	echo "<label><input type=\"checkbox\" name=\"create_account\">Tạo mới tài khoản</label>\n";
	echo "</div>\n";
	echo "		<button type=\"submit\" class=\"btn btn-primary\">Đăng nhập</button>\n";
	echo "<br>\n";
	echo "	  </div>\n";
	echo "	</form>\n";
}
#####################################################
function do_login($conn, $uname, $upass) {
	$bpid="notset";
	if (ctype_alnum($uname)) {
		$sql = "SELECT name, password FROM user where bpid='" . $uname . "'";
		$result = $conn->query($sql);
		$numofrow=$result->num_rows;
		if ($numofrow == 1) {
			$row = $result->fetch_assoc();
			$stored_hash=$row['password'];
			if ( password_verify ($upass ,$stored_hash )) {
				$bpid=$uname;
				$_SESSION["bpid"] = $bpid;
				$_SESSION["name"] = $row['name'];
			}
		}
	}
	return $bpid;
}
#####################################################
function do_create_account($conn, $uname, $upass) {
	$bpid="notset";
	$hash=password_hash($upass,PASSWORD_DEFAULT);
	if (ctype_alnum($uname)) {
		$sql = "INSERT into user (bpid, name, password) values ('". $uname . "','" . $uname . "','" . $hash . "')";
		if (mysqli_query($conn, $sql)) {
			echo "Đã lưu thành công<br>";
			$bpid=$uname;
			$_SESSION["bpid"] = $bpid;
			$_SESSION["name"] = $bpid;
			$_SESSION["displaytype"] = "table";
		} else {
			$error_code = mysqli_error($conn);
			if (strpos($error_code,"Duplicate ") >=0) {
				echo "Tên đăng nhập bị trùng, không tạo được tài khoản!<br>\n";
			} else {
				echo "Error: ". $error_code . "<br>\n";
			}
		}
	} else {
		echo "Tên đăng nhập chỉ gồm chữ cái và số<br>\n";
	}
	return $bpid;
}

#####################################################
function get_graph($conn, $id) {
        echo "<script type=\"text/javascript\" src=\"https://www.gstatic.com/charts/loader.js\"></script>\n";
        echo "  <script type=\"text/javascript\">\n";
        echo "    google.charts.load('current', {'packages':['corechart']});\n";
        echo "    google.charts.setOnLoadCallback(drawChart);\n";
        echo "    function drawChart() {\n";
        echo "          var data = google.visualization.arrayToDataTable([\n";
        echo "            ['Time', 'Systolic', 'Diastolic', 'Heart beat'],\n";
        ############################################################
        $sql1 = "SELECT * FROM bpmain where bpid='" . $id . "'" . " and recordtime between DATE_SUB(NOW(), INTERVAL 30 DAY) and NOW() order by recordtime";
	$conn->query("SET time_zone = 'Asia/Saigon'"); 
	$result1 = $conn->query($sql1);
        $numofrow1=$result1->num_rows;
        $rowcount1=0;
        if ($numofrow1> 0) {
                // output data of each row
                while($row1 = $result1->fetch_assoc()) {
                        $rowcount1++;
                        echo "['";
                        echo $row1["recordtime"];
                        echo "',";
                        echo $row1["systolic"];
                        echo ",";
                        echo $row1["diastolic"];
                        echo ",";
                        echo $row1["heart_beat"];
                        echo "]";
                        if ($rowcount1 < $numofrow1) {
                                echo ",\n";
                        } else {
                                echo "\n";
                        }
                }
        } else {
                echo "0 results\n";
        }
        ############################################################
        echo "          ]);\n";
        echo "          var options = {\n";
        echo "            title: 'Biểu đồ 30 ngày gần đây',\n";
        echo "            curveType: 'function',\n";
				echo "				width: '100%',\n";
				echo "				height: '600',\n";
				echo "			  chartArea:{left:40,top:20,width:\"90%\",height:\"400\"},\n";
        echo "            legend: { position: 'bottom' },\n";
				echo "			  colors: ['green', 'blue', 'gray'],\n";
				echo "            seriesType: 'line',\n";
        echo "			  series: {2: {type: 'bars'}}\n";
        echo "          };\n";
        echo "          var chart = new google.visualization.LineChart(document.getElementById('30_chart'));\n";
        echo "  chart.draw(data, options);\n";
        echo "    }\n";
        echo "  </script>\n";

        echo "  <script type=\"text/javascript\">\n";
        echo "    google.charts.load('current', {'packages':['corechart']});\n";
        echo "    google.charts.setOnLoadCallback(drawChart);\n";
        echo "    function drawChart() {\n";
        echo "          var data = google.visualization.arrayToDataTable([\n";
        echo "            ['Time', 'Systolic', 'Diastolic', 'Heart beat'],\n";
        ############################################################
        $sql2 = "SELECT * FROM bpmain where bpid='" . $id . "'" . " and recordtime between DATE_SUB(NOW(), INTERVAL 180 DAY) and NOW() order by recordtime";
        #echo $sql . "\n";
	$conn->query("SET time_zone = 'Asia/Saigon'");
	$result2 = $conn->query($sql2);
        $numofrow2=$result2->num_rows;
        $rowcount2=0;
        if ($numofrow2 > 0) {
                // output data of each row
                while($row2 = $result2->fetch_assoc()) {
                        $rowcount2++;
                        echo "['";
                        echo $row2["recordtime"];
                        echo "',";
                        echo $row2["systolic"];
                        echo ",";
                        echo $row2["diastolic"];
                        echo ",";
                        echo $row2["heart_beat"];
                        echo "]";
                        if ($rowcount2 < $numofrow2) {
                                echo ",\n";
                        } else {
                                echo "\n";
                        }
                }
        } else {
                echo "0 results\n";
        }
        ############################################################
        echo "          ]);\n";
        echo "          var options = {\n";
        echo "            title: 'Biểu đồ 6 tháng gần đây',\n";
        echo "            curveType: 'function',\n";
				echo "				width: '100%',\n";
				echo "				height: '600',\n";
				echo "			  chartArea:{left:40,top:20,width:\"90%\",height:\"400\"},\n";
        echo "            legend: { position: 'bottom' },\n";
				echo "			  colors: ['green', 'blue', 'gray'],\n";
				echo "            seriesType: 'line',\n";
        echo "			  series: {2: {type: 'bars'}}\n";
        echo "          };\n";
        echo "          var chart = new google.visualization.LineChart(document.getElementById('180_chart'));\n";
        echo "  chart.draw(data, options);\n";
        echo "    }\n";
        echo "  </script>\n";

        echo "  <script type=\"text/javascript\">\n";
        echo "    google.charts.load('current', {'packages':['corechart']});\n";
        echo "    google.charts.setOnLoadCallback(drawChart);\n";
        echo "    function drawChart() {\n";
        echo "          var data = google.visualization.arrayToDataTable([\n";
        echo "            ['Time', 'Systolic', 'Diastolic', 'Heart beat'],\n";
        ############################################################
        $sql3 = "SELECT * FROM bpmain where bpid='" . $id . "'" . " and recordtime between DATE_SUB(NOW(), INTERVAL 365 DAY) and NOW() order by recordtime";
        #echo $sql . "\n";
	$conn->query("SET time_zone = 'Asia/Saigon'");
	$result3 = $conn->query($sql3);
        $numofrow3=$result3->num_rows;
        $rowcount3=0;
        if ($numofrow3 > 0) {
                // output data of each row
                while($row3 = $result3->fetch_assoc()) {
                        $rowcount3++;
                        echo "['";
                        echo $row3["recordtime"];
                        echo "',";
                        echo $row3["systolic"];
                        echo ",";
                        echo $row3["diastolic"];
                        echo ",";
                        echo $row3["heart_beat"];
                        echo "]";
                        if ($rowcount3 < $numofrow3) {
                                echo ",\n";
                        } else {
                                echo "\n";
                        }
                }
        } else {
                echo "0 results\n";
        }
        ############################################################
        echo "          ]);\n";
        echo "          var options = {\n";
        echo "            title: 'Biểu đồ 1 năm gần đây',\n";
        echo "            curveType: 'function',\n";
				echo "				width: '100%',\n";
				echo "				height: '600',\n";
				echo "			  chartArea:{left:40,top:20,width:\"90%\",height:\"400\"},\n";
        echo "            legend: { position: 'bottom' },\n";
				echo "			  colors: ['green', 'blue', 'gray'],\n";
				echo "            seriesType: 'line',\n";
        echo "			  series: {2: {type: 'bars'}}\n";
        echo "          };\n";
        echo "          var chart = new google.visualization.LineChart(document.getElementById('365_chart'));\n";
        echo "  chart.draw(data, options);\n";
        echo "    }\n";
        echo "  </script>\n";
}
###############################################
function getmax($conn, $fname, $id)
{
        $sql = "SELECT max(" . $fname . ") as maxval FROM bpmain where bpid='" . $id . "'";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $value = $row["maxval"];
return $value;
}
###############################################
function getmin($conn, $fname, $id)
{
        $sql = "SELECT min(" . $fname . ") as minval FROM bpmain where bpid='" . $id . "'";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $value = $row["minval"];
return $value;
}
###############################################
function get_rawdata($conn, $id, $name){
        $sql = "SELECT * FROM bpmain where bpid='" . $id . "'" . " order by recordtime desc";
        $result = $conn->query($sql);
				echo "<b>Lịch sử các chỉ số huyết áp của " . $name . "</b>\n";
				echo "id: " . $id . "<br>\n";
        echo "<div class='table-responsive'>\n";
        echo "<table class='table'>\n";
        echo "<tr>\n";

        echo "<td>\n";
        echo "Thời gian đo\n";
        echo "</td>\n";

        echo "<td>\n";
        echo "Tâm thu<br>\n";
        echo "Max=". getmax($conn, "systolic", $id) . "<br>\n";
        echo "Min=". getmin($conn, "systolic", $id) . "<br>\n";
        echo "</td>\n";

        echo "<td>\n";
        echo "Tâm trương<br>\n";
        echo "Max=". getmax($conn, "diastolic", $id) . "<br>\n";
        echo "Min=". getmin($conn, "diastolic", $id) . "<br>\n";
        echo "</td>\n";

        echo "<td>\n";
        echo "Nhịp tim<br>\n";
        echo "Max=". getmax($conn, "heart_beat", $id) . "<br>\n";
        echo "Min=". getmin($conn, "heart_beat", $id) . "<br>\n";
        echo "</td>\n";

        echo "</tr>\n";
        if ($result->num_rows > 0) {
                // output data of each row
                while($row = $result->fetch_assoc()) {
                        echo "<tr>\n";
                        echo "<td>" . $row["recordtime"] . "</td>\n";
                        echo "<td>" . $row["systolic"] . "</td>\n";
                        echo "<td>" . $row["diastolic"] . "</td>\n";
                        echo "<td>" . $row["heart_beat"] . "</td>\n";
                        echo "</tr>\n";
                }
        } else {
                echo "0 results\n";
        }
        echo "</table>\n";
        echo "</div>\n";
}
###############################################
?>
