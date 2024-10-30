<?php
namespace BrightEdge\Wordpress;

include_once("WidgetLem.php");
include_once("Controller.php");
include_once("View.php");
include_once("Settings.php");

class BEIXFLoader {
    protected $base_path;
    protected $path;

    public function __construct($plugin_path){
        // Prevent direct access
        defined('ABSPATH' ) or die( 'No direct access allowed!');
        $this->base_path = $plugin_path;

/*********** Custom code start ***************/
        // Skip frontend-specific setup if WP-CLI is active
        if (defined('WP_CLI') && WP_CLI) {
            return; // Exit the constructor if WP-CLI is active
        }
/*********** Custom code end ***************/

        add_action(
            'admin_init',
            [
                __NAMESPACE__ . '\BEIXFSettings',
                'register_settings',
            ]
        );
        add_action(
            'admin_menu',
            [
                __NAMESPACE__ . '\BEIXFSettings',
                'add_settings_page',
            ]
        );

        // If client sdk class not included, likely not a composer
        // install and require the static path
        if (!class_exists('BEIXFClient')){
            $this->getSDKPath();
            if ($this->path) {
                // loads the static file SDK
                require $this->path;
                $this->load();
            } else {
                echo '<!-- BEIXF SDK could not be loaded from static path-->';
            }
        } else {
            $this->load();
        }
    }

    protected function getSDKPath(){
         // Default SDK path
        $path_default = $this->base_path . 'src/be_ixf_client.php';

        if (file_exists($path_default)) {
            $this->path = $path_default;
        } else {
            $this->path = '';
        }
    }

    public static function activationHook(){
        add_option(
            'be_ixf',
            [
                BEIXFConstants::STATUS => BEIXFConstants::STATUS_DISABLED,
                BEIXFConstants::ACCOUNT_ID => '',
                BEIXFConstants::API_ENDPOINT => '',
                BEIXFConstants::CANONICAL_HOST => '',
                BEIXFConstants::PROTOCOL => BEIXFConstants::PROTOCOL_HTTPS,
                BEIXFConstants::WHITE_LIST => 'ixf',
                BEIXFConstants::EXCLUDE_HOMEPAGE => BEIXFConstants::EXCLUDE_OPTION,
                BEIXFConstants::STRATEGY => BEIXFConstants::PRE_CONTENT,
            ]
        );
    }

    public static function uninstallHook(){
        delete_option('be_ixf');
    }

    public static function registerWidget(){
        $sidebar_args = array(
            'name' => __('BrightEdge Autopilot'),
            'id' => "be-foundations-sidebar",
            'description' => 'Provides a WordPress sidebar to deploy into themes and host BrightEdge Autopilot widgets.',
            'class' => 'be-ixf-widget',
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget' => "</div>\n",
            'before_title' => '<h3 class="widgettitle">',
            'after_title' => "</h3>\n",
        );
        register_sidebar( $sidebar_args );
        register_widget( __NAMESPACE__ . '\BEIXFWidgetLem');
    }

    protected function load(){
        // Skip the load method if WP-CLI is active
        if (defined('WP_CLI') && WP_CLI) {
            return;
        }

        $instance = new BEIXFController();
        // Hook ensures rendering after conditional tags are available
        add_action('template_redirect', function()  use ($instance) {
            // Hooks and renders output based on integration strategy
            new BEIXFView($instance);
        });
    }
}
