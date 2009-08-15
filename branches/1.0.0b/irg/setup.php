<?php
function plugin_init_irg(){
    // This is where you hook into the plugin archetecture
    global $plugin_hooks;
    $plugin_hooks['draw_navigation_text']['irg'] = 'irg_draw_navigation_text';
    $plugin_hooks['config_arrays']['irg'] = 'irg_config_arrays';
    // $plugin_hooks['config_settings']['irg'] = 'irg_config_settings';
    $plugin_hooks['top_header_tabs']['irg'] = 'irg_show_tab';
    $plugin_hooks['top_graph_header_tabs']['irg'] = 'irg_show_tab';
}

function irg_version(){
    return plugin_irg_version();
}

function plugin_irg_version(){
    return array(
            'name'      => 'irg',
            'version'   => '1.0.0b',
            'longname'  => 'Inox Report Generator',
            'author'    => 'Piyaboot Thavilthirakul',
            'homepage'  => 'http://inox.co.th',
            'email'     => 'piyaboot@inox.co.th',
            'url'       => 'http://inox.co.th'
            );
}

function plugin_irg_install(){
    irg_setup_tables();

    api_plugin_register_realm('irg', 'IRG.php', 'IRG Cacti', 1);
    api_plugin_register_realm('irg', 'Controller.php', 'Controller', 1);
    api_plugin_register_realm('irg', 'sample_report.php', 'IRG Sample Report', 1);
    api_plugin_register_realm('irg', 'make_report.php', 'IRG Make', 1);
    api_plugin_register_realm('irg', 'report_archives.php', 'IRG Report Archives', 1);
    api_plugin_register_realm('irg', 'api_doc.php', 'IRG API Documentation', 1);
    api_plugin_register_realm('irg', 'exclude.php', 'Exclude Period', 1);
    api_plugin_register_realm('irg', 'rest/index.php', 'RestAPI', 1);


    api_plugin_register_hook('irg', 'config_arrays', 'irg_config_arrays', 'setup.php');
    api_plugin_register_hook('irg', 'top_header_tabs', 'irg_show_tab', 'setup.php');
    api_plugin_register_hook('irg', 'top_graph_header_tabs', 'irg_show_tab', 'setup.php');
    api_plugin_register_hook('irg', 'draw_navigation_text', 'irg_draw_navigation_text', 'setup.php');
    api_plugin_register_hook('irg', 'config_form', 'irg_config_form', 'setup.php');
    api_plugin_register_hook('irg', 'config_settings', 'irg_config_settings', 'setup.php');
}

function plugin_irg_uninstall(){
    // remove plugin tables
    db_execute("DROP TABLE `irg_reports`");
    db_execute("DROP TABLE `irg_report_prime`");
    db_execute("DROP TABLE `irg_report_host`");

    db_execute("DELETE FROM `settings` WHERE `name` like 'irg\_%'");
    api_plugin_remove_realms('irg');
}

function irg_draw_navigation_text($nav){
    $nav["irg.php:"] = array(
        "title" => "Inox Report Generator",
        "mapping" => "index.php:",
        "url" => "irg.php",
        "level" => "1"
    );
    $nav["sample_report.php:"] = array(
        "title" => "Sample Report",
        "mapping" => "irg.php:",
        "url" => "sample_report.php",
        "level" => "2"
    );
    $nav["make_report.php:"] = array(
        "title" => "Make Report",
        "mapping" => "irg.php:",
        "url" => "make_report.php",
        "level" => "2"
    );
    $nav["report_archives.php:"] = array(
        "title" => "Report Archives",
        "mapping" => "irg.php:",
        "url" => "report_archives.php",
        "level" => "2"
    );
    $nav["exclude.php:"] = array("title" => "Excluded Periods",
        "mapping" => "irg.php:",
        "url" => "exclude.php",
        "level" => "2"
    );
    $nav["api_doc.php:"] = array("title" => "API Documentation",
        "mapping" => "irg.php:",
        "url" => "doc/api_doc.php:",
        "level" => "2"
    );

    return $nav;
}

function irg_config_arrays(){
    $realm_id=150;
    global $user_auth_realms, $user_auth_realm_filenames;

    $user_auth_realms[$realm_id]='irg';
    $user_auth_realm_filenames['irg.php'] = $realm_id;
}

function irg_config_settings(){
    //this puts the following form elements on the Settings->Misc tab in cacti
    //require('irg_cacti_api.php');
    //$api = IRG::getInstance();
    //$rraTypes = $api->getCactiRRAType();

    //$rraConfig = array();
    //foreach($rraTypes as $rraType){
    //    $rraConfig[$rraType['id']] =  $rraType['name'];
    //}

    global $tabs, $settings;
    $tabs["irg"] = "Cacti Report";

    $temp = array(
        "irg_header" => array(
            "friendly_name" => "General",
            "method" => "spacer",
            ),
        "irg_report_company_name" => array(
            "friendly_name" => "Report company name",
            "description" => "",
            "method" => "textbox",
            "max_length" => "255",
            ),
        //"irg_default_rratype" => array(
        //    "friendly_name" => "Default report type",
        //    "description" => "",
        //    "method" => "drop_array",
        //    "array" => $rraConfig,
        //)
    );
    if(isset($settings["irg"]))
        $settings["irg"] = array_merge($settings["misc"], $temp);
    else
        $settings["irg"]=$temp;
}

function irg_show_tab(){
    global $config;

    if(isset($_SESSION["sess_user_id"])){

        $user_id = $_SESSION["sess_user_id"];

        $irg_realm = db_fetch_cell("SELECT id FROM plugin_config WHERE directory = 'irg'");
        $irg_enabled = db_fetch_cell("SELECT status FROM plugin_config WHERE directory = 'irg'");

        if($irg_enabled == "1"){
            if(api_user_realm_auth('irg.php')){
                $cp = false;
                if(basename($_SERVER["PHP_SELF"]) == "irg.php"){ $cp = true; }

                print '<a href="' . $config['url_path'] . 'plugins/irg/irg.php"><img src="'
                     . $config['url_path'] . 'plugins/irg/images/tab_irg'
                     .($cp ? "_down": "") . '.gif" alt="irg" align="absmiddle" border="0"></a>';
            }
        }
    }
}

function irg_setup_tables(){
    $realm_id=150;
    global $config, $database_default;
    include_once($config["library_path"] . "/database.php");

    // Set the version
    $version = irg_version();
    $version = $version['version'];
    db_execute("REPLACE INTO settings(name, value) VALUES('plugin_irg_version', '$version')");

    $found = false;

    if(!$found){
        $sql = "INSERT INTO `user_auth_realm` VALUES($realm_id, 1);";
        $result = mysql_query($sql); // or die(mysql_error());
    }

    $result = db_fetch_assoc('show tables');

    $tables = array();
    $sql = array();

    if(count($result) > 1){
        foreach($result as $index => $arr){
            foreach($arr as $t){
                $tables[] = $t;
            }
        }
    }

    if(!in_array('irg_reports', $tables)){
        $sql[] = "CREATE TABLE `irg_reports` (
                    `id` int(11) unsigned NOT NULL auto_increment,
                    `rra_type_id` mediumint(8) unsigned NOT NULL default '1',
                    `graph_start` timestamp NOT NULL,
                    `graph_end` timestamp NOT NULL,
                    `date` datetime NOT NULL default '0000-00-00 00:00:00',
                    PRIMARY KEY (`id`)
                  ) ENGINE=MyISAM;";
    }

    if(!in_array('irg_report_prime', $tables)){
        $sql[] = "CREATE TABLE `irg_report_prime` (
                    `report_id` int(11) unsigned NOT NULL,
                    `prime_start` time NOT NULL default '00:00:00',
                    `prime_end` time NOT NULL default '00:00:00'
                    ) ENGINE=MyISAM;";
    }

    if(!in_array('irg_report_host', $tables)){
        $sql[] = "CREATE TABLE `irg_report_host` (
                    `report_id` int(11) unsigned NOT NULL,
                    `host_id` mediumint(8) unsigned NOT NULL default '0'
                ) ENGINE=MyISAM;";
    }

    if(!in_array('irg_exclude_period', $tables)){
        $sql[] = "CREATE TABLE `irg_exclude_period` (
                    `id` int(11) unsigned NOT NULL auto_increment,
                    `exc_start` timestamp NOT NULL,
                    `exc_end` timestamp NOT NULL,
                    `note` text
                )ENGINE=MyISAM;";
    }

    if(!empty($sql)){
        for($a = 0; $a < count($sql); $a++){
             $result = db_execute($sql[$a]);
        }
    }
}
?>
