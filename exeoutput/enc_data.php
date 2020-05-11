<?php
$defaultTimeZone='Europe/Warsaw';date_default_timezone_set($defaultTimeZone);
		ini_set('default_socket_timeout', 40);
		//set_time_limit(120);


$dates=null;
$storagelocation = (function_exists('exo_getglobalvariable')) ? exo_getglobalvariable('HEPubStorageLocation', '') :"";
 
# Source: http://stackoverflow.com/documentation/php/5794/cryptography/25499/

# Encrypt Files

/**
* Define the number of blocks that should be read from the source file for each chunk.
* For 'AES-128-CBC' each block consist of 16 bytes.
* So if we read 10,000 blocks we load 160kb into memory. You may adjust this value
* to read/write shorter or longer chunks.
*/
define('FILE_ENCRYPTION_BLOCKS', 10000);

/**
* Encrypt the passed file and saves the result in a new file with ".enc" as suffix.
* 
* @param string $source Path to file that should be encrypted
* @param string $key    The key used for the encryption
* @param string $dest   File name where the encryped file should be written to.
* @return string|false  Returns the file name that has been created or FALSE if an error occured
*/
function encryptFile($source, $key, $dest)
{
		$a=file_get_contents($source);
		$source.=".jrtemp.bz2";
		$source="php://memory";
		//file_put_contents($source,bzcompress($a));
		$fpIn=fopen($source,"rb+");
		fwrite($fpIn,bzcompress($a));
		rewind($fpIn);
		unset($a);

		
		
    $key = substr(sha1($key, true), 0, 16);
    $iv = openssl_random_pseudo_bytes(16);

    $error = false;
    if ($fpOut = fopen($dest, 'w')) {
        // Put the initialzation vector to the beginning of the file
        fwrite($fpOut, $iv);
        if ($fpIn) {
        //if ($fpIn = fopen($source, 'rb')) {
            while (!feof($fpIn)) {
                $plaintext = fread($fpIn, 16 * FILE_ENCRYPTION_BLOCKS);
                $ciphertext = openssl_encrypt($plaintext, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
                // Use the first 16 bytes of the ciphertext as the next initialization vector
                $iv = substr($ciphertext, 0, 16);
                bzwrite($fpOut, $ciphertext);
            }
            fclose($fpIn);
        } else {
            $error = true;
        }
        fclose($fpOut);
    } else {
        $error = true;
    }
//		@unlink ($source);
    return $error ? false : $dest;
}

# Decrypt Files

#To decrypt files that have been encrypted with the above function you can use this function.

/**
* Dencrypt the passed file and saves the result in a new file, removing the
* last 4 characters from file name.
* 
* @param string $source Path to file that should be decrypted
* @param string $key    The key used for the decryption (must be the same as for encryption)
* @param string $dest   File name where the decryped file should be written to.
* @return string|false  Returns the file name that has been created or FALSE if an error occured
*/
function decryptFile($source, $key, $dest)
{
    $key = substr(sha1($key, true), 0, 16);

    $error = false;
    if ($fpOut = fopen($dest, 'w')) {
//        if ($fpIn = bzopen($source, 'rb')) {
        if ($fpIn = fopen($source, 'r')) {
            // Get the initialzation vector from the beginning of the file
            $iv = bzread($fpIn, 16);
            while (!feof($fpIn)) {
                // we have to read one block more for decrypting than for encrypting
                $ciphertext = fread($fpIn, 16 * (FILE_ENCRYPTION_BLOCKS + 1)); 
                $plaintext = openssl_decrypt($ciphertext, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
                // Use the first 16 bytes of the ciphertext as the next initialization vector
                $iv = substr($ciphertext, 0, 16);
                fwrite($fpOut, $plaintext);
            }
            fclose($fpIn);
        } else {
            $error = true;
        }
        fclose($fpOut);
    } else {
        $error = true;
    }

    return $error ? false : $dest;
}

function decryptFileMem($source, $key,$debug=0)
{
		if ($debug) print "\n// $source dec";
		if (!is_file($source)) return false;
		if ($debug) print "ode";
		$wynik="";
    $key = substr(sha1($key, true), 0, 16);
		
    //if ($fpOut = fopen($dest, 'w')) {
//        if ($fpIn = bzopen($source, 'rb')) {
				if ($debug) print "1";
        if ($fpIn = fopen($source, 'r')) {
            // Get the initialzation vector from the beginning of the file
            if ($debug) print "2";
            $iv = fread($fpIn, 16);
            if ($debug) print "3";
            while (!feof($fpIn)) {
                // we have to read one block more for decrypting than for encrypting
                if ($debug) print "4";
                $ciphertext = fread($fpIn, 16 * (FILE_ENCRYPTION_BLOCKS + 1)); 
                if ($debug) print "a";
                $plaintext = openssl_decrypt($ciphertext, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
                if ($debug) print "b";
                // Use the first 16 bytes of the ciphertext as the next initialization vector
                $iv = substr($ciphertext, 0, 16);
                //fwrite($fpOut, $plaintext);
                $wynik.=$plaintext;
            }
            if ($debug) print "5";
            fclose($fpIn);
            if ($debug) print "6";
        } else {
            if ($debug) print "err";
            return false;
        }
    //    fclose($fpOut);
    //} else {
    //    $error = true;
    //}
    if ($debug) print "wyn";
		return bzdecompress ($wynik);
//    return $error ? false : $dest;


}

function remoteFileData($f,$ladnie=1) {
   $h = @get_headers($f, 1);
    if (stristr($h[0], '200')) {
        foreach($h as $k=>$v) {
            if(strtolower(trim($k))=="last-modified") {
							 $date=date("Y-m-d H:i:s", strtotime($v));
							 if (!$ladnie) return strtotime($v);
							return $date;
						}
        }
    }
    return false;
}

function localFileData($f,$ladnie=1) {
//	return false;
	if (!is_file($f)) return false;
	if (($v=@filemtime($f))===false) return false;
	if (!$ladnie) return $v;
	$date=date("Y-m-d H:i:s", $v);
	return $date;
}

function JSlocalFileData($f,$ladnie=1) {
//	return false;
	if (!is_file($f)) return false;
	$f=fopen($f,"r");
	$v=fread($f,40);
	//print __LINE__." -->".$v."\n";
	$v=substr($v,3,20);
	//print __LINE__." -->".$v."\n";
	$v=strtotime($v);
	if (!$ladnie) return $v;
	//print __LINE__." -->".$v."\n";
	$date=date("Y-m-d H:i:s", $v);
	//print __LINE__." -->".$date."\n";
	return $date;
}




function getJRfile($loc,$rem,$remloc) {
	global $dates,$storagelocation;
//	if (!is_file($loc)) $loc=$storagelocation.$loc;
//	$remloc=$remloc;

//	return $loc;
	/*
		loc - local file
		rem - remote file
		remloc - local copy of remote file 
	*/
	$l1=JSlocalFileData($loc,0);
	$l2=remoteFileData($rem,0);
	$l3=localFileData($storagelocation.$remloc,0);
	$dates=array($loc=>date("Y-m-d H:i:s", $l1),$rem=>date("Y-m-d H:i:s", $l2),$remloc=>date("Y-m-d H:i:s", $l3));
	print "\n/*\n1:$l1\n2:$l2\n3:$l3\n*/\n";
	if ($l1===false) {
		print "\n// l1 not FOUND\n";
	}
	print "//";
	if ($l2===false) {
			print ($l1>$l3)?"1loc":"1remloc";
			return ($l1>$l3)?$loc:$remloc;
	} else {
			print ($l1>$l2)?"2loc -remloc do skasowania":"2rem nowszy od loc";
			if ($l1>$l2) {
				@unlink ($storagelocation.$remloc);
				return $loc;
			} else {
				print ($l2>$l3)?"->3rem to download":"->3 local zostaje";
				if ($l2>$l3) {
						//if (!is_dir("Data")) mkdir ("Data");
						print "4a";
						$a=@file_get_contents($rem);
						print "4b";
						if ($a===false) return $remloc;
						print "4c";
						$a1=@file_put_contents($storagelocation.$remloc,$a);
						print "4d";
						if ($a1===false) {
							print "4e";
							unlink ($storagelocation.$remloc);
							print "4f";
							return $loc;
						}
						print "5";
						return $remloc;
				} else {
						print "6";
						return $remloc;
				}
			}
	}
}

function get_version() {
		if (function_exists("exo_get_resstring")) {
			return exo_get_resstring("SPubVersion");
		}
		return false;
}
		


$pass_buduj_plik="0000";
 $up_dir='C:\Users\jrusin\AppData\Local\ExeOutput\UserApplication\{898AB4F9-5B49-40CF-8A8F-DDABD6C12C08}'."\\"; // for windows cmd
 $up_dir="/mnt/c/Users/jrusin/AppData/Local/ExeOutput/UserApplication/{898AB4F9-5B49-40CF-8A8F-DDABD6C12C08}/"; // for windoes subsystem for Linux (WSL)

function buduj_plik1() {
  buduj_plik(1);
}


function buduj_plik2() {
  buduj_plik(2);
  buduj_plik(0);
}

function buduj_plik($small=0) {
 global $up_dir,$pass_buduj_plik;
 $ar=array(
//  "jquery/leaflet.css",
//  "jquery/leaflet-src.js",
//  "jquery/jquery-3.4.1.min.js",
//  "jquery/jquery-3.4.1.js",
//  'jquery/jquery-ui.css',
//  'jquery/jquery-ui.js',
//  "jquery/leaflet.js"

 # mandatory files for demo with toast
  "jquery.toast.css",
  "jquery.toast.js",
  "update.php",
  "main.php",
  "exe_update.js"
 );
 
 $w=array();
 foreach ($ar as $f) {
 $f1=$f;
 if ($f=="dane.js") $f1=($small?"dane.min.js":"dane.js");
  print ($f1==$f)?"$f ":"$f=$f1 ";
  $w[$f]=file_get_contents($f1);
 }
 
 $t=($small)?2:time()-1556643774;
 if ($small==2) {
   $t=3;
   if (isset($w['dane.js'])) unset($w['dane.js']);
   if (isset($w['dane.min.js'])) unset($w['dane.min.js']); 
 }
 $w=array('files'=>$w,'ts'=>$t,'main'=>"main.php");   // put  HERE the YOUR INITIAL PAGE
 file_put_contents("pmo_ver.txt",$t);
 print "d1 ";
 file_put_contents("dane.1.txt",serialize($w));
 print "d2 ";
 encryptFile("dane.1.txt",$pass_buduj_plik,$up_dir."dane.5.txt");
 print "d3 ";
 //file_put_contents("dane.3.js","var _upt=\"".base64_encode(file_get_contents("dane.2.txt")))."\";";
 $fpc=file_put_contents("pmo_dat.txt",base64_encode(file_get_contents($up_dir."dane.5.txt")));
 print "\nfpc:$fpc: ";
 if ($small) {
    
    if ($small==2) {
      rename($up_dir."dane.5.txt","dane.9.txt");
    } else {
      rename($up_dir."dane.5.txt","dane.2.txt");
    }
    
    print "==>dane.".($small==2?"9":"2").".txt ";
 } else { print "==>".$up_dir."dane.5.txt ";}
 unlink("dane.1.txt");
 //unlink("dane.2.txt");
 
 print "end ts=$t\n";
}

function exeout() {
    if (function_exists("exo_getglobalvariable")) {
      return array(
      'SPubTitle'=>exo_get_resstring("SPubTitle"),
      'SPubHomepage'=>exo_get_resstring("SPubHomepage"),
      'SPubAuthor'=>exo_get_resstring("SPubAuthor"),
      'SPubCopyright'=>exo_get_resstring("SPubCopyright"),
      'SPubEMail'=>exo_get_resstring("SPubEMail"),
      'SPubVersion'=>exo_get_resstring("SPubVersion"),
      'SPubProductVer'=>exo_get_resstring("SPubProductVer"),
      'hd_sn'=>exo_return_hescriptcom('UserMain.GetMID|0',0),
      'manuf_id'=>exo_return_hescriptcom('UserMain.GetMID|1',0),
      /*
        https://www.exeoutput.com/help/scriptreference
        function GetMID( i:integer):String
        begin                        
          Result := GetManualHardwareID(i); 
        end;  

        procedure OnStartMainWindow;
        begin
          // When the main window is going to be displayed (just before the homepage is shown). 
          //SetUIProp("crm", "ProxyServer", """plwarad-proxy001.emea.nsn-net.net""");SetUIProp("crm", "ProxyPort", "8080");
          //SetUIProp("crm", "ProxyUsername", """yser""");SetUIProp("crm", "ProxyPassword", """pass""");

          SetUIProp("crm", "ProxyType", "2");SetUIProp("crm", "ProxyScheme", "0");       
          NavigateCommand(13); // Update preferences
        end;

      */
      'cpu_id'=>exo_return_hescriptcom('UserMain.GetMID|2',0),
      'hd_id'=>exo_return_hescriptcom('UserMain.GetMID|3',0),
      'pub'=>exo_getglobalvariable("HEPubStorageLocation",0),
      'src'=>exo_getglobalvariable("_danesrc",0),
      'ts' =>exo_getglobalvariable("_danets",0),
      'tmp'=>exo_getglobalvariable("HEPubTempPath",0),
      'exe'=>exo_getglobalvariable("HEPHPDataPath",0));
    }
    return false;
    
}


function pobralem_plik_update() {
//  header("Content-Type: text/plain");

  if (isset($_POST) && isset($_POST["dane4"]) /*&& exeout() */)  {
    $o=exeout();
   // die("OOO");
   // print_r($o);
    if ($o['pub']) {
      //file_put_contents($o['pub'].'dane.5.txt',$_POST['dane4']);
      
      $f=fopen($o['pub'].'dane.5.txt',"wb") or die("ERROR:".__LINE__.$o['pub']);
      fwrite($f,base64_decode($_POST['dane4']));
      fclose($f);
      
      die("OK");
    }
  }
  die("ERROR:".__LINE__);
}

function kasuj_plik_update() {
    $o=exeout();
    if ($o['pub']) {
      @unlink($o['pub'].'dane.5.txt');
      exo_runhescriptcom ( "Global.HEExitPublication" );
      return 1;
      $f=fopen($o['pub'].'dane.5.txt',"wb") or die("ERROR:".__LINE__.$o['pub']);
      fwrite($f,'');
      fclose($f);
      return 1;
    }
    return 0;
}

function update_server1($proxy='',$possible_proxy=1) {
  update_server($proxy,0);
}

function update_server($proxy='',$possible_proxy=1) {

  $ip=exec("ip route get 1 | awk '{print \$NF;exit}'");
  //die($ip);

 global $up_dir,$pass_buduj_plik;
// print __LINE__.";";
  //stream_context_set_default(['http'=>['proxy'=>'proxy-host:proxy-port']]);
 // if (!is_file($up_dir."dane.9.txt")) {
 //   buduj_plik2();
 // }    
 // if (!is_file($up_dir."dane.5.txt")) {
 //   buduj_plik();
 // }    
      if (is_file($up_dir."dane.5.txt")) {
        $g=unserialize(decryptFileMem($up_dir."dane.5.txt",$pass_buduj_plik));
        $data=array('f1'=>$g['ts']);
        unset($g);
        $data['f2']=base64_encode(file_get_contents($up_dir."dane.5.txt"));
        if (false && is_file("dane.9.txt")) {
          $data['f3']=base64_encode(file_get_contents("dane.9.txt"));
          print "f3 added";
        }
//        header('Content-Type: application/json');
//        die (json_encode($ver));
  


// https://stackoverflow.com/questions/5647461/how-do-i-send-a-post-request-with-php
        $url = 'https://www.jr.pl/projects/exeoutput_updater/pmo.php?upp';
//        $data = array('f1' => 'value1', 'f2' => 'value2');
//192.168.2.31 10.137.160.12

        // use key 'http' even if you send the request to https://...
        $options = array(
            "ssl"=>array(
              "verify_peer"=>false,
              "verify_peer_name"=>false,
            ),
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
//                'timeout' => 300,
                
                'content' => http_build_query($data)
            )
        );
        if ($possible_proxy && preg_match('/^10\\./',$ip)) $options['http']['proxy']='proxy.default.in.case.pc.is.in.net10.x.x.x:8080';
        if ($proxy) $options['http']['proxy']=$proxy;
        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        if ($result === FALSE) { /* Handle error */ die ("ERROR");}

        var_dump($result);
        print "uploaded ts=".$data['f1']." fs=".filesize($up_dir."dane.5.txt")."\n";
        //unlink($up_dir."dane.5.txt");
      
    }
}



function get_file() {

 global $pass_buduj_plik; 
 file_put_contents("dane.5.txt",base64_decode(file_get_contents("dane.4.txt")));
 
 $g=unserialize(decryptFileMem("dane.5.txt",$pass_buduj_plik));
 if (isset ($g['main']) && isset($g['ts'])) {
    //plik jest ok
    if (isset($g['files'])) 
      foreach($g['files'] as $f=>$plik) {
          print "$f";
          //file_put_contents("wow/".$f,$plik);
      }
 }
}


function update_files($up=0) {
 ob_start();$g=array();
 print __LINE__."update_files\n";
 global $pass_buduj_plik; 
 $o=exeout();
 if (!($o && $o['pub'] && $o['exe']))  {
  die("ERROR:".__LINE__);
 }    
 print __LINE__."\n";
 if ($up==2) kasuj_plik_update();
 
 if (!$up) {
 print __LINE__."\n";
 
    if (is_file($o['pub']."dane.5.txt")) {
        print __LINE__."\n";
        $g=unserialize(decryptFileMem($o['pub']."dane.5.txt",$pass_buduj_plik));
		$g['src']='upgraded';
    }
 }
  print __LINE__."\n";

 if (!(isset ($g['main']) && isset($g['ts']) && isset($g['files']))) {
  print __LINE__."\n";
       $g=unserialize(decryptFileMem($o['exe']."dane.2.txt",$pass_buduj_plik));
	   $g['src']='initial';
  print __LINE__."\n";
        //exo_setglobalvariable('DefWinTitle','dane.2',false);
  print __LINE__."\n";
 }
 
 print __LINE__."\n";
 if (isset ($g['main']) && isset($g['ts'])) {
    //plik jest ok
    print __LINE__."\n";
    if (isset($g['files'])) 
      foreach($g['files'] as $fn=>$plik) {
          print "$fn<br/>";
          $f=fopen($o["exe"].$fn,"w");
          fwrite($f,$plik);
          fclose($f);
      }
  $o['ts']=$g['ts'];$o['src']=$g['src'];$_SERVER['exeoutput']=$o;
	exo_setglobalvariable('_danesrc',$g['src'],false);
	exo_setglobalvariable('_danets',$g['ts'],false);
	exo_setglobalvariable('_danex',json_encode($_SERVER),false);
  if (1) {
    ob_start();
    print $g['ts'].":".$g['main'].":".join(',',array_keys($g['files']));
//    print_r($_SERVER);
    file_put_contents($o['pub']."dane.5a.txt",ob_get_contents());
    ob_end_clean();
   }


//	$f=fopen($o["exe"].$fn,"w");
//    fwrite($f,$plik);
//    fclose($f);
  
  ob_clean();
  include_once $o["exe"].$g['main'];
  //print $o["exe"]."$g['main'];
  die();
  }
  die("ERROR IN FILE");
}

function do_all() {
	buduj_plik();
	update_server();
}

function build_file() {
	buduj_plik();
}

function build_file1() {
	buduj_plik1();
}

function build_file2() {
	buduj_plik2();
}

function delete_file() {
	kasuj_plik_update();
}


function help() {
  $info=array(
    'do_all' => "build file and update server",
    'build_file' => "create update file",
    'build_file1' => "create update file for initial update (from version 2) dane.2.txt and normal file",
    'update_server' => "update file to server",
    'update_server1' => "update file to server - force no proxy",
    'delete_file' => "delete LOCAL update file. NEXT app RUN on the compiled version. Restart needed.",
    "help"=>"display this info"
  );
  foreach ($info as $i1=>$i2)
     print "  ".$_SERVER['PHP_SELF']." $i1  => $i2\n";
}

function info() {
  phpinfo();
  
}


//exo_getglobalvariable


//buduj_plik();


if (isset($argv[1])) $argv[1]();
if (isset($_POST) && isset($_POST['kasuj_plik_update'])) kasuj_plik_update();
?>