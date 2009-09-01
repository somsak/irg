<?php
function plugin_init_repoti(){
    // This is where you hook into the plugin archetecture
    global $plugin_hooks;
    $plugin_hooks['draw_navigation_text']['repoti'] = 'repoti_draw_navigation_text';
    $plugin_hooks['config_arrays']['repoti'] = 'repoti_config_arrays';
    $plugin_hooks['top_header_tabs']['repoti'] = 'repoti_show_tab';
    $plugin_hooks['top_graph_header_tabs']['repoti'] = 'repoti_show_tab';
}

function repoti_version(){
    return plugin_repoti_version();
}

function plugin_repoti_version(){
    return array(
            'name'      => 'Repoti',
            'version'   => '1.0.0b',
            'longname'  => 'Repoti',
            'author'    => 'Piyaboot Thavilthirakul',
            'homepage'  => 'http://inox.co.th',
            'email'     => 'piyaboot@inox.co.th',
            'url'       => 'http://inox.co.th'
            );
}

function plugin_repoti_install(){
    repoti_setup_tables();

    api_plugin_register_realm('repoti', 'repoti.php', 'Inox Cacti Report', 1);
    api_plugin_register_realm('repoti', 'make_report.php', 'Make a report', 1);
    api_plugin_register_realm('repoti', 'host_and_graph_info.php', 'Host and Graph info', 1);

    api_plugin_register_hook('repoti', 'config_arrays', 'repoti_config_arrays', 'setup.php');
    api_plugin_register_hook('repoti', 'top_header_tabs', 'repoti_show_tab', 'setup.php');
    api_plugin_register_hook('repoti', 'top_graph_header_tabs', 'repoti_show_tab', 'setup.php');
    api_plugin_register_hook('repoti', 'draw_navigation_text', 'repoti_draw_navigation_text', 'setup.php');
    api_plugin_register_hook('repoti', 'config_form', 'repoti_config_form', 'setup.php');
    api_plugin_register_hook('repoti', 'config_settings', 'repoti_config_settings', 'setup.php');
}

function plugin_repoti_uninstall(){
    // remove plugin tables
    /*
    db_execute("DROP TABLE `repoti_reports`");
    db_execute("DROP TABLE `repoti_report_prime`");
    db_execute("DROP TABLE `repoti_report_host`");
    */

    db_execute("DELETE FROM `settings` WHERE `name` like 'repoti\_%'");
    api_plugin_remove_realms('repoti');
}

function repoti_draw_navigation_text($nav){
    $nav["repoti.php:"] = array(
        "title" => "Report",
        "mapping" => "index.php:",
        "url" => "repoti.php",
        "level" => "1"
        );

        $nav["make_report.php:"] = array(
        "title" => "Make report",
        "mapping" => "index.php:",
        "url" => "repoti.php",
        "level" => "1"
        );

        $nav["host_and_graph_info.php:"] = array(
        "title" => "Host adn Graph info",
        "mapping" => "index.php:",
        "url" => "repoti.php",
        "level" => "1"
        );

        return $nav;
}

function repoti_config_arrays(){
    $realm_id=152;
    global $user_auth_realms, $user_auth_realm_filenames;

    $user_auth_realms[$realm_id]='repoti';
    $user_auth_realm_filenames['repoti.php'] = $realm_id;
}

function repoti_config_settings(){
    global $tabs, $settings;
    $tabs["repoti"] = "Report";

    $temp = array(
        "repoti_header" => array(
            "friendly_name" => "General",
            "method" => "spacer"),
        "repoti_report_company_name" => array(
            "friendly_name" => "Report company name",
            "description" => "",
            "method" => "textbox",
            "max_length" => "255")
    );

    if(isset($settings["repoti"])){
        $settings["repoti"] = array_merge($settings["misc"], $temp);
    }else{
        $settings["repoti"]=$temp;
    }
}

function repoti_show_tab(){
    global $config;

    if(isset($_SESSION["sess_user_id"])){

        $user_id = $_SESSION["sess_user_id"];

        $repoti_realm = db_fetch_cell("SELECT id FROM plugin_config WHERE directory = 'repoti'");
        $repoti_enabled = db_fetch_cell("SELECT status FROM plugin_config WHERE directory = 'repoti'");

        if($repoti_enabled == "1"){
            if(api_user_realm_auth('repoti.php')){
                $cp = false;
                if(basename($_SERVER["PHP_SELF"]) == "repoti.php"){ $cp = true; }

                print '<a href="' . $config['url_path'] . 'plugins/repoti/repoti.php"><img src="'
                . $config['url_path'] . 'plugins/repoti/images/tab_repoti.gif" alt="repoti" align="absmiddle" border="0"></a>';
                //.($cp ? "_down": "") . '.gif" alt="repoti" align="absmiddle" border="0"></a>';
            }
        }
    }
}

function repoti_setup_tables(){
    $realm_id=152;
    global $config, $database_default;
    include_once($config["library_path"] . "/database.php");

    // Set the version
    $version = repoti_version();
    $version = $version['version'];
    db_execute("REPLACE INTO settings(name, value) VALUES('plugin_repoti_version', '$version')");

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

    if(!in_array('repoti_template', $tables)){
        $sql[] = "CREATE TABLE `repoti_template` (
                    `id` int(11) unsigned NOT NULL auto_increment,
                    `template_name` char(255) NOT NULL,
                    `rratype_id` int(11) NOT NULL,
                    `graph_ids` text NOT NULL,
                    `begin_prime` char(5) NOT NULL,
                    `end_prime` char(5) NOT NULL,
                    PRIMARY KEY (`id`)
                )ENGINE=MyISAM;";
    }

    if(!empty($sql)){
        for($a = 0; $a < count($sql); $a++){
            $result = db_execute($sql[$a]);
        }
    }
}
?>
