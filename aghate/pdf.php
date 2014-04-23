<?php
include("phpToPDF.php");

$PDF = new phpToPDF();
$PDF->AddPage();
$PDF->SetFont("Arial","B",16);
$PDF->Text(40,10,"Uniquement un texte");

$PDF->Output("test.PDF", "F");

// affiche le document test.PDF dans une iframe.
echo '
	<iframe src="test.PDF" width="100%" height="100%">
	[Your browser does <em>not</em> support <code>iframe</code>,
	or has been configured not to display inline frames.
	You can access <a href="./test.PDF">the document</a>
	via a link though.]</iframe>
';
?>
