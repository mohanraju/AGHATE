/**
 * DHTML date validation script. Courtesy of SmartWebby.com (http://www.smartwebby.com/dhtml/)
 */
// Declaring valid date character, minimum year and maximum year
var dtCh= "/";
var minYear=1900;
var maxYear=2100;

function isInteger(s){
	var i;
    for (i = 0; i < s.length; i++){   
        // Check that current character is number.
        var c = s.charAt(i);
        if (((c < "0") || (c > "9"))) return false;
    }
    // All characters are numbers.
    return true;
}

function stripCharsInBag(s, bag){
	var i;
    var returnString = "";
    // Search through string's characters one by one.
    // If character is not in bag, append to returnString.
    for (i = 0; i < s.length; i++){   
        var c = s.charAt(i);
        if (bag.indexOf(c) == -1) returnString += c;
    }
    return returnString;
}

function daysInFebruary (year){
	// February has 29 days in any year evenly divisible by four,
    // EXCEPT for centurial years which are not also divisible by 400.
    return (((year % 4 == 0) && ( (!(year % 100 == 0)) || (year % 400 == 0))) ? 29 : 28 );
}
function DaysArray(n) {
	for (var i = 1; i <= n; i++) {
		this[i] = 31
		if (i==4 || i==6 || i==9 || i==11) {this[i] = 30}
		if (i==2) {this[i] = 29}
   } 
   return this
}

function isDate(dtStr){
	var daysInMonth = DaysArray(12)
	var pos1=dtStr.indexOf(dtCh)
	var pos2=dtStr.indexOf(dtCh,pos1+1)
	var strDay=dtStr.substring(0,pos1)
	var strMonth=dtStr.substring(pos1+1,pos2)
	var strYear=dtStr.substring(pos2+1)
	strYr=strYear
	
	if (dtStr=="") return true;
	if (strDay.charAt(0)=="0" && strDay.length>1) strDay=strDay.substring(1)
	if (strMonth.charAt(0)=="0" && strMonth.length>1) strMonth=strMonth.substring(1)
	for (var i = 1; i <= 3; i++) {
		if (strYr.charAt(0)=="0" && strYr.length>1) strYr=strYr.substring(1)
	}
	month=parseInt(strMonth)
	day=parseInt(strDay)
	year=parseInt(strYr)
	if (pos1==-1 || pos2==-1){
		alert("le format du date doit être  : JJ/MM/AAAA")
		return false
	}
	if (strMonth.length<1 || month<1 || month>12){
		alert("Mois invalide, veuillez le corriger ")
		return false
	}
	if (strDay.length<1 || day<1 || day>31 || (month==2 && day>daysInFebruary(year)) || day > daysInMonth[month]){
		alert("Jour invalide, veuillez le corriger ")
		return false
	}
	if (strYear.length != 4 || year==0 || year<minYear || year>maxYear){
		alert("veuillez entrer l'année  4 chiffres entre"+minYear+" et "+maxYear)
		return false
	}
	if (dtStr.indexOf(dtCh,pos2+1)!=-1 || isInteger(stripCharsInBag(dtStr, dtCh))==false){
		alert("veuillez taper un valide date ")
		return false
	}
return true
}

function CheckDate(obj){
	var dt=obj;
	var chk_dt=dt.value;
	
	if (chk_dt.length==8){
		chk_dt=chk_dt.substr(0,2) + "/" + chk_dt.substr(2,2) + "/" + chk_dt.substr(4,4);
		obj.value=chk_dt;
	}	
	if (chk_dt.length==6){
		chk_dt=chk_dt.substr(0,2) + "/" + chk_dt.substr(2,2) + "/20" + chk_dt.substr(4,2);
		obj.value=chk_dt;
	}

	 
	if (isDate(dt.value)==false){
		return false
	}
    return true
 }

 
//============================
// les scripts par mohan
//============================
// Check format du float ou integer
//============================

function CheckNumber(obj,taille,dec){
	var num=obj.value;
	var max=0;
	var int_max=0;
	var max_val=0
	// convert into integer
	taille=parseInt(taille);
	dec=parseInt(dec);	
	if (num=="") return true;
	
	// check format defnit dans le formulaire
	if (taille < dec){
		alert("Invalide définition taille et déc. dans le format saisi !!!!" + taille + "," + dec);
		return false;
	}
	
	// check the integer part
	if (dec >0){
		intmax=taille - (dec + 1);
	}else{
		intmax= taille  ;
	}
	max_val = Math.pow(10,intmax) 
	msg_max=(max_val );	
	if (dec ==0)msg_max=(max_val);
	if (dec ==1)msg_max=(max_val - 0.1);
	if (dec ==2)msg_max=(max_val - 0.01  );
	if (dec ==3)msg_max=(max_val - 0.001   );
	if (dec ==4)msg_max=(max_val - 0.0001   );

	if (num > (max_val - 0.00001) ){
		alert("Maximum limit dépasée !!!! :" + num + "\nMax authorisée : " + msg_max   );
		return false;
	}
	
	
	if((num * 1) != parseFloat(num)){
		alert("foramt  invalide !!!!" + num);
		return false;		
	}

	num= parseFloat(num);
	val_ok=num.toFixed(dec);
	obj.value=val_ok;
	return true;
	
}

//============================
// Check format du TIME
//============================
function FormatTime(obj,max_heure){
	var max_heure;
	var heure=obj.value;
	var hh='00';
	var mm='00';
	var fmt=true;
	var hhmm;

	if (max_heure==null)max_heure=23;
	
	hhmm =heure.split(':');	
	hh=hhmm[0];
	
	if (hhmm[1]!=null){
		mm=hhmm[1];
	}else{
		fmt=true;
		switch (heure.length) {
			case 1:
				hh='0' +heure;
				mm='00';
				break;
			case  2:
				hh=heure;
				mm ='00';
				break;
			case  3:
				hh=heure.substr(0,3);
				mm ='00';
				break;
			case  4:
				hh=heure.substr(0,2);
				mm=heure.substr(2,2);
				break;
			case  5:
				hh=heure.substr(0,3);
				mm=heure.substr(3,2);
				break;
			default: 
				fmt=false;
				break;
		}
		

	}

	// vérification d'heure et du min
	if (fmt==true){	
		
		if ((( hh > max_heure ) || (hh < 0 ) ) || (isInteger(hh)==false) ) {
			alert("l'heure invalide (limité de 0 à "+ max_heure + ")!!! : " +hh);
			fmt=false;
			return false;	
		}else{
			fmt=true;
		} 
		if ((( mm > 59 ) || (mm < 0 ) ) || ((isInteger(mm)==false)) ) {
			alert("le minute invalide (limité de 0 à 59)!!! : " +mm);
			fmt=false;
			return false;	
		}else{
			fmt=true;
		}		
	}else{
			alert("le format d'heure invalide !!! : " +heure);
			fmt=false;
			return false;	
	}
	// on retoure le heure correct avec le formatage 
	if (fmt=true){
		obj.value=hh +':' + mm;
	}
 }	


function sisInteger (s)  {
		var x = s;
		if (x == parseInt(x) && x == parseFloat(x)) {
			return true;
		} else if (x == parseFloat(x)) {
			return true;
		} else {
			return false;
		}
   }

