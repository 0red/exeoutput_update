<html>
<head>
<!-- prerequisites -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js" ></script>
<link rel="stylesheet" type="text/css" href="jquery.toast.css" />
<script src="jquery.toast.js" ></script>

<!-- main file -->
<script src="exe_update.js" ></script>
<script>
window.addEventListener("load", after_load);

async function after_load() {
  delay(2000).then((x)=>{jr_check_update('https://www.jr.pl/projects/exeoutput_updater/pmo.php');});
  let d = document.getElementById("show");
  let x;
  x=await show_version();
  $("#show").html(x.map(y=>y.join(":")).join("\n"));
}

</script>
</head>
<body><pre id='show'><?php


?></pre>
<h1>Hi!! THIS IS AN UPDATED APP !!!</h1>
<hr/>

<a href="main.php">RELOAD main.php</a><br/>
<a href="#" onclick="jr_check_update('https://www.jr.pl/projects/exeoutput_updater/pmo.php');" >Check update</a><br/>
<a href="exe_start.php?noupdate=2" >Delete update</a><br/>
<a href="exe_start.php?noupdate=1" >Reload w/o updates</a><br/>
Enjoy the update !!!


</body></html>