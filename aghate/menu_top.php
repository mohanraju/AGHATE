<?php

$menus[]=array('name' =>'Accuil',		'droit_level' =>'0',		'link' => 'index.php', 			'img' =>'./commun/images/homme.jpg',			'tooltip'=>'accuil');
$menus[]=array('name' =>'Gestion',		'droit_level' =>'1',		'link' => 'admin_accueil.php', 	'img' =>'./commun/images/admin.jpg',			'tooltip'=>'Gestion');
$menus[]=array('name' =>'Premier place disponible',		'droit_level' =>'1',		'link' => 'admin_accueil.php', 	'img' =>'./commun/images/admin.jpg',			'tooltip'=>'Premier Place disponible');
$menus[]=array('name' =>'Localisation Patients',		'droit_level' =>'1',		'link' => 'admin_accueil.php', 	'img' =>'./commun/images/admin.jpg',			'tooltip'=>'Localisation Patients');
$menus[]=array('name' =>'Syncronisation Gilda',		'droit_level' =>'1',		'link' => 'admin_accueil.php', 	'img' =>'./commun/images/admin.jpg',			'tooltip'=>'Syncronisation Gilda');
$menus[]=array('name' =>'Recherche patients','droit_level' =>'1',		'link' => 'admin_accueil.php', 	'img' =>'./commun/images/admin.jpg',			'tooltip'=>'Recherche patients');
$menus[]=array('name' =>'Situation Lit','droit_level' =>'1',		'link' => 'admin_accueil.php', 	'img' =>'./commun/images/admin.jpg',			'tooltip'=>'situation Lit');
$menus[]=array('name' =>'Gestion',		'droit_level' =>'1',		'link' => 'admin_accueil.php', 	'img' =>'./commun/images/admin.jpg',			'tooltip'=>'Gestion');

$UserLevel=$Aghate->GetUserLevel($$_SESSION['login'],$area,'area');

echo "</head>\n";
echo "<body>\n";
echo '<nav role="navigation" class="navbar navbar-inverse navbar-fixed-top">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
        <button type="button" data-target="#navbarCollapse" data-toggle="collapse" class="navbar-toggle">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
        <a href="#" class="navbar-brand">AGHATE</a>
    </div>
    <div id="navbarCollapse" class="collapse navbar-collapse">
        <ul class="nav navbar-nav">
    ';
 
for ($m=0; $m < count($menus); $m++)
{
	if($UserLevel >= intval($menus[$m]['droit_level'])){
		echo 	"<li><a href='".$menus[$m]['link']."'  title='".$menus[$m]['tooltip']."'><i class='icon-home icon-white'></i>".$menus[$m]['name']."</a></li>";
	}

}
echo "</ul>";
 
?>
<!-- LOGIN menus-->
        <ul class="nav navbar-nav navbar-right">
            <li><a href="#">Login</a></li>
        </ul>
    </div>
</nav>