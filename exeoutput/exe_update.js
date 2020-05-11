/**
  file created by Jacek Rusin
*/

/**

window.addEventListener("load", after_load);

function after_load() {
  delay(2000).then((x)=>{jr_check_update();});

}

*/

if (window.location.href.match(/noupdate=2/) && exeoutput) exeoutput.RunHEScriptCode('ExitPublication;');


var opt_update={};
 

async function show_version() {
  let w=await get_exeoutput().then(opt => {
   // console.log(opt);
    let c=[["",""]];
    c.push(["user",opt.USERNAME]);
    c.push(["domain",opt.USERDOMAIN]);
    c.push(["datafile ts",opt.exeoutput.ts]);
    c.push(["datafile" ,jr_update_time(opt.exeoutput.ts)]);
    if ($ && $.fn && $.fn.jquery ) c.push(["jquery ver" ,$.fn.jquery]);
//    if ($ && $.ui && $.ui.version) c.push(["jqueryui ver" ,$.ui.version]);
//    if (L && L.version) c.push(["Leaflet ver" ,L.version]);
    c.push(["SPubVersion",opt.exeoutput.SPubVersion]);
    c.push(["SPubProductVer",opt.exeoutput.SPubProductVer]);
    c.push(["path",opt.exeoutput.pub]);
    //console.log(c);
    return c;
  });
  return w;
}

async function jr_check_update(url,file_wynik,show_noupdate_info=true) {
  url = url || 'https://www.<server>.com/pmo.php';
  file_wynik = file_wynik || "pmo.ver";
  if (window.location.href.match(/noupdate=2/) && exeoutput) exeoutput.RunHEScriptCode('ExitPublication;');

   
  //if (typeof exeoutput !== 'undefined') 
   get_exeoutput().then(opt => {
    ajax_post(url+'#pmo_ver.php',{o:'v',d:opt.USERDOMAIN,u:opt.USERNAME,v:opt.exeoutput.SPubVersion,c:opt.exeoutput.cpu_id,t:opt.exeoutput.ts}).then (net => {
      console.log(net);
      opt_update=opt;
      get_exeoutput('_danets','').then ( loc => {
        console.log (net+'>'+loc);
        if (net==1 && opt.QUERY_STRING!="noupdate=2") {
            window.location.href=""+opt.SCRIPT_NAME+"?noupdate=2";
            console.log("127 noupdate=2");
            if (exeoutput) exeoutput.RunHEScriptCode('ExitPublication;');
            return ;
        }
        if ( net>loc ) {
           if (window.confirm('Do You want to download the update and restart application?\nVersion: '+loc+" will be upgraded to: "+net
              +"\n"+jr_update_time(loc)+" (actual)\n"+jr_update_time(net)+" (proposed)")) {
          //    alert('get');
              info("Update procedure is starting.<br/>Please be patient...");
              ajax_post(url+'#pmo_dat.txt',{o:'u',d:opt.USERDOMAIN,u:opt.USERNAME,v:opt.exeoutput.SPubVersion,c:opt.exeoutput.cpu_id,t:opt.exeoutput.ts}).then (upp => {
                 //alert('downloaded');
                 ajax_post('update.php',{'dane4':upp}).then (kon => {
                    window.location.reload();
                    console.log("137 reload");
                 });
              });
           } else {
              delay(3000).then(info ('Update not performed.<br/>Actual version: '+opt_update.exeoutput.ts+" ("+opt_update.exeoutput.src+')<br/>'+jr_update_time(opt_update.exeoutput.ts)+net,'Update status.'));
              delay(1000/*1sek*/*3600).then(x => jr_check_update(url,null,false));
           }
        } else {
          if (show_noupdate_info)
              delay(3000).then(info ('No update data avaiable on the server.<br/>Actual version: '+opt_update.exeoutput.ts+" ("+opt_update.exeoutput.src+')<br/>'
                  +jr_update_time(opt_update.exeoutput.ts),'Update status.'));
              delay(1000/*1sek*/*3600).then(x => jr_check_update(url,null,false));
        }
      });
    });
    
 },x=>{}); 
}

const info1=(m="no message",t="no title") =>{ window.alert((t+"\n"+m).replace(/\<br\/?\>/gi,"\n"));};



function jr_update_time(ts) {
  ts=1*ts;
  if (ts<10) return "No update";
  let d=new Date((1556643774+ts)*1000);
  return d.toISOString().replace(/T.*/,"")+" "+d.toTimeString().replace(/ .*/,"");
  //return d.toISOString().replace(/T/, ' ').replace(/\..+/, '');
}


const ajax_get = (url,data,typ) => new Promise ( (resolve, reject) => {
      $.ajax({
         method:"GET",
         url:url,
         data:data || {},
         dataType: typ || 'text',
         success: (dane,status,xhr) => resolve(dane,status,xhr),
         error: (xhr,status,errorText) => reject(xhr,status,errorText)
      })
   }   
  );

const ajax_post = (url,data,typ) => new Promise ( (resolve, reject) => {
      $.ajax({
         method:"POST",
         url:url,
         data:data || {},
         dataType: typ || 'text',
         success: (dane,status,xhr) => resolve(dane,status,xhr),
         error: (xhr,status,errorText) => reject(xhr,status,errorText)
      })
   }
  );
  

 
function get_exeoutput(exo_global,defaultvar) {
  // get_exeoutput().then( x=> console.log(x)).then( () => console.log('ala'))
  if (typeof exeoutput == 'undefined') return  Promise.reject(new Error('no exeoutput'));
  exo_global = exo_global || '_danex';
  defaultvar = defaultvar || '';
  return new Promise((resolve, reject) => {
    exeoutput.GetGlobalVariable(exo_global, defaultvar, w => resolve(JSON.parse(w)) ); 
  });
}
 

const delay =  ms =>  new Promise(yea => setTimeout(yea, ms)); 



function info(msg,header='',hideAfter=7000,stack=5) {
  console.log("TOAST: "+((header)?"--"+header+"-- ":"")+msg);
      $.toast({
							text: msg,
							heading: header, // Optional heading to be shown on the toast
							showHideTransition: 'slide', // fade, slide or plain
							allowToastClose: true, // Boolean value true or false
							hideAfter: hideAfter, // false to make it sticky or number representing the miliseconds as time after which toast needs to be hidden
							stack: stack, // false if there should be only one toast at a time or a number representing the maximum number of toasts to be shown at a time
							position: 'bottom-right', // bottom-left or bottom-right or bottom-center or top-left or top-right or top-center or mid-center or an object representing the left, right, top, bottom values				
							textAlign: 'left',  // Text alignment i.e. left, right or center
							loader: true,  // Whether to show loader or not. True by default
							loaderBg: '#9EC600',  // Background color of the toast loader
            });
}