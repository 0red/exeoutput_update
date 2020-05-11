<?php
include_once "enc_data.php";
update_files((isset($_GET) && isset($_GET["noupdate"]))?$_GET["noupdate"]:0);
print "<HR/>END";
?>