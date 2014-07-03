
function GetClock(){
d = new Date();
nhour  = d.getHours();
nmin   = d.getMinutes();
if(nmin <= 9){nmin="0"+nmin}


document.getElementById('clockbox').innerHTML=""+nhour+":"+nmin+"";
setTimeout("GetClock()", 1000);
}
window.onload=GetClock;
