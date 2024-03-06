<?php

/**
 * @package Woo_Rewards_Program
 */
/*
Plugin Name: Woo Rewards Program
Plugin URI: https://method.au/
Description: Our plugin lets customers earn points for every dollar spent, unlocking enticing product discounts. Elevate your store and turn one-time buyers into loyal customers.
Version: 1.0.0
Requires at least: 5.8
Requires PHP: 5.6.20
Author: Cullen Webber
Author URI: https://method.au/
License: GPLv2 or later
Text Domain: woo-rewards-program
*/


//Prevent direct access to the plugin
defined('ABSPATH') || die('Brother, you have no permission to access this');

//Check if the user has woocommerce installed
include_once(ABSPATH . 'wp-admin/includes/plugin.php');
if (!is_plugin_active('woocommerce/woocommerce.php')) {
    add_action('admin_notices', 'woo_rewards_program_missing_woocommerce_notice');
    return;
}

/**
 * Display wordpress error message that woocommerce is not installed
 * then uninstall woo rewards program
 * 
 */
function woo_rewards_program_missing_woocommerce_notice()
{
?>
    <div class="error">
        <p><?php _e('Woo Rewards Program requires WooCommerce to be installed and active.', 'woo-rewards-program'); ?></p>
    </div>
<?php
    //Deactivate the plugin to prevent any issues until Woocommerce is installed
    deactivate_plugins(plugin_basename(__FILE__));
}


class Woo_Rewards_Program
{
    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      The_Motion_Academy_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    public function __construct()
    {

        $this->version = '1.0.0';
        $this->plugin_name = 'woo-rewards-program';

        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    //Load the dependency files required for the plugin to work
    private function load_dependencies()
    {

        require_once plugin_dir_path(__FILE__) . 'includes/class-woo-rewards-program-loader.php';

        /**
         * The class responsible for all the UTILs for both admin and public classes
         */
        require_once plugin_dir_path(__FILE__) . 'includes/class-woo-rewards-program-utils.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path(__FILE__) . 'admin/class-woo-rewards-program-admin.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path(__FILE__) . 'public/class-woo-rewards-program-public.php';

        //Intializes the loader class
        $this->loader = new Woo_Rewards_Program_Loader();
    }

    private function define_admin_hooks()
    {

        //Intializes the Admin class
        $plugin_admin = new Woo_Rewards_Program_Admin($this->get_plugin_name(), $this->get_version());

        //Create the admin menu
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_woo_rewards_submenu');

        //Handle the settings saved in the admin area
        $this->loader->add_action('admin_init', $plugin_admin, 'woo_rewards_program_handle_settings_save');

        //Check if the user has disabled the plugin in the woocommerce admin interface
        if (!Woo_Rewards_Program_Utils::woo_rewards_is_enabled()) {
            return false;
        }

        //Load in the styles & scripts for the admin area
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

        //Handles the point update system when an order is completed
        $this->loader->add_action('woocommerce_new_order', $plugin_admin, 'woo_rewards_store_points');

        //Adds the addtional fields to the edit user interface
        $this->loader->add_action('show_user_profile', $plugin_admin, 'render_additional_user_setting_fields');
        $this->loader->add_action('edit_user_profile', $plugin_admin, 'render_additional_user_setting_fields');

        //Handles the additional fields on save in the edit user interface
        $this->loader->add_action('personal_options_update', $plugin_admin, 'save_woo_rewards_edit_user');
        $this->loader->add_action('edit_user_profile_update', $plugin_admin, 'save_woo_rewards_edit_user');
    }

    private function define_public_hooks()
    {

        //Check if the user has disabled the plugin in the woocommerce admin interface
        if (!Woo_Rewards_Program_Utils::woo_rewards_is_enabled()) {
            return false;
        }

        //Intializes the public class
        $plugin_public = new Woo_Rewards_Program_Public($this->get_plugin_name(), $this->get_version());

        //Load in the styles and scripts for the public area
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');

        //Display the number of points before the cart
        $this->loader->add_action('woocommerce_before_cart', $plugin_public, 'display_user_points');

        //Display the checkbox at checkout to enable the point deduction system
        $this->loader->add_action('woocommerce_after_checkout_billing_form', $plugin_public, 'add_points_usage_checkbox_to_checkout');

        //Update the cart with the discount when the slider is moved
        $this->loader->add_action('woocommerce_cart_calculate_fees', $plugin_public, 'woo_rewards_update_discount');
    }


    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run()
    {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name()
    {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    The_Motion_Academy_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader()
    {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version()
    {
        return $this->version;
    }
}

if (class_exists('Woo_Rewards_Program')) { //Check if the class exists before intializing
    $wooRewardsProgram = new Woo_Rewards_Program(); //Intialise the class
    $wooRewardsProgram->run(); // run the loader
}
