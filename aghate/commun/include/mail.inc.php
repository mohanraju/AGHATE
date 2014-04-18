<?php
    error_reporting (E_ALL ^ E_NOTICE ^ E_WARNING);
    // Appel de la classe phpmailer
    require "./phpmailer/class.phpmailer.php";

    define("GRR_FROM",getSettingValue("grr_mail_from"));
    define("GRR_FROMNAME",getSettingValue("grr_mail_fromname"));
    class my_phpmailer extends phpmailer {
        // Set default variables for all new objects
        var $From = GRR_FROM;
        var $FromName = GRR_FROMNAME;
        var $Port = 25;
        var $Priority = 3;
        var $Encoding = "8bit";
        var $CharSet = "utf-8";
        var $checkAddress = false;
        var $IsHTML= true;
        var $WordWrap = 75;
        function my_phpmailer() {
            if (getSettingValue("grr_mail_method")  == "smtp") {
                $this->Host = getSettingValue("grr_mail_smtp");
                $this->Mailer = "smtp";
                if (getSettingValue("grr_mail_Username")!="") {
                    $this->SMTPAuth  = true;
                    $this->Username = getSettingValue("grr_mail_Username");
                    $this->Password = getSettingValue("grr_mail_Password");
                } else {
                    $this->SMTPAuth  = false;
                }
            }
        }
    }
?>
