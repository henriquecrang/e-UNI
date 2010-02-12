<!--
var no_stylesheet = 5;  /* This value needs to be set to the number of normal style sheets i.e. those not affecting the accessibility functions */
if (document.all){ // Explorer
 no_stylesheet = no_stylesheet +1;
 }

var size_ss_start = no_stylesheet;
var font_ss_start = no_stylesheet + 5; /* this value (+ 5) needs to be how many dans stylesheets in total */







function fontsizeup() {
  active = getActiveStyleSheet();
  switch (active) {
    case 'Small' : 
	alert("da small a default ");
      setActiveStyleSheet('Default');
      createCookie("style", 'Default', 365);
      break;
    case 'Default' : 
	alert("da default a large ");
      setActiveStyleSheet('Large');
      createCookie("style", 'Large', 365);
 	  break;
    case 'Large' :
	alert("da large a larger "); 
      setActiveStyleSheet('Larger');
      createCookie("style", 'Larger', 365);
      break;
    case 'Larger' : 
	alert("da larger a massimo");
      setActiveStyleSheet('Largest');
      createCookie("style", 'Largest', 365);
      break;
    case 'Largest' :
	 alert("piu si cosi no ");
      break;
    default :
      setActiveStyleSheet('Default');
	  createCookie("style", 'Default', 365);
      break;
  }
}

function fontsizedown() {
  active = getActiveStyleSheet();
  switch (active) {
    case 'Largest' : 
	alert("da massimo a larger");
      setActiveStyleSheet('Larger');
	  createCookie("style", 'Larger', 365);
      break;
    case 'Larger' :
	 
      alert("da larger a large");
	  setActiveStyleSheet('Large');
	  createCookie("style", 'Large', 365);
      break;
    case 'Large' : 
	  alert("da large a default");
      setActiveStyleSheet('Default');
	  createCookie("style", 'Default', 365);
      break;
    case 'Default' :
	   alert("da default a small");
      setActiveStyleSheet('Small');
	  createCookie("style", 'Small', 365);
      break;
    case 'Small' :
	   alert("meno di cosi no ");
       break;
    default :
      setActiveStyleSheet('Default');
	  createCookie("style", 'Default', 365);
      break;
  }
}

function setActiveStyleSheet(title) {
  var i, a, arraylen, main;
  //arraylen = document.styleSheets.length -3;
  arraylen = font_ss_start;
  for(i=size_ss_start; i<arraylen; i++) {
	a = document.getElementsByTagName("link")[i];  
    //if(a.getAttribute("rel").indexOf("style") != -1 && a.getAttribute("title") && a.getAttribute("href").indexOf("style") != -1) {
	  if(a.getAttribute("rel").indexOf("style") != -1 && a.getAttribute("title")) {
      a.disabled = true;
      if(a.getAttribute("title") == title) a.disabled = false;
	}
  }
}

function getActiveStyleSheet() {
  var i, a, arraylen, main;
  //arraylen = document.styleSheets.length -3;
  arraylen = font_ss_start;
  //alert("arraylen is "+arraylen);
  for(i=size_ss_start; i<arraylen; i++) {
	a = document.getElementsByTagName("link")[i];  
    //if(a.getAttribute("rel").indexOf("style") != -1 && a.getAttribute("title") && a.getAttribute("href").indexOf("style") != -1 && !a.disabled) return a.getAttribute("title");
	if(a.getAttribute("rel").indexOf("style") != -1 && a.getAttribute("title") && !a.disabled) return a.getAttribute("title");
  }
  return null;
}


function getPreferredStyleSheet() {
  return ('Default');
}

/* Background colour functions */

function getCurrentColour() {
	var CurrColour;
	CurrColour = document.bgColor;
	if(CurrColour != "") {return CurrColour;
  }
  CurrColour = '#ffffff';
  return CurrColour;
}

function setActiveColour(colour) {
	if (colour == null) { colour = '#ffffff'; }
	document.bgColor=colour;
    createCookie("colour", colour,365);
}

/* Font type functions */

function setActiveFont(id) {
  var i, b, main;
  for(i=font_ss_start; (b = document.getElementsByTagName("link")[i]); i++) {
    	if(b.getAttribute("rel").indexOf("style") != -1 && b.getAttribute("title")) 
		{
			b.disabled = true;
			if(b.getAttribute("title") == id) { b.disabled = false; createCookie("fonttype", id,365); }
    	}
  }
}

function getActiveFont() {
  var i, b;
  for(i=font_ss_start; (b = document.getElementsByTagName("link")[i]); i++) {
    if(b.getAttribute("rel").indexOf("style") != -1 && !b.disabled) return b.getAttribute("title");
  }
  return null;
}

function getPreferredFont() {
  return ('arial');
}


/* Cookie Functions */

function createCookie(name,value,days) {
  if (days) {
    var date = new Date();
    date.setTime(date.getTime()+(days*24*60*60*1000));
    var expires = "; expires="+date.toGMTString();
  }
  else expires = "";
  document.cookie = name+"="+value+expires+"; path=/";
}

function readCookie(name) {
  var nameEQ = name + "=";
  var ca = document.cookie.split(';');
  for(var i=0;i < ca.length;i++) {
    var c = ca[i];
    while (c.charAt(0)==' ') c = c.substring(1,c.length);
    if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
  }
  return null;
}

/*Initiate accessibility functions*/

function initFontScaler() {
	
	var fontscale = readCookie("style");
	var colour = readCookie("colour");
	var font = readCookie("fonttype");
	
	var title = fontscale ? fontscale : getPreferredStyleSheet();
	var id = font ? font : getPreferredFont();
		
	setActiveStyleSheet(title);
	setActiveFont(id);
	setActiveColour(colour);
}


window.onunload = function(e) {
  var title = getActiveStyleSheet();
  var colourbg = getCurrentColour();
  var fontused = getActiveFont();
  createCookie("colour", colourbg,365);
  createCookie("style", title, 365);
  createCookie("fonttype", fontused,365);
}


/*Set up controls for accessibility items */

function createControls() {
	
if (document.getElementsByTagName) {
	var col1 = '#ffffff';
	var col1a = "'"+col1+"'";
	var col2 = '#ffffe0';
	var col2a = "'"+col2+"'";
	var col3 = '#ccffff';
	var col3a = "'"+col3+"'";
	var col4 = '#cfffe8';
	var col4a = "'"+col4+"'";
	var col5 = '#ffddee';
	var col5a = "'"+col5+"'";
	var col6 = '#ddddff';
	var col6a = "'"+col6+"'";
	var col7 = '#ccffcc';
	var col7a = "'"+col7+"'";
	var col8 = '#cceedd';
	var col8a = "'"+col8+"'";
	var col9 = '#ffddb3';
	var col9a = "'"+col9+"'";
	var col10 ='#c9dfff';
	var col10a = "'"+col10+"'";
	var col11 ='#f0f0f0';
	var col11a = "'"+col11+"'";
	var text1 = 'arial';
	var text1a = "'"+text1+"'";
	var text2 = 'tahoma';
	var text2a = "'"+text2+"'";
	var text3 = 'comic';
	var text3a = "'"+text3+"'";
	var text5 = 'trebuchet';
	var text5a = "'"+text5+"'";
	
	document.write('<form action="GET" method="get" id="textSizer" name="accessibility bar">');
   	document.write('<div>');
	
	document.write('<span class="textsizer_text"> grandezza </span>');
    document.write('<input alt="Increase font size" style="width: 56px; height: 23px" onclick="javascript:fontsizeup(); return false;" src="http://www.istruzioneveneto.it/unimoodle/theme/1410provatema/text_plus.jpg" type="image"/>');
    document.write('<input alt="diminuisci la grandezza dei caratteri" style="width: 56px; height: 23px" onclick="javascript:fontsizedown(); return false;" src="http://www.istruzioneveneto.it/unimoodle/theme/1410provatema/text_minus.jpg" type="image"/>');
	
	document.write('<span class="textsizer_text">  sfondo </span>');
	document.write('<input alt="White" style="width: 23px; background-color:'+col1+'; height: 23px" onclick="javascript:setActiveColour('+col1a+'); return false;"  src="http://www.istruzioneveneto.it/unimoodle/theme/1410provatema/spacer.gif" type="image"/>');
    document.write('<input alt="Pastel Yellow" style="width: 23px; background-color:'+col2+'; height: 23px" onclick="javascript:setActiveColour('+col2a+'); return false;"  src="http://www.istruzioneveneto.it/unimoodle/theme/1410provatema/spacer.gif" type="image"/>');
   	document.write('<input alt="Pastel Cyan" style="width: 23px; background-color:'+col3+'; height: 23px" onclick="javascript:setActiveColour('+col3a+'); return false;"  src="http://www.istruzioneveneto.it/unimoodle/theme/1410provatema/spacer.gif" type="image"/>');
   	document.write('<input alt="Pastel Aqua" style="width: 23px; background-color:'+col4+'; height: 23px" onclick="javascript:setActiveColour('+col4a+'); return false;"  src="http://www.istruzioneveneto.it/unimoodle/theme/1410provatema/spacer.gif" type="image"/>');
    document.write('<input alt="Pastel Pink" style="width: 23px; background-color:'+col5+'; height: 23px" onclick="javascript:setActiveColour('+col5a+'); return false;"  src="http://www.istruzioneveneto.it/unimoodle/theme/1410provatema/spacer.gif" type="image"/>');
   	document.write('<input alt="Pastel Violet" style="width: 23px; background-color:'+col6+'; height: 23px" onclick="javascript:setActiveColour('+col6a+'); return false;"  src="http://www.istruzioneveneto.it/unimoodle/theme/1410provatema/spacer.gif" type="image"/>');
    document.write('<input alt="Pastel Lime Green" style="width: 23px; background-color:'+col7+'; height: 23px" onclick="javascript:setActiveColour('+col7a+'); return false;"  src="http://www.istruzioneveneto.it/unimoodle/theme/1410provatema/spacer.gif" type="image"/>');
   	document.write('<input alt="Pastel Forest Green" style="width: 23px; background-color:'+col8+'; height: 23px" onclick="javascript:setActiveColour('+col8a+'); return false;"  src="http://www.istruzioneveneto.it/unimoodle/theme/1410provatema/spacer.gif" type="image"/>');
   	document.write('<input alt="Pastel Orange" style="width: 23px; background-color:'+col9+'; height: 23px" onclick="javascript:setActiveColour('+col9a+'); return false;"  src="http://www.istruzioneveneto.it/unimoodle/theme/1410provatema/spacer.gif" type="image"/>');
    document.write('<input alt="Pastel Blue" style="width: 23px; background-color:'+col10+'; height: 23px" onclick="javascript:setActiveColour('+col10a+'); return false;"  src="http://www.istruzioneveneto.it/unimoodle/theme/1410provatema/spacer.gif" type="image"/>');
   	document.write('<input alt="Light Grey" style="width: 23px; background-color:'+col11+'; height: 23px" onclick="javascript:setActiveColour('+col11a+'); return false;"  src="http://www.istruzioneveneto.it/unimoodle/theme/1410provatema/spacer.gif" type="image"/>');
	
	document.write('<span class="textsizer_text">  stile </span>');
	document.write('<input alt="Arial" style="width: 70px; height: 23px" onclick="javascript:setActiveFont('+text1a+'); return false;" src="http://www.istruzioneveneto.it/unimoodle/theme/1410provatema/'+text1+'.jpg" type="image"/>');
   	document.write('<input alt="Tahoma" style="width: 70px; height: 23px" onclick="javascript:setActiveFont('+text2a+'); return false;" src="http://www.istruzioneveneto.it/unimoodle/theme/1410provatema/'+text2+'.jpg" type="image"/>');   	
	document.write('<input alt="Comic" style="width: 70px; height: 23px" onclick="javascript:setActiveFont('+text3a+'); return false;" src="http://www.istruzioneveneto.it/unimoodle/theme/1410provatema/'+text3+'.jpg" type="image"/>');
	document.write('<input alt="Trebuchet" style="width: 70px; height: 23px" onclick="javascript:setActiveFont('+text5a+'); return false;" src="http://www.istruzioneveneto.it/unimoodle/theme/1410provatema/'+text5+'.jpg" type="image"/>');
	
	document.write('</div>');
   	document.write('</form>');
    }
}


/* Date function */

function ShowDate() {
d = new Array(
"Sunday",
"Monday",
"Tuesday",
"Wednesday",
"Thursday",
"Friday",
"Saturday"
);
m = new Array(
"January",
"February",
"March",
"April",
"May",
"June",
"July",
"August",
"September",
"October",
"November",
"December"
);
today = new Date();
day = today.getDate();
year = today.getYear();
if (year < 2000)    // Y2K Fix, Isaac Powell
year = year + 1900; // http://onyx.idbsu.edu/~ipowell
end = "th";
if (day==1 || day==21 || day==31) end="st";
if (day==2 || day==22) end="nd";
if (day==3 || day==23) end="rd";
day+=end;
document.write(d[today.getDay()]+", "+m[today.getMonth()]+" ");
document.write(day+", " + year);
}
-->