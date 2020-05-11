<?php
#ini_set("upload_max_filesize","500M");
#ini_set("memory_limit","128M");
#ini_set('display_errors', 1);
#ini_set('post_max_size',"80M");
set_time_limit(120);



if (isset($_GET)) {
	foreach ($_GET as $k=>$v) $_GET[$k]=trim($v);
}
if (isset($_POST)) {
	foreach ($_POST as $k=>$v) $_POST[$k]=trim($v);
//	print_r($_POST);
}

	$log_file='pmo.log';

function _log_show() {
	global $log_file;
	$a=file($log_file);
			header("Content-Type: text/html");
			print "<pre>".join("",$a)."</pre>";
}

function send() {
	if ($_GET && isset($_GET['msg']) && $_GET['msg']) {
		$wiad=$_GET['msg'];
		$ip = msg_get_queue(12340);
		$wiad.= (msg_send($ip,8,$wiad,false,false,$err))? "->TRUE":"->FALSE";
		header('Content-Type: application/json');
		die(json_encode(array("status"=>"send","msg"=>$wiad,'ts'=>time())));
	}
}


function sendMsg($id , $msg) {
  echo "id: $id" . PHP_EOL;
  echo "data: {\n";
  echo "data: \"msg\": \"$msg\", \n";
  echo "data: \"id\": $id\n";
  echo "data: }\n";
  echo PHP_EOL;
  ob_flush();
  flush();
}


function chat() {
	if ($_GET && isset($_GET['chat']) && $_GET['chat']) {
	header('Content-Type: text/event-stream');
	header('Cache-Control: no-cache');
	sendMsg(time(),time());
		while (true) {
			$ip = msg_get_queue(12340);
			msg_receive($ip,0,$msgtype,100000,$data,false,null,$err);
			$id=time();
			sendMsg($id,$id);
			//echo "msgtype {$msgtype} data {$data}\n";
		}		
	}
}

if (isset($argv) && isset($argv[1]) && $argv[1]=='msg'){
		$wiad=$argv[1];
		$ip = msg_get_queue(12340);
		$wiad.= (msg_send($ip,8,$wiad,false,false,$err))? "->TRUE":"->FALSE";
		die($wiad."\n");
}

function check_user() {
	//if ($_POST && in_array($_POST['d'],array("HOME","WORK"))) return false;   // You can limit the access to You application here
	return false; // update allowed
	return true; //no update allowed
}

function _log($val='') {
	global $log_file;
	$a=file($log_file);
	array_push($a,join("\t",array(
			date("YmdHis",time()),
			$_POST['o'],
			$_POST['u'],
			$_POST['d'],
			$_POST['v'],
			$_POST['c'],
			$_POST['t'],
			$val
	)));
	foreach ($a as $a1=>$a2) $a[$a1]=trim($a2);
	file_put_contents($log_file,join("\n",$a));
}

chat();send();

if (isset($_POST) && isset($_POST['f1']) && isset($_POST['f2'])) {
#	error_reporting( E_ALL );
	file_put_contents("pmo_ver.txt",$_POST['f1']) or die("ERROR f1");
	file_put_contents("pmo_dat.txt",$_POST['f2']) or die("ERROR f2");
	print_r(array_keys($_POST));
	if (isset($_POST['f3'])){
		file_put_contents("pmo_dat3.txt",$_POST['f3']) or die("ERROR f3");
		die("OK2");
	}
	die("OK2");
}

if (isset($_POST) && isset($_POST['o'])) {
	switch ($_POST['o']) {
		case "v":
			_log(check_user()?"NO_ACCESS":trim(file_get_contents("pmo_ver.txt")));
			header("Content-Type: text/html");
			//if ($_POST['v']<="1.0.0.42") die ("<br/><b>Please download EXE file.</b>"); // You can force the answer i.e. outdated exe
			if (check_user()) {print "1";die();}
	//		if ($_POST['t']==2) {print "3"; die();}
			print check_user()?"1":file_get_contents("pmo_ver.txt");
			die();
			break;
		case "u":
			_log();
			header("Content-Type: text/html");
			if (check_user()) {
				header("HTTP/1.0 404 Not Found");
				die();
			}
			if ($_POST['t']==12) {
				print file_get_contents("pmo_dat3.txt");
				_log("3");
				die();
			}
			print check_user()?"server interal error":file_get_contents("pmo_dat.txt");
			die();
			break;

	}
}
if (isset($_GET) && isset($_GET['log'])) {   //show the cog
	_log_show();
}
if (isset($_GET) && isset($_POST) && !count($_GET) && !count($_POST)) {
			header("Content-Type: text/html");
			print "Actual version: ".file_get_contents("pmo_ver.txt");
	
}

#print_r($_GET);
#print_r($_POST);
?>
