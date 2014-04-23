// Calendar 
// date format is DD/MM/YYYY
// Author Mohanraju SOUPRAYANE
// le 18/10/05
// to call the function PrepareCal('your_element_name')
// exemple javascript:PrepareCal('From_date')

var OpenCalendar;
var Calc;
var CreateCalendar;
var Aujourdhui=new Date();	

var MoisNom=new Array("Janvier","Février","Mars","Avril","Mai","June","Juillet","Août","Septembre","Octobre","Novembre","Decembre");
var MonthDays = new Array(31,28,31,30,31,30,31,31,30,31,30,31);
var SemaineNom=["Lundi","Mardi","Mercredi","Jeudi","Vendredi","Samedi","Dimanche"];	
var NbrWeekChar=1;
var calTop=200;
var calLeft=200;
var SemaineBgClolor="#66FFCC";	
var JourBgColor="#CCFFFF";


function PrepareCal(Date_Control){Calc=new Calendar(Aujourdhui);if (Date_Control!=null)	Calc.Ctrl=Date_Control;	exDateTime=document.getElementById(Date_Control).value;	OpenCalendar=window.open("","DateTimePicker","toolbar=0,status=0,menubar=0,fullscreen=no,width=220,height=230,resizable=0,top="+calTop+",left="+calLeft);	CreateCalendar=OpenCalendar.document;	WriteCal();}
function IncYear(){	Calc.Year++;}Calendar.prototype.IncYear=IncYear;
function DecYear(){	Calc.Year--;}Calendar.prototype.DecYear=DecYear;
function DecMois(){	Calc.Month--;if(Calc.Month < 0 ){Calc.Month=11;	Calc.Year--;} }Calendar.prototype.DecMois=DecMois;
function IncMois(){	Calc.Month++;if(Calc.Month > 11){Calc.Month=0; Calc.Year++;} }Calendar.prototype.IncMois=IncMois;
		
function Calendar(CurDate,Date_Control){this.Date=CurDate.getDate();this.Month=CurDate.getMonth();this.Year=CurDate.getFullYear();this.MyWindow=OpenCalendar;this.Ctrl=Date_Control;}
	
function WriteCal(){	
	c_jour=Calc.Date;
	c_mois=Calc.Month;
	c_annee=Calc.Year;
	

	
	StartOfMonth_1 = new Date(c_annee,c_mois,1);
	StartOfMonth = StartOfMonth_1.getDay()-1;
	EndOfMonth=MonthDays[c_mois];
	//check leep year	
	if((c_mois==1)&&((c_annee%4)==0)){
		if ((c_annee%100==0) && (c_annee%400)!=0)
			EndOfMonth=28;
		else
			EndOfMonth=29;
	}
	
	/*if((c_mois==1)&&((c_annee%4)==0)){
		if(((c_annee%100)!==0) && ((c_annee%400)=0)){
			EndOfMonth=29;
		}
	}*/	
	//alert(EndOfMonth);	
	var url_date='';	
	//ecrire le tableau ici
	Chaine="<html><head><title>Calendar</title><script>var CalOpener=window.opener;</script></head><body leftmargin='0px' topmargin='0px' marginwidth='0px' marginheight='0px'>";
	Chaine+="<form name='cal' >";
	Chaine+="<table  border='0' cellspacing='7' style='table-layout:fixed;z-index:1000;position:absolute;top:0;left:0;'> ";
	Chaine+="<tr height='21' >";
	Chaine+="<td bgcolor='#4682B4' colspan='7'><table cellspacing='0' cellpadding='0' border='0' width='100%'><tr>"
	Chaine+="	<td width='10%' ><a href=\"javascript:CalOpener.Calc.DecYear();CalOpener.WriteCal()\"><img src='../commun/images/prev_year.gif' width='16' height='16' border='0' alt='Année président'></a> </td>";
	Chaine+="	<td width='10%' ><a href=\"javascript:CalOpener.Calc.DecMois();CalOpener.WriteCal()\"><img src='../commun/images/prev.gif' width='16' height='16' border='0' alt='Mois président'></a> </td>";
	Chaine+="	<td width='60%' align='center'><font color='#ffffff'>"+MoisNom[c_mois]+" - "+c_annee+ "</font></td>";
	Chaine+="	<td width='10%' ><a href=\"javascript:CalOpener.Calc.IncMois();CalOpener.WriteCal()\"><img src='../commun/images/next.gif' width='16' height='16' border='0' alt='Mois suivante'></a> </td>";
	Chaine+="	<td width='10%' ><a href=\"javascript:CalOpener.Calc.IncYear();CalOpener.WriteCal()\"><img src='../commun/images/next_year.gif' width='16' height='16' border='0' alt='Année suivante'></a> </td>";
	
	
	Chaine+="</tr></table> </td></tr >";

	Chaine+="<tr height='21' >";
	for(weekNum=0;weekNum<=6;weekNum++){
		Chaine+="<td align='center' width='22' style='font-family:Tahoma;font-size:x-small;font-weight:bold;'>"+SemaineNom[weekNum].substr(0,NbrWeekChar)+"</td>";
     }
	Chaine+="</tr>";
	var jour=0;
	for(weekNum=0;weekNum<=6;weekNum++){
		Chaine+="<tr height='21' > ";
		for(ColNum=0;ColNum<=6;ColNum++){
			tr=0;
			if((weekNum==0)&&(StartOfMonth > ColNum)){
				Chaine+="<td>&nbsp;</td>";		
			}else{			
				jour++;
				if(jour > EndOfMonth)break;
				c_dt=((jour < 10 ) ? '0'+jour :jour) + "/" + ((c_mois < 9 ) ? '0'+(c_mois+1) :c_mois+1)+"/"+c_annee; 
				url="<a href=javascript:CalOpener.document.getElementById('"+Calc.Ctrl+"').value='"+c_dt+"';window.close();>";
				
				if(jour == c_jour){
					url="<a href=javascript:CalOpener.document.getElementById('"+Calc.Ctrl+"').value='"+c_dt+"';window.close();>";
				    Chaine+="<td bgcolor='yellow' align='center' width='22' style='font-family:Tahoma;font-size:x-small;font-weight:bold;'>"+url+jour+"</a></td>";
				}else{
					Chaine+="<td align='center' width='22' style='font-family:Tahoma;font-size:x-small;font-weight:bold;'>"+url+jour+"</a></td>";
			}	
			}	 
	    }
	    Chaine+="</tr>";
	    if(jour > EndOfMonth)break;
 	}
 	Chaine+="</table></form></body></html>";
 	CreateCalendar.open();
	CreateCalendar.writeln(Chaine);
	CreateCalendar.close();
}	

