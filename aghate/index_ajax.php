 <html>
 <head>
 <title>XMLHTTPREQUEST GRE WORDS QUERY</title>

<link rel="stylesheet" href="./commun/style/style_autocomplet.css" type="text/css" media="screen" charset="utf-8" />
<script type="text/javascript" src="./commun/js/AutoSuggest.js"></script>
 </head>
 <body>



 <form >

 this is a small test
 <input type="text" id="abr" name="abr" value="<?php echo $_POST['abr'];?>" onkeyup="callsuggestionbox('abr','treat.php');" />
 <div id="suggestionbox"  class="prop" onmousemove="this.style.visibility='visible';" onmouseout="this.style.visibility='hidden';"> </div>

 
<br /> this is a second test<input type="text" id="test" name="test" value="<?php echo $_POST['test'];?>" onkeyup="callsuggestionbox('test','treat.php');" /> 
 </form>



 </body>
 </html>

