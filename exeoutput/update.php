<?php
//die ("UPDATE");
ini_set("post_max_size",'50M');
if (isset($_POST) && isset($_POST["dane4"])) {
  include_once "enc_data.php";
  pobralem_plik_update();
  die("OK");
}

if (isset($_POST) && isset($_POST["get5"])) {
  include_once "enc_data.php";
  $o=exeout();
    if ($o['pub']) {
      if (is_file($o['pub']."dane.5.txt")) {
        $g=unserialize(decryptFileMem($o['pub']."dane.5.txt",$pass_buduj_plik));
        $ver=array('f1'=>$g['ts']);
        unset($g);
        $ver['f2']=base64_encode($o['pub']."dane.5.txt");
        header('Content-Type: application/json');
        die (json_encode($ver));
      }
    }
  die("ERROR");
}


if (isset($_POST) && isset($_POST["file_wynik"]) && isset($_POST["dane"])) {
  print "received file - odebrano plik:".$_POST["file_wynik"]." length - dlugosc:".strlen($_POST["dane"]);
  print "<br/>\n".$_POST["dane"];
}

?>