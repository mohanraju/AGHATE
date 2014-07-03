<?php
#####################
# gestion des langues
#####################

# Les "locales" sont un système permettant de gérer le plus proprement possible les différences de langue et de style
# des utilisateurs. Vous pouvez connaître les locales installées sur votre système linux avec la commande "locale -a"
# (les noms semblent peu standardisés, vous aurez des chaînes du genre "fr_FR" ou "fr_FR.ISO8859-1")

if (function_exists("getSettingValue")) {
    $defaultlanguage = getSettingValue("default_language");
} else {
    $defaultlanguage = false;
}

if (isset($_SESSION['default_language'])) {
    // si l'utilisateur a défini sa propre langue
    $locale = $_SESSION['default_language'];
} else if ($defaultlanguage) {
    // sinon, on utilise la variable stockée dans la base
    $locale = $defaultlanguage;
} else {
    // sinon, on fixe la valeur à "fr"
    $locale = 'fr';
}

// $pass_leng est utilisé dans les fichiers langue, d'où la ligne ci-dessous
$pass_leng = "";
if (isset($fichier_mysql_inc_est_present)) $pass_leng = grr_sql_query1("select VALUE from agt_config where NAME = 'pass_leng'");

// Fichier de traduction
if (@file_exists("./commun/language/lang." . $locale)) {
    $lang_file = "./commun/language/lang." . $locale;
} else {
    $lang_file = "./commun/language/lang.fr";
}
include $lang_file;

// Fichiers de personnalisation de langue
if (@file_exists("./commun/language/lang_subst." . $locale))
    include "./commun/language/lang_subst." . $locale;



// Pour l'affichage de la pendule javascript
$clock_file = "clock_".$locale.".js";

// Sélection du fichier d'aide.
$faqfilelang = "_".$locale;

# Format de la date : 0 pour afficher des dates sous la forme "Jul 10", 1 pour afficher des dates sous la forme "10 Jul"
if ($locale == 'en') {
    $dateformat = 'en';
} else {
    $dateformat = 'fr';
}

switch ($dateformat) {
    case "en":
    $dformat = "%A, %B %d, %Y";
    break;
    case "fr":
    $dformat = "%A %d %B %Y";
    break;
}

function utf8_convert($string)
{
  global $windows_locale, $unicode_encoding, $winlocale_codepage_map;

  if ($unicode_encoding && (get_server_os() == "windows") && $windows_locale)
  {
    if ($winlocale_codepage_map[$windows_locale])
    {
      $string = iconv($winlocale_codepage_map[$windows_locale],"utf-8",$string);
    }
  }
  return $string;
}

function utf8_strftime($format, $time)
{
  $result = strftime($format,$time);
  return utf8_convert($result);
}


# A map is needed to convert from the HTTP language specifier to a
# locale specifier for Windows
$lang_map_windows = array
(
  'cs' => 'csy',
  'cs-cs' => 'csy',
  'cs-cz' => 'csy',
  'cz' => 'csy',
  'cz-cz' => 'csy',
  'da' => 'dan',
  'da-da' => 'dan',
  'de' => 'deu',
  'de-at' => 'dea',
  'de-ch' => 'des',
  'de-de' => 'deu',
  'el' => 'ell',
  'el-el' => 'ell',
  'el-gr' => 'ell',
  'en' => 'eng',
  'en-au' => 'ena',
  'en-ca' => 'enc',
  'en-en' => 'eng',
  'en-ie' => 'eng',
  'en-nz' => 'enz',
  'en-gb' => 'eng',
  'en-us' => 'usa',
  'es' => 'esp',
  'es-es' => 'esp',
  'es-mx' => 'esm',
  'fi' => 'fin',
  'fi-fi' => 'fin',
  'fr' => 'fra',
  'fr-be' => 'frb',
  'fr-ca' => 'frc',
  'fr-ch' => 'frs',
  'fr-fr' => 'fra',
  'it' => 'ita',
  'it-ch' => 'its',
  'it-it' => 'its',
  'ja' => 'jpn',
  'ja-ja' => 'jpn',
  'ja-jp' => 'jpn',
  'ko' => 'kor',
  'ko-ko' => 'kor',
  'ko-kr' => 'kor',
  'nl' => 'nld',
  'nl-be' => 'nlb',
  'nl-nl' => 'nld',
  'no' => 'norwegian',
  'no-no' => 'norwegian',
  'nb' => 'nor',
  'nb-nb' => 'nor',
  'nn' => 'non',
  'nn-nn' => 'non',
  'pl' => 'plk',
  'pl-pl' => 'plk',
  'pt' => 'ptg',
  'pt-br' => 'ptb',
  'pt-pt' => 'ptg',
  'sv' => 'sve',
  'sv-se' => 'sve',
  'sv-sv' => 'sve',
  'zh' => 'chinese',
  'zh-tw' => 'cht',
  'zh-cn' => 'chs',
  'zh-hk' => 'cht',
  'zh-sg' => 'cht',
  'zh-zh' => 'chinese',
);

# This maps a Windows locale to the charset it uses, which are
# all Windows code pages
$winlocale_codepage_map = array
(
  'chs' => 'CP936',
  'cht' => 'CP950',
  'csy' => 'CP1250',
  'dan' => 'CP1252',
  'dea' => 'CP1252',
  'des' => 'CP1252',
  'deu' => 'CP1252',
  'ell' => 'CP1253',
  'ena' => 'CP1252',
  'enc' => 'CP1252',
  'eng' => 'CP1252',
  'enz' => 'CP1252',
  'esm' => 'CP1252',
  'esp' => 'CP1252',
  'fin' => 'CP1252',
  'fra' => 'CP1252',
  'frb' => 'CP1252',
  'frc' => 'CP1252',
  'frs' => 'CP1252',
  'ita' => 'CP1252',
  'its' => 'CP1252',
  'jpn' => 'CP932',
  'kor' => 'CP949',
  'nlb' => 'CP1252',
  'nld' => 'CP1252',
  'norwegian' => 'CP1252',
  'ptb' => 'CP1252',
  'ptg' => 'CP1252',
  'plk' => 'CP1250',
  'sve' => 'CP1252',
  'usa' => 'CP1252'
);

// Cas particulier de la langue française
// Si la langue française est choisie et que setlocale(LC_TIME,'fr') renvoie false
// On essaye d'autres possibilités
$ok = 'no';
if ($locale == 'fr') {
    $server_os = get_server_os();
    // Si le serveur est sous windows
    if ($server_os == "windows") {
        if ($lang_map_windows[strtolower($locale)]) {
            setlocale(LC_ALL, $lang_map_windows[strtolower($locale)]);
            $windows_locale = $lang_map_windows[strtolower($locale)];
        }
    } else {
        if (!setlocale(LC_TIME,$locale))  {
            if ($unicode_encoding) {
               if (setlocale(LC_TIME,'fr_FR.utf-8') == TRUE) $ok = 'yes';
               else if (setlocale(LC_TIME,'fr_FR.utf8') == TRUE) $ok = 'yes';
               else if (setlocale(LC_TIME,'fr-utf-8') == TRUE) $ok = 'yes';
            } else {
               if (setlocale(LC_TIME,'fr_FR.ISO8859-1') == TRUE) $ok = 'yes';
               else if (setlocale(LC_TIME,'French') == TRUE) $ok = 'yes';
               else if (setlocale(LC_TIME,'france') == TRUE) $ok = 'yes';
               else if (setlocale(LC_TIME,'fra') == TRUE) $ok = 'yes';
               else if (setlocale(LC_TIME,'french') == TRUE) $ok = 'yes';
               else if (setlocale(LC_TIME,'FR') == TRUE) $ok = 'yes';
               else if (setlocale(LC_TIME,'fr_FR') == TRUE) $ok = 'yes';
               else if (setlocale(LC_TIME,'fr_FR@euro') == TRUE) $ok = 'yes';
           }
        }
    }
}
// Les autres langues que le français
if ($locale != 'fr') {
    $server_os = get_server_os();
    // Si le serveur est sous windows
    if ($server_os == "windows") {
        if ($lang_map_windows[strtolower($locale)]) {
            setlocale(LC_ALL, $lang_map_windows[strtolower($locale)]);
            $windows_locale = $lang_map_windows[strtolower($locale)];
        }
    } else {
        // Si la locale sous la forme de deux lettre (de, en, ...) n'est pas reconue par le serveur,
        // on tente de la mettre sous la forme de_DE, etc...
        // cas perticulier de l'anglais :
        if ($locale == 'en') $locale = "en_US";
        // Autre cas :
        if (strlen($locale) == 2)
        {
           # Convertit locale=xx en xx_XX, ce qui est approprié à certains système
           $locale = strtolower($locale)."_".strtoupper($locale);
        }
        if ($unicode_encoding) {
            $locale_utf8 .= $locale.".utf-8";
            if (setlocale(LC_TIME,$locale_utf8) == TRUE) $ok = 'yes';
            else
            setlocale(LC_TIME,$locale);
        } else
            setlocale(LC_TIME,$locale);
    }
}


function get_server_os()
{
  if (stristr(PHP_OS,"Darwin"))
  {
    return "unix";
  }
  else if (stristr(PHP_OS, "WIN"))
  {
    return "windows";
  }
  else if (stristr(PHP_OS, "ux") || stristr(PHP_OS, "BSD"))
  {
    return "unix";
  }
  else
  {
    return "unsupported";
  }
}

// Si $unicode_encoding a pour valeur 1, on teste si iconv est activé
if (($unicode_encoding) && (!function_exists('iconv')))
{
exit("
<P><B>Erreur:</B> Le module iconv, qui permet le support Unicode par PHP, n'est pas installé le serveur.</P>\n
<P>Pour éviter cette erreur, suivez une des deux possibilités ci-dessous :</P>
<UL>
<LI><P>Installez et activez le module iconv.<br /></P></LI>
<LI><P>Désactivez le support Unicode en donnant à la variable \$unicode_encoding la valeur 0 dans le fichier config.inc.php.
</P></LI>
</UL>
");
}

# Get a vocab item, in UTF-8 or a local encoding, depending on the setting of $unicode_encoding
function get_vocab($tag)
{
  global $vocab, $charset_html, $unicode_encoding;
  if (!isset($vocab[$tag])) {
      return "<b><font color=\"#FF0000\"><i>(".$tag.")</i></font></b>";
  } else {
      if ($unicode_encoding)
      {
        return iconv($charset_html,"utf-8",$vocab[$tag]);
      }
      else
      {
        return $vocab[$tag];
      }
  }
}

if (isset($_SESSION['type_month_all'])) $type_month_all = $_SESSION['type_month_all']; else $type_month_all = "month_all";
?>
