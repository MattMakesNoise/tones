<?php
/**
 * Plugin Name:  Tones
 * Plugin URI:   https://mattsadev.com
 * Description:  Serve test tones via Rest API.
 * Version:      1.0.0
 * Requires PHP: 8.0
 * Author:       Matt Jones
 * Text Domain:  tones
 * Domain Path:  /languages
 *
 * @version 1.0.0
 */
namespace Mattsadev\Tones;

use Exception;
use Mattsadev\Tones\Tones_Post_Type;
use Mattsadev\Tones\Tones_Endpoint;


/**
 * Copyright (c) 2023 Matt Jones
 */

// Use composer autoload.
require_once __DIR__ . '/vendor/autoload_packages.php';
require_once __DIR__ . '/shortcut-function.php';

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Main initiation class.
 *
 * @since 1.0.0
 */
class Main {
    /**
     * Current version.
     *
     * @var    string
     * @since 1.0.0
     */
    const VERSION = '1.0.0';

    /**
     * What version of maintenance upgrades we are at.
     */
    const MAINTENANCE_VERSION = 1;

    /**
     * Settings Page slug
     */
    const PAGE_SLUG = 'tones';


    /**
     * The token, used to prefix values in DB.
     *
     * @var   string
     * @since 1.0.0
     */
    public $_token = 'mad_tones';

    /**
     * URL of plugin directory with trailing slash.
     *
     * @var    string
     * @since 1.0.0
     */
    public $url = '';

    /**
     * Path of plugin directory with trailing slash.
     *
     * @var    string
     * @since 1.0.0
     */
    public $path = '';

    /**
     * Plugin basename.
     *
     * @var    string
     * @since 1.0.0
     */
    protected $basename = '';

    /**
     * The main plugin file.
     *
     * @var string
     * @since 1.0.0
     */
    public $file;

    /**
     * Detailed activation error messages.
     *
     * @var    array
     * @since 1.0.0
     */
    protected $activation_errors = [];

    /**
     * Singleton instance of plugin.
     *
     * @since 1.0.0
     * @var    Main
     */
    protected static $instance = null;

    /**
     * @var Admin_Settings Only initialised in WP admin.
     * @since 1.0.0
     */
    public $settings;

    /**
     * REST API endpoints.
     *
     * @since 1.1.0
     * @var REST_API|null
     */
    public $rest_api = null;

    /**
     * Creates or returns an instance of this class.
     *
     * @since 1.0.0
     * @return Main A single instance of this class.
     */
    public static function instance(): Main {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Sets up our plugin.
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->file     = basename( __FILE__ );
        $this->basename = plugin_basename( __FILE__ );
        $this->url      = plugin_dir_url( __FILE__ );
        $this->path     = plugin_dir_path( __FILE__ );
    }

    /**
     * Hooks run at plugins_loaded level 4.
     *
     * @since 1.0.0
     * @return void
     */
    public function early_hooks(): void {
        Tones_Post_Type::init();
        Tones_Endpoint::init();
    }

    /**
     * Add hooks and filters.
     *
     * @return void
     * @since 1.0.0
     */
    public function hooks(): void {
        // Initialise features early
        add_action( 'init', [ $this, 'early_init' ], 0 );

        // Initialise REST API
        add_action( 'bp_rest_api_init', [ $this, 'api_init' ] );

        // Add settings link to plugins page
        add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), [ $this, 'add_settings_link' ] );
    }

    /**
     * Activate the plugin.
     *
     * @return void
     * @since 1.0.0
     */
    public function _activate(): void {
        // Bail early if requirements aren't met.
        if ( ! $this->check_requirements() ) {
            return;
        }

        // Make sure any rewrite functionality has been loaded.
        flush_rewrite_rules();
    }

    /**
     * Deactivate the plugin.
     * Uninstall routines should be in uninstall.php.
     *
     * @return void
     * @since 1.0.0
     */
    public function _deactivate() {
        // Add deactivation cleanup functionality here.
    }

    /**
     * Init hooks before other plugins have initialised.
     *
     * Hooked onto 'init' at priority 0.
     *
     * @return void
     * @since 1.0.0
     */
    public function early_init(): void {
        // Bail early if requirements aren't met.
        if ( ! $this->check_requirements() ) {
            return;
        }

        // Load translated strings for plugin.
        load_plugin_textdomain( 'tones', false, dirname( $this->basename ) . '/languages/' );

        // Perform maintenance
        $this->maybe_run_maintenance();
    }

    /**
     * Check if the plugin meets requirements and
     * disable it if they are not present.
     *
     * @since 1.0.0
     *
     * @return bool True if requirements met, false if not.
     */
    public function check_requirements(): bool {
        // Bail early if plugin meets requirements.
        if ( $this->meets_requirements() ) {
            return true;
        }

        // Add a dashboard notice.
        add_action( 'all_admin_notices', array( $this, 'requirements_not_met_notice' ) );

        // Deactivate our plugin.
        add_action( 'admin_init', array( $this, 'deactivate_me' ) );

        // Didn't meet the requirements.
        return false;
    }

    /**
     * Deactivates this plugin, hook this function on admin_init.
     *
     * @return void
     * @since 1.0.0
     */
    public function deactivate_me(): void {
        // We do a check for deactivate_plugins before calling it, to protect
        // any developers from accidentally calling it too early and breaking things.
        if ( function_exists( 'deactivate_plugins' ) ) {
            deactivate_plugins( $this->basename );
        }
    }

    /**
     * Check that all plugin requirements are met.
     *
     * @since 1.0.0
     * @return bool True if requirements are met.
     */
    public function meets_requirements(): bool {
        $valid = true;

        return $valid;
    }

    /**
     * Adds a notice to the dashboard if the plugin requirements are not met.
     *
     * @return void
     * @since 1.0.0
     */
    public function requirements_not_met_notice(): void {
        // Compile default message.
        $default_message = sprintf(
            __(
                    ),
            admin_url( 'plugins.php' )
        );

        // Default details to null.
        $details = null;

        // Add details if any exist.
        if ( $this->activation_errors ) {
            $details = '<small>' . implode( '</small><br /><small>', $this->activation_errors ) . '</small>';
        }

        // Output errors.
        ?>
        <div id="message" class="error">
            <p><?php echo wp_kses_post( $default_message ); ?></p>
            <?php echo wp_kses_post( $details ); ?>
        </div>
        <?php
    }

    /**
     * Magic getter for our object.
     *
     * @param string $field Field to get.
     *
     * @return mixed         Value of the field.
     * @throws Exception    Throws an exception if the field is invalid.
     * @since 1.0.0
     */
    public function __get( string $field ) {
        switch ( $field ) {
            case 'version':
                return self::VERSION;
            case 'basename':
            case 'url':
            case 'path':
                return $this->$field;
            default:
                throw new Exception( 'Invalid ' . __CLASS__ . ' property: ' . $field );
        }
    }

    /**
     * Check if any necessary maintenance tasks need to be run and execute them.
     *
     * @return void
     * @since 1.0.0
     */
    public function maybe_run_maintenance(): void {
        if ( ! is_admin() ) {
            return;
        }

        $maintenance_version = (int) get_option( $this->_token . '_maint_version' );

        if ( $maintenance_version < self::MAINTENANCE_VERSION ) {
            for ( $version = $maintenance_version + 1; $version <= self::MAINTENANCE_VERSION; $version++ ) {
                Maintenance::run( $version );
            }
        }

        update_option( $this->_token . '_maint_version', self::MAINTENANCE_VERSION );
    }

    /**
     * Add settings link to plugin list table.
     *
     * @param array $links Existing links.
     *
     * @return array Modified links.
     * @since 1.0.0
     */
    public function add_settings_link( array $links ): array {
        $settings_link = '<a href="options-general.php?page=' . self::PAGE_SLUG . '">' .
            __( 'Settings', 'tones' ) .
            '</a>';

        $links[] = $settings_link;

        return $links;
    }
}

// Kick it off.
add_action( 'plugins_loaded', [ Main::instance(), 'early_hooks' ], 4 );
add_action( 'plugins_loaded', [ Main::instance(), 'hooks' ] );

// Activation and deactivation.
register_activation_hook( __FILE__,   [ Main::instance(), '_activate' ] );
register_deactivation_hook( __FILE__, [ Main::instance(), '_deactivate' ] );