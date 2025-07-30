<?php

// SZAMLAZZ.HU INVOICE AGENT COMMUNICATION CLASS

// setting php default error reporting to minimal, we'll use our custom logging method instead
// defining TAB character constant just like we use PHP_EOL for marking lines end

/**
 * CUSTOM STYLERS MODIFICATIONS:
 * - Add namespace for autoloading instead of require_once
 * - Rename class and file name for autoloading instead of require_once
 * - don't use session(_start) when testing - it breaks testing environment. session is only needed for storing connection type
 */

namespace App\Services\Billing\Szamlazzhu\Phpapi;

use DateTime;

if ('cli' !== PHP_SAPI) {
    session_start();
}
setlocale(LC_CTYPE, 'en_US.UTF8');

# @todo @ivan @20190130 - ez a sor kell, kulonben az alabbi hibat dobja:
# Undefined index: tetel
// error_reporting(E_ERROR);

define("TAB", "\t");

class InvoiceAgent
{

    // defininig variables, note that $cfg is protected, as it could contain critical information

    protected $backupErrorLevel;

    protected $cfg;
    public $xml_schema;
    public $xml_scope;
    public $payments_stack;
    public $items_stack;
    public $post_request;
    public $delim;
    public $context;

    public function __construct($config)
    {
        // Load user defined .ini file, or default configuration
        // Check file for more info and comments

        $this->backupErrorLevel = error_reporting();
        error_reporting(E_ERROR);

        $this->cfg = $config;
        $this->writeLog("Default config file loaded", "debug");

        //        _generateInvoice() expected data structure passed to xml_schema variable
        //            - title of xml tag
        //            - variable name user when calling the function
        //            - expected type
        //            - is required? (boolean)
        //            - default value
        //            - show xml tag even if it's content is null? (boolean)

        $this->xml_schema["generateInvoice"]["beallitasok"] = [
            ["felhasznalo", "username", "str", 1, "", 0],
            ["jelszo", "password", "str", 1, "", 0],
            ["apiKey", "api_key", "str", 0, "", 0],
            ["eszamla", "e_invoice", "bool", 1, "true", 0],
            ["kulcstartojelszo", "keychain", "str", 0, "", 0],
            ["szamlaLetoltes", "download_invoice", "bool", 1, "false", 0],
            ["szamlaLetoltesPld", "download_count", "num", 0, "", 0],
            ["valaszVerzio", "response_type", "num", 1, "1", 0],
            ["aggregator", "aggregator", "str", 0, "", 0]
        ];

        $this->xml_schema["generateInvoice"]["fejlec"] = [
            ["keltDatum", "invoice_date", "date", 1, "", 0],
            ["teljesitesDatum", "fulfillment", "date", 1, "", 0],
            ["fizetesiHataridoDatum", "payment_due", "date", 1, "", 0],
            ["fizmod", "payment_method", "str", 1, "", 0],
            ["penznem", "currency", "str", 1, "Ft", 0],
            ["szamlaNyelve", "language", "str", 1, "hu", 0],
            ["megjegyzes", "comment", "str", 0, "", 0],
            ["arfolyamBank", "exchange_bank", "str", 0, "", 0],
            ["arfolyam", "exchange_rate", "num", 0, "", 0],
            ["rendelesSzam", "order_no", "str", 0, "", 0],
            ["elolegszamla", "is_deposit", "bool", 0, "", 0],
            ["vegszamla", "is_final", "bool", 0, "", 0],
            ["helyesbitoszamla", "is_corrective", "bool", 0, "", 0],
            ["helyesbitettSzamlaszam", "correctived_num", "str", 0, "", 0],
            ["dijbekero", "is_proform", "bool", 0, "", 0],
            ["szallitolevel", "is_deliverynote", "bool", 0, "", 0],
            ["logoExtra", "logo_extra", "str", 0, "", 0],
            ["szamlaszamElotag", "num_prefix", "str", 0, "", 0],
            ["fizetendoKorrekcio", "correction_to_pay", "num", 0, "", 0],
            ["fizetve", "is_paid", "bool", 0, "", 0],
            ["arresAfa", "profit_vat", "bool", 0, "", 0]
        ];

        $this->xml_schema["generateInvoice"]["elado"] = [
            ["bank", "bank", "str", 0, "", 0],
            ["bankszamlaszam", "bank_account", "str", 0, "", 0],
            ["emailReplyto", "email_replyto", "str", 0, "", 0],
            ["emailTargy", "email_subject", "str", 0, "", 0],
            ["emailSzoveg", "email_content", "str", 0, "", 0],
            ["alairoNeve", "signatory", "str", 0, "", 0]
        ];

        $this->xml_schema["generateInvoice"]["vevo"][] = ["nev", "name", "str", 1, "", 0];
        $this->xml_schema["generateInvoice"]["vevo"][] = ["orszag", "country", "str", 0, "", 0];
        $this->xml_schema["generateInvoice"]["vevo"][] = ["irsz", "zip", "str", 1, "", 0];
        $this->xml_schema["generateInvoice"]["vevo"][] = ["telepules", "city", "str", 1, "", 0];
        $this->xml_schema["generateInvoice"]["vevo"][] = ["cim", "address", "str", 1, "", 0];
        $this->xml_schema["generateInvoice"]["vevo"][] = ["email", "email", "str", 0, "", 0];
        $this->xml_schema["generateInvoice"]["vevo"][] = ["sendEmail", "send_email", "bool", 0, "", 0];
        $this->xml_schema["generateInvoice"]["vevo"][] = ["adoszam", "tax_no", "str", 0, "", 0];
        $this->xml_schema["generateInvoice"]["vevo"][] = ["adoszamEU", "eu_tax_no", "str", 0, "", 0];
        $this->xml_schema["generateInvoice"]["vevo"][] = ["postazasiNev", "postal_name", "str", 0, "", 0];
        $this->xml_schema["generateInvoice"]["vevo"][] = ["postazasiOrszag", "postal_country", "str", 0, "", 0];
        $this->xml_schema["generateInvoice"]["vevo"][] = ["postazasiIrsz", "postal_zip", "str", 0, "", 0];
        $this->xml_schema["generateInvoice"]["vevo"][] = ["postazasiTelepules", "postal_city", "str", 0, "", 0];
        $this->xml_schema["generateInvoice"]["vevo"][] = ["postazasiCim", "postal_address", "str", 0, "", 0];
        $this->xml_schema["generateInvoice"]["vevo"][] = ["vevoFokonyv", "buyer_account", "arr", 0, "", 0];
        $this->xml_schema["generateInvoice"]["vevo"][] = ["azonosito", "buyer_id", "str", 0, "", 0];
        $this->xml_schema["generateInvoice"]["vevo"][] = ["alairoNeve", "signatory", "str", 0, "", 0];
        $this->xml_schema["generateInvoice"]["vevo"][] = ["telefonszam", "phone", "str", 0, "", 0];
        $this->xml_schema["generateInvoice"]["vevo"][] = ["megjegyzes", "comment", "str", 0, "", 0];

        $this->xml_schema["generateInvoice"]["vevo::vevoFokonyv"][] = [
            "konyvelesDatum",
            "account_date",
            "date",
            0,
            "",
            0
        ];
        $this->xml_schema["generateInvoice"]["vevo::vevoFokonyv"][] = [
            "vevoAzonosito",
            "buyer_id",
            "str",
            0,
            "",
            0
        ];
        $this->xml_schema["generateInvoice"]["vevo::vevoFokonyv"][] = [
            "vevoFokonyviSzam",
            "account_id",
            "str",
            0,
            "",
            0
        ];

        $this->xml_schema["generateInvoice"]["fuvarlevel"][] = ["uticel", "destination", "str", 0, "", 0];
        $this->xml_schema["generateInvoice"]["fuvarlevel"][] = ["futarSzolgalat", "parcel", "str", 0, "", 0];
        $this->xml_schema["generateInvoice"]["fuvarlevel"][] = ["vonalkod", "barcode", "str", 0, "", 0];
        $this->xml_schema["generateInvoice"]["fuvarlevel"][] = ["megjegyzes", "comment", "str", 0, "", 0];
        $this->xml_schema["generateInvoice"]["fuvarlevel"][] = ["tof", "trans-o-flex", "arr", 0, "", 0];
        $this->xml_schema["generateInvoice"]["fuvarlevel"][] = ["ppp", "pick-pack-point", "arr", 0, "", 0];
        $this->xml_schema["generateInvoice"]["fuvarlevel"][] = ["sprinter", "sprinter", "arr", 0, "", 0];

        $this->xml_schema["generateInvoice"]["fuvarlevel::tof"][] = ["azonosito", "numeric_id", "str", 0, "", 0];
        $this->xml_schema["generateInvoice"]["fuvarlevel::tof"][] = ["shipmentID", "shipment_id", "str", 0, "", 0];
        $this->xml_schema["generateInvoice"]["fuvarlevel::tof"][] = ["csomagszam", "packet_no", "num", 0, "", 0];
        $this->xml_schema["generateInvoice"]["fuvarlevel::tof"][] = [
            "countryCode",
            "country_code",
            "str",
            0,
            "",
            0
        ];
        $this->xml_schema["generateInvoice"]["fuvarlevel::tof"][] = ["zip", "zip", "str", 0, "", 0];
        $this->xml_schema["generateInvoice"]["fuvarlevel::tof"][] = ["service", "service", "str", 0, "", 0];

        $this->xml_schema["generateInvoice"]["fuvarlevel::ppp"][] = [
            "vonalkodPrefix",
            "barcode_prefix",
            "str",
            0,
            "",
            0
        ];
        $this->xml_schema["generateInvoice"]["fuvarlevel::ppp"][] = [
            "vonalkodPostfix",
            "barcode_postfix",
            "str",
            0,
            "",
            0
        ];

        $this->xml_schema["generateInvoice"]["fuvarlevel::sprinter"][] = [
            "azonosito",
            "numeric_id",
            "str",
            0,
            "",
            0
        ];
        $this->xml_schema["generateInvoice"]["fuvarlevel::sprinter"][] = [
            "feladokod",
            "sender_id",
            "str",
            0,
            "",
            0
        ];
        $this->xml_schema["generateInvoice"]["fuvarlevel::sprinter"][] = [
            "iranykod",
            "shipment_zip",
            "str",
            0,
            "",
            0
        ];
        $this->xml_schema["generateInvoice"]["fuvarlevel::sprinter"][] = [
            "csomagszam",
            "packet_no",
            "num",
            0,
            "",
            0
        ];
        $this->xml_schema["generateInvoice"]["fuvarlevel::sprinter"][] = [
            "vonalkodPostfix",
            "barcode_postfix",
            "str",
            0,
            "",
            0
        ];
        $this->xml_schema["generateInvoice"]["fuvarlevel::sprinter"][] = [
            "szallitasiIdo",
            "shipping_time",
            "str",
            0,
            "",
            0
        ];

        $this->xml_schema["generateInvoice"]["tetel"][] = array("megnevezes", "name", "str", 1, "", 0);
        $this->xml_schema["generateInvoice"]["tetel"][] = array("azonosito", "id", "str", 0, "", 0);
        $this->xml_schema["generateInvoice"]["tetel"][] = array("mennyiseg", "quantity", "num", 1, "", 0);
        $this->xml_schema["generateInvoice"]["tetel"][] = array("mennyisegiEgyseg", "quantity_unit", "str", 1, "", 0);
        $this->xml_schema["generateInvoice"]["tetel"][] = array("nettoEgysegar", "unit_price", "num", 1, "", 0);
        $this->xml_schema["generateInvoice"]["tetel"][] = array("afakulcs", "vat", "str", 1, "", 0);
        $this->xml_schema["generateInvoice"]["tetel"][] = array("arresAfaAlap", "profit_vat", "num", 0, "", 0);
        $this->xml_schema["generateInvoice"]["tetel"][] = array("nettoErtek", "net_price", "num", 1, "", 0);
        $this->xml_schema["generateInvoice"]["tetel"][] = array("afaErtek", "vat_amount", "num", 1, "", 0);
        $this->xml_schema["generateInvoice"]["tetel"][] = array("bruttoErtek", "gross_amount", "num", 1, "", 0);
        $this->xml_schema["generateInvoice"]["tetel"][] = array("megjegyzes", "comment", "str", 0, "", 0);
        $this->xml_schema["generateInvoice"]["tetel"][] = array("tetelFokonyv", "product_accounting", "arr", 0, "", 0);

        $this->xml_schema["generateInvoice"]["tetel::tetelFokonyv"][] = array(
            "gazdasagiEsem",
            "transaction_event",
            "str",
            0,
            "",
            0
        );
        $this->xml_schema["generateInvoice"]["tetel::tetelFokonyv"][] = array(
            "gazdasagiEsemAfa",
            "transaction_vat",
            "str",
            0,
            "",
            0
        );
        $this->xml_schema["generateInvoice"]["tetel::tetelFokonyv"][] = array(
            "arbevetelFokonyviSzam",
            "income_account_no",
            "str",
            0,
            "",
            0
        );
        $this->xml_schema["generateInvoice"]["tetel::tetelFokonyv"][] = array(
            "afaFokonyviSzam",
            "vat_account_no",
            "str",
            0,
            "",
            0
        );

        // _reverseInvoice() expected data structure

        $this->xml_schema["reverseInvoice"]["beallitasok"][] = array("felhasznalo", "username", "str", 1, "", 0);
        $this->xml_schema["reverseInvoice"]["beallitasok"][] = array("jelszo", "password", "str", 1, "", 0);
        $this->xml_schema["reverseInvoice"]["beallitasok"][] = array("apiKey", "api_key", "str", 0, "", 0);
        $this->xml_schema["reverseInvoice"]["beallitasok"][] = array("eszamla", "e_invoice", "bool", 1, "true", 0);
        $this->xml_schema["reverseInvoice"]["beallitasok"][] = array("kulcstartojelszo", "keychain", "str", 0, "", 0);
        $this->xml_schema["reverseInvoice"]["beallitasok"][] = array(
            "szamlaLetoltes",
            "download_invoice",
            "bool",
            1,
            "false",
            0
        );
        $this->xml_schema["reverseInvoice"]["beallitasok"][] = array(
            "szamlaLetoltesPld",
            "download_count",
            "num",
            0,
            "",
            0
        );
        $this->xml_schema["reverseInvoice"]["beallitasok"][] = array("aggregator", "aggregator", "str", 0, "", 0);

        $this->xml_schema["reverseInvoice"]["fejlec"][] = array("szamlaszam", "invoice_num", "str", 1, "", 0);
        $this->xml_schema["reverseInvoice"]["fejlec"][] = array("keltDatum", "invoice_date", "date", 0, "", 0);
        $this->xml_schema["reverseInvoice"]["fejlec"][] = array("teljesitesDatum", "fulfillment", "date", 0, "", 0);
        $this->xml_schema["reverseInvoice"]["fejlec"][] = array("tipus", "invoice_type", "str", 0, "", 0);

        $this->xml_schema["reverseInvoice"]["elado"][] = array("emailReplyto", "email_replyto", "str", 0, "", 0);
        $this->xml_schema["reverseInvoice"]["elado"][] = array("emailTargy", "email_subject", "str", 0, "", 0);
        $this->xml_schema["reverseInvoice"]["elado"][] = array("emailSzoveg", "email_content", "str", 0, "", 0);

        $this->xml_schema["reverseInvoice"]["vevo"][] = array("email", "email", "str", 0, "", 0);

        // _payInvoice() expected data structure

        $this->xml_schema["payInvoice"]["beallitasok"][] = array("felhasznalo", "username", "str", 1, "", 0);
        $this->xml_schema["payInvoice"]["beallitasok"][] = array("jelszo", "password", "str", 1, "", 0);
        $this->xml_schema["payInvoice"]["beallitasok"][] = array("apiKey", "api_key", "str", 0, "", 0);
        $this->xml_schema["payInvoice"]["beallitasok"][] = array("szamlaszam", "invoice_num", "str", 1, "", 0);
        $this->xml_schema["payInvoice"]["beallitasok"][] = array("additiv", "additive", "bool", 1, "false", 0);
        $this->xml_schema["payInvoice"]["beallitasok"][] = array("aggregator", "aggregator", "str", 0, "", 0);

        $this->xml_schema["payInvoice"]["kifizetes"][] = array("datum", "date", "date", 1, "", 0);
        $this->xml_schema["payInvoice"]["kifizetes"][] = array("jogcim", "transaction_title", "str", 1, "", 0);
        $this->xml_schema["payInvoice"]["kifizetes"][] = array("osszeg", "amount", "num", 1, "", 0);

        // _requestInvoicePDF() expected data structure

        $this->xml_schema["requestInvoicePDF"]["args"][] = array("felhasznalo", "username", "str", 1, "", 0);
        $this->xml_schema["requestInvoicePDF"]["args"][] = array("jelszo", "password", "str", 1, "", 0);
        $this->xml_schema["requestInvoicePDF"]["args"][] = array("apiKey", "api_key", "str", 0, "", 0);
        $this->xml_schema["requestInvoicePDF"]["args"][] = array("szamlaszam", "invoice_num", "str", 1, "", 0);
        $this->xml_schema["requestInvoicePDF"]["args"][] = array("valaszVerzio", "response_type", "num", 1, "1", 0);

        $this->writeLog("Agent initialized", "debug");

    }

    /**
     * wraps logging function
     * @param $message
     * @param $type
     * @return bool
     * @throws \Exception
     */
    public function writeLog($message, $type)
    {
        if ($type == 'debug' && $this->cfg['defaults']['log_level'] < 3) {
            return false;
        } elseif ($type == 'warn' && $this->cfg['defaults']['log_level'] < 2) {
            return false;
        } elseif ($type == 'error' && $this->cfg['defaults']['log_level'] < 1) {
            return false;
        }


        if (isset($this->cfg['defaults']['log_file'])) {
            error_log('[' . date('Y-m-d H:i:s') . '] [' . $_SERVER['REMOTE_ADDR'] . '] [' . $type . '] ' . $message . PHP_EOL,
                3, $this->cfg['defaults']['log_file']);
        } else {
            error_log('[' . $type . '] ' . $message, 0);
        }

        if (isset($this->cfg['defaults']['log_email']) && $type == 'error') {
            error_log($message, 1, $this->cfg['defaults']['log_email']);
        }

        if ($type == "error") {
            throw new \Exception($message);
        }
        return null;
    }

    //
    // loops through expected xml schema, checks for information validity, builds xml if input data is ok
    // returns error report if there is a problem like variable type mismatch or missing value
    //
    public function checkAndInsert($action, $parent, $args, $padding)
    {
        $target = "";
        if (!$padding) {
            $padding = TAB;
        }

        foreach ($this->xml_schema[$action][$parent] as &$value) {
            if ($value[2] != "arr") {
                if (array_key_exists($value[1], $args) && ($value[2] == 'bool' || $args[$value[1]] != "")) {

                    if ($value[2] == "num") {
                        if (!is_numeric($args[$value[1]])) {
                            $this->writeLog("Use numeric for `" . $value[1] . "` under `" . $parent . "` section!",
                                "error");
                            return false;
                        } else {
                            $target .= $padding . '<' . $value[0] . '>' . htmlspecialchars($args[$value[1]]) . '</' . $value[0] . '>' . PHP_EOL;
                        }
                    } elseif ($value[2] == "str") {
                        if (!is_string($args[$value[1]])) {
                            $this->writeLog("Use string for `" . $value[1] . "` under `" . $parent . "` section!",
                                "error");
                            return false;
                        } else {
                            $target .= $padding . '<' . $value[0] . '>' . htmlspecialchars($args[$value[1]]) . '</' . $value[0] . '>' . PHP_EOL;
                        }
                    } elseif ($value[2] == "date") {
                        // accepted format: YYYY-MM-DD
                        if ($args[$value[1]] instanceof DateTime) {
                            $parsed_date = $args[$value[1]];
                        } else {
                            $parsed_date = DateTime::createFromFormat('Y-m-d', $args[$value[1]]);
                        }
                        if (!$parsed_date || DateTime::getLastErrors()['warning_count'] > 0 || !checkdate($parsed_date->format("m"),
                                $parsed_date->format("d"), $parsed_date->format("Y"))) {
                            $this->writeLog("Use date for `" . $value[1] . "` under `" . $parent . "` section!",
                                "error");
                            return false;
                        } else {
                            $target .= $padding . '<' . $value[0] . '>' . htmlspecialchars($parsed_date->format("Y-m-d")) . '</' . $value[0] . '>' . PHP_EOL;
                        }
                    } elseif ($value[2] == "bool") {
                        if (!is_bool($args[$value[1]])) {
                            $this->writeLog("Use boolean for `" . $value[1] . "` under `" . $parent . "` section!",
                                "error");
                            return false;
                        } else {
                            $target .= $padding . '<' . $value[0] . '>' . var_export($args[$value[1]],
                                    true) . '</' . $value[0] . '>' . PHP_EOL;
                        }
                    }
                } elseif ((!array_key_exists($value[1],
                            $args) || $args[$value[1]] == "") && $this->cfg[$parent][$value[1]] != "") {
                    $target .= $padding . '<' . $value[0] . '>' . htmlspecialchars($this->cfg[$parent][$value[1]]) . '</' . $value[0] . '>' . PHP_EOL;
                } elseif ((!array_key_exists($value[1],
                            $args) || $args[$value[1]] == "") && $value[4] != "" && !$this->cfg[$parent][$value[1]]) {
                    $target .= $padding . '<' . $value[0] . '>' . htmlspecialchars($value[4]) . '</' . $value[0] . '>' . PHP_EOL;
                } elseif ((!array_key_exists($value[1],
                            $args) || $args[$value[1]] == "") && $value[3] == 1 && $value[4] == "") {
                    $this->writeLog("Define `" . $value[1] . "` under `" . $parent . "` section!", "error");
                    return false;
                }
            } elseif ($value[2] == "arr" && $args[$value[1]] != "" && is_array($args[$value[1]])) {
                $target .= $padding . '<' . $value[0] . '>' . PHP_EOL;
                foreach ($this->xml_schema[$action][$parent . "::" . $value[0]] as &$sub_value) {
                    if ($args[$value[1]][$sub_value[1]] != "") {
                        if ($sub_value[2] == "num") {
                            if (!is_numeric($args[$value[1]][$sub_value[1]])) {
                                $this->writeLog("Use numeric for `" . $value[1] . "` under `" . $parent . "::" . $value[0] . "` section!",
                                    "error");
                                return false;
                            } else {
                                $target .= $padding . TAB . '<' . $sub_value[0] . '>' . htmlspecialchars($args[$value[1]][$sub_value[1]]) . '</' . $sub_value[0] . '>' . PHP_EOL;
                            }
                        } elseif ($sub_value[2] == "str") {
                            if (!is_string($args[$value[1]][$sub_value[1]])) {
                                $this->writeLog("Use numeric for `" . $value[1] . "` under `" . $parent . "::" . $value[0] . "` section!",
                                    "error");
                                return false;
                            } else {
                                $target .= $padding . TAB . '<' . $sub_value[0] . '>' . htmlspecialchars($args[$value[1]][$sub_value[1]]) . '</' . $sub_value[0] . '>' . PHP_EOL;
                            }
                        } elseif ($sub_value[2] == "date") {
                            if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",
                                $args[$value[1]][$sub_value[1]])) {
                                $this->writeLog("Use date for `" . $value[1] . "` under `" . $parent . "::" . $value[0] . "` section!",
                                    "error");
                                return false;
                            } else {
                                $target .= $padding . TAB . '<' . $sub_value[0] . '>' . htmlspecialchars($args[$value[1]][$sub_value[1]]) . '</' . $sub_value[0] . '>' . PHP_EOL;
                            }
                        } elseif ($sub_value[2] == "bool") {
                            if (!is_bool($args[$value[1]][$sub_value[1]])) {
                                $this->writeLog("Use numeric for `" . $value[1] . "` under `" . $parent . "::" . $value[0] . "` section!",
                                    "error");
                                return false;
                            } else {
                                $target .= $padding . TAB . '<' . $sub_value[0] . '>' . htmlspecialchars($args[$value[1]][$sub_value[1]]) . '</' . $sub_value[0] . '>' . PHP_EOL;
                            }
                        }
                    }
                }
                $target .= $padding . '</' . $value[0] . '>' . PHP_EOL;
            }
        }

        return $target;

    }

    //
    // if connection type is set to auto, tries curl first, it there is an error, falls back to 
    // legacy file_get_contents solution
    //
    public function checkConnection($download)
    {
        if (!isset($_SESSION['connection'])) {

            $agent_url = $this->cfg["defaults"]["agent_url"];

            $ch = curl_init($agent_url);

            // setting ssl verification ok, defininig root certificate to validate connection to remote server

            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            // never ever set to false, as this carries huge security risk!!!
            // if you experience problems with ssl validation, use legacy data call instead! (see below)

            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_CAINFO, $this->cfg["defaults"]["root_cert_file"]);

            curl_setopt($ch, CURLOPT_NOBODY, true);

            curl_exec($ch);
            $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($retcode == "200") {
                $_SESSION['connection'] = "curl";
                $this->writeLog("Agent set to auto choose connection type. Curl connection OK, using it.", "debug");
                return $this->makeCurlCall($download);
            } else {
                $_SESSION['connection'] = "file_get_contents";
                $this->writeLog("Agent set to auto choose connection type. Curl connection FAILED, using legacy instead.",
                    "warn");
                return $this->makeLegacyCall($download);
            }

        } elseif ($_SESSION['connection'] == "curl") {
            return $this->makeCurlCall($download);
        } else {
            return $this->makeLegacyCall($download);
        }

        error_reporting($this->backupErrorLevel);
    }

    //
    // simple xml constructor for small queries
    //
    public function setSimpleXml($xml_name, $action, $args)
    {
        $this->xml_scope .= '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $this->xml_scope .= '<' . $xml_name . ' xmlns="http://www.szamlazz.hu/' . $xml_name . '" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.szamlazz.hu/' . $xml_name . ' ' . $xml_name . '.xsd ">' . PHP_EOL;
        $this->xml_scope .= $this->checkAndInsert($action, "args", $args, "");
    }

    //
    // preconstructs our xml scope, defines as "beallitasok"
    //
    public function predefineXml($xml_name, $action, $args)
    {
        $this->xml_scope .= '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $this->xml_scope .= '<' . $xml_name . ' xmlns="http://www.szamlazz.hu/' . $xml_name . '" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.szamlazz.hu/' . $xml_name . ' ' . $xml_name . '.xsd ">' . PHP_EOL;
        $this->xml_scope .= '<beallitasok>' . PHP_EOL;
        $this->xml_scope .= $this->checkAndInsert($action, "beallitasok", $args, "");
        $this->xml_scope .= '</beallitasok>' . PHP_EOL;

    }

    //
    // sets xml files header, defined as "fejlec"
    //
    public function setHeader($action, $args)
    {
        $this->xml_scope .= '<fejlec>' . PHP_EOL;
        $this->xml_scope .= $this->checkAndInsert($action, "fejlec", $args, "");
        $this->xml_scope .= '</fejlec>' . PHP_EOL;
    }

    //
    // sets seller info as "elado"
    //
    public function setSeller($action, $args)
    {
        $this->xml_scope .= '<elado>' . PHP_EOL;
        $this->xml_scope .= $this->checkAndInsert($action, "elado", $args, "");
        $this->xml_scope .= '</elado>' . PHP_EOL;
    }

    //
    // sets buyer info as "vevo"
    //
    public function setBuyer($action, $args)
    {
        $this->xml_scope .= '<vevo>' . PHP_EOL;
        $this->xml_scope .= $this->checkAndInsert($action, "vevo", $args, "");
        $this->xml_scope .= '</vevo>' . PHP_EOL;
    }

    //
    // sets waybill info as "fuvarlevel"
    //
    public function setWaybill($action, $args)
    {
        $this->xml_scope .= '<fuvarlevel>' . PHP_EOL;
        $this->xml_scope .= $this->checkAndInsert($action, "fuvarlevel", $args, "");
        $this->xml_scope .= '</fuvarlevel>' . PHP_EOL;
    }

    //
    // imports products listed on invoice under single xml child "tetelek"
    //
    public function setItems()
    {
        $this->xml_scope .= '<tetelek>' . PHP_EOL;
        $this->xml_scope .= $this->items_stack;
        $this->xml_scope .= '</tetelek>' . PHP_EOL;
    }

    //
    // constructs temporary xml scope from invoice products info seperage "tetel" tags
    //
    public function addItem($args)
    {
        $this->items_stack .= TAB . '<tetel>' . PHP_EOL;
        $this->items_stack .= $this->checkAndInsert("generateInvoice", "tetel", $args, TAB . TAB);
        $this->items_stack .= TAB . '</tetel>' . PHP_EOL;
    }

    //
    // marks xml files end
    //
    public function closeXml($xml_name)
    {
        $this->xml_scope .= '</' . $xml_name . '>';
        if (isset($this->cfg['defaults']['xml_file_save_path'])) {
            $savedate = date("Y-m-d-H-i-s");
            $savePath = $this->cfg['defaults']['xml_file_save_path'] . "/" . $savedate . ".xml";
            $this->writeLog("Request xml file saved to path: " . $savePath, "debug");
            file_put_contents($savePath, $this->xml_scope);
        }
    }

    //
    // starts curl data call (suggested)
    //
    public function makeCurlCall($download)
    {
        $agent_url = $this->cfg["defaults"]["agent_url"];

        $ch = curl_init($agent_url);

        // setting ssl verification ok, defininig root certificate to validate connection to remote server

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        // never ever set to false, as this carries huge security risk!!!
        // if you experience problems with ssl validation, use legacy data call instead! (see below)

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_CAINFO, $this->cfg["defaults"]["root_cert_file"]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_VERBOSE, true);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: multipart/form-data; boundary=' . $this->delim,
            'Content-Length: ' . strlen($this->post_request)
        ));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->post_request);

        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        if (isset($this->cfg['defaults']['cookiejar'])) {

            if (file_exists($this->cfg['defaults']['cookiejar']) && filesize($this->cfg['defaults']['cookiejar']) > 0 && strpos(file_get_contents($this->cfg['defaults']['cookiejar']),
                    'curl') === false) {
                file_put_contents($this->cfg['defaults']['cookiejar'], "");
                $this->writeLog("Cookiejar content changed.", "debug");
            }

            curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cfg['defaults']['cookiejar']);

            if (file_exists($this->cfg['defaults']['cookiejar']) && filesize($this->cfg['defaults']['cookiejar']) > 0) {
                curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cfg['defaults']['cookiejar']);
            }
        }

        $agent_response = curl_exec($ch);
        $http_error = curl_error($ch);
        $agent_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $agent_header = substr($agent_response, 0, $header_size);
        $agent_body = substr($agent_response, $header_size);

        if ($http_error != "") {
            $this->writeLog($http_error, "error");
            return false;
        }
        curl_close($ch);

        $header_array = preg_split('/\n|\r\n?/', $agent_header);
        return $this->handleResponse($header_array, $agent_body, $download);
    }

    public function getHeadersFromCurlResponse($headerContent)
    {
        $headers = array();
        $arrRequests = explode("\r\n\r\n", $headerContent);
        for ($index = 0; $index < count($arrRequests) - 1; $index++) {
            foreach (explode("\r\n", $arrRequests[$index]) as $i => $line) {
                if ($i === 0) {
                    $headers['http_code'] = $line;
                } else {
                    list ($key, $value) = explode(': ', $line);
                    $headers[$key] = $value;
                }
            }
        }
        return $headers;
    }

    //
    // starts file_get_contents call (use if curl not working)
    //
    public function makeLegacyCall($download)
    {

        $this->writeLog("Using legacy call", "debug");

        $cookietxt = "";
        $cookies = array();
        $stored_cookies = array();

        if (isset($this->cfg['defaults']['cookiejar']) && filesize($this->cfg['defaults']['cookiejar']) > 0 && strpos(file_get_contents($this->cfg['defaults']['cookiejar']),
                'curl') === false) {
            $stored_cookies = unserialize(file_get_contents($this->cfg['defaults']['cookiejar']));
            $cookietxt = "\r\n" . "Cookie: JSESSIONID=" . $stored_cookies["JSESSIONID"];
        }

        $this->context = stream_context_create(array(
            'http' => array(
                "method" => "POST",
                "header" => "Content-Type: multipart/form-data; boundary=" . $this->delim . $cookietxt,
                "content" => $this->post_request
            )
        ));
        $response = file_get_contents($this->cfg["defaults"]["agent_url"], false, $this->context);

        foreach ($http_response_header as $header) {
            if (preg_match('/^Set-Cookie:\s*([^;]+)/', $header, $matches)) {
                parse_str($matches[1], $temp);
                $cookies += $temp;
            }
        }

        if (isset($this->cfg['defaults']['cookiejar']) && isset($cookies['JSESSIONID'])) {
            if (file_exists($this->cfg['defaults']['cookiejar']) && filesize($this->cfg['defaults']['cookiejar']) > 0 && strpos(file_get_contents($this->cfg['defaults']['cookiejar']),
                    'curl') !== false) {
                file_put_contents($this->cfg['defaults']['cookiejar'], serialize($cookies));
                $this->writeLog("Cookiejar content changed.", "debug");
            } elseif (file_exists($this->cfg['defaults']['cookiejar']) && filesize($this->cfg['defaults']['cookiejar']) > 0 && strpos(file_get_contents($this->cfg['defaults']['cookiejar']),
                    'curl') === false && ($stored_cookies != $cookies)) {
                file_put_contents($this->cfg['defaults']['cookiejar'], serialize($cookies));
                $this->writeLog("Cookiejar content changed.", "debug");
            } elseif (file_exists($this->cfg['defaults']['cookiejar']) && filesize($this->cfg['defaults']['cookiejar']) == 0) {
                file_put_contents($this->cfg['defaults']['cookiejar'], serialize($cookies));
                $this->writeLog("Cookiejar content changed.", "debug");
            }
        }


        return $this->handleResponse($http_response_header, $response, $download);
    }

    //
    // sets invoice payment info
    //
    public function addPayment($args)
    {
        $this->payments_stack .= TAB . '<kifizetes>' . PHP_EOL;
        $this->payments_stack .= $this->checkAndInsert("payInvoice", "kifizetes", $args, TAB . TAB);
        $this->payments_stack .= TAB . '</kifizetes>' . PHP_EOL;
    }

    public function setPayments()
    {
        $this->xml_scope .= $this->payments_stack;
    }

    //
    // handles data call response, be it either curl or file_get_contents
    //
    public function handleResponse($headers, $agent_body, $download)
    {
        foreach ($headers as $val) {
            if (substr($val, 0, strlen('szlahu')) === 'szlahu') {
                $got_szlahu = true;
                if (substr($val, 0, strlen('szlahu_error:')) === 'szlahu_error:') {
                    $got_error = true;
                    $agent_error = substr($val, strlen('szlahu_error:'));
                }
                if (substr($val, 0, strlen('szlahu_error_code:')) === 'szlahu_error_code:') {
                    $got_error = true;
                    $agent_error_code = substr($val, strlen('szlahu_error_code:'));
                }
                if (substr($val, 0, strlen('szlahu_szamlaszam:')) === 'szlahu_szamlaszam:') {
                    $invoice_number = trim(substr($val, strlen('szlahu_szamlaszam:')));
                }
            }
        }
        if ($got_szlahu != true) {
            $this->writeLog("Agent call error, invalid response. " . $headers[0], "error");
            return false;
        }

        if ($got_error == true) {
            $this->writeLog("Agent call error code:" . $agent_error_code . " " . iconv('UTF8', 'ascii//TRANSLIT',
                    urldecode($agent_error)), "error");
            return false;
        } else {
            $this->writeLog("Agent call succesfully completed", "debug");
            if ($download == true) {
                $this->writeLog("Answer pdf file saved to path: " . $this->cfg['defaults']['pdf_file_save_path'] . "/" . $invoice_number . ".pdf",
                    "debug");
                file_put_contents($this->cfg['defaults']['pdf_file_save_path'] . "/" . $invoice_number . ".pdf",
                    $agent_body);
            }
            if (isset($this->cfg["defaults"]["return_pdf_as_result"]) && $this->cfg["defaults"]["return_pdf_as_result"] == true) {
                return array("invoice_number" => $invoice_number, "pdf" => $agent_body);
            } elseif (isset($this->cfg["defaults"]["return_invoice_number_as_result"]) && $this->cfg["defaults"]["return_invoice_number_as_result"] == true) {
                return $invoice_number;
            } else {
                return true;
            }
        }

        error_reporting($this->backupErrorLevel);

    }

    //
    // builds post query from scratch
    //
    public function constructQuery($xml_file_name)
    {
        $this->delim = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 16);
        $this->post_request = "--" . $this->delim . "\r\n";
        $this->post_request .= 'Content-Disposition: form-data; name="' . $xml_file_name . '"; filename="' . $xml_file_name . '"' . "\r\n";
        $this->post_request .= 'Content-Type: text/xml' . "\r\n";
        $this->post_request .= "\r\n";
        $this->post_request .= $this->xml_scope . "\r\n";
        $this->post_request .= "--" . $this->delim . "--\r\n";
    }

    public function runCall($if_download)
    {
        switch ($this->cfg['defaults']['call_method']) {
            case "auto":
                return $this->checkConnection($if_download);
                break;
            case "default":
                return $this->makeCurlCall($if_download);
                break;
            case "legacy":
                return $this->makeLegacyCall($if_download);
                break;
        }
    }

    //
    // function to generate new invoice
    //
    public function _generateInvoice($settings, $header, $seller, $buyer, $waybill)
    {
        $this->predefineXml("xmlszamla", "generateInvoice", $settings);
        $this->setHeader("generateInvoice", $header);
        $this->setSeller("generateInvoice", $seller);
        $this->setBuyer("generateInvoice", $buyer);
        $this->setWaybill("generateInvoice", $waybill);
        $this->setItems();
        $this->closeXml("xmlszamla");
        $this->constructQuery("action-xmlagentxmlfile");
        $this->writeLog("Fired generateInvoice function", "debug");
        return $this->runCall($settings['download_invoice']);
    }

    //
    // function to generate a reverse (storno) invoice
    //
    public function _reverseInvoice($settings, $header, $seller, $buyer)
    {
        $this->predefineXml("xmlszamlast", "reverseInvoice", $settings);
        $this->setHeader("reverseInvoice", $header);
        $this->setSeller("reverseInvoice", $seller);
        $this->setBuyer("reverseInvoice", $buyer);
        $this->closeXml("xmlszamlast");
        $this->constructQuery("action-szamla_agent_st");
        $this->writeLog("Fired reverseInvoice function", "debug");
        return $this->runCall($settings['download_invoice']);
    }

    //
    // function to commit a payment to given invoice
    //
    public function _payInvoice($settings)
    {
        $this->predefineXml("xmlszamlakifiz", "payInvoice", $settings);
        $this->setPayments();
        $this->closeXml("xmlszamlakifiz");
        $this->constructQuery("action-szamla_agent_kifiz");
        $this->writeLog("Fired payInvoice function", "debug");
        return $this->runCall($settings['download_invoice']);
    }

    //
    // function to get invoice pdf copy of already existing one
    //
    public function _requestInvoicePDF($settings)
    {
        $this->cfg["defaults"]["return_pdf_as_result"] = true;
        $this->setSimpleXml("xmlszamlapdf", "requestInvoicePDF", $settings);
        $this->closeXml("xmlszamlapdf");
        $this->constructQuery("action-szamla_agent_pdf");
        $this->writeLog("Fired requestInvoicePDF function", "debug");
        return $this->runCall($settings['download_invoice']);
    }

    //
    // deconstruct class function
    //
    public function __destruct()
    {
        $this->writeLog("Agent destroyed", "debug");
    }

}
