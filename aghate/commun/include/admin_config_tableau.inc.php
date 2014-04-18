<?php
?>
<script type="text/javascript" language="javascript">
function changeclass(objet, myClass) { objet.className = myClass; }
</script>
<?php
echo "<table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n";
echo "<tbody>\n";
echo "<tr>";
for ($k=1;$k<6;$k++) {
echo "<td>";
if ($page_config == $k) {
    echo "<div style=\"position: relative;\"><div class=\"onglet_off\" style=\"position: relative; top: 0px; padding-left: 20px; padding-right: 20px;\">".
    get_vocab('admin_config'.$k.'.php')."</div></div>";
} else {
    echo "<div style=\"position: relative;\">
    <div onmouseover=\"changeclass(this, 'onglet_on');\" onmouseout=\"changeclass(this, 'onglet');\" class=\"onglet\" style=\"position: relative; top: 0px; padding-left: 20px; padding-right: 20px;\">
    <a href=\"admin_config.php?page_config=".$k."\">".get_vocab('admin_config'.$k.'.php')."</a></div></div>";
}
echo "</td>\n";
}
echo "</tr></tbody></table>\n";
?>