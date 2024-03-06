<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://method.au
 * @since      1.0.0
 *
 * @package    Woo_Rewards_Program
 * @subpackage Woo_Rewards_Program/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Woo_Rewards_Program
 * @subpackage Woo_Rewards_Program/admin
 * @author     Method <cullen@method.au>
 */

class Woo_Rewards_Program_Admin
{

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

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Adds the submenu to the woocommerce parent menu
     * 
     */
    public function add_woo_rewards_submenu()
    {
        add_submenu_page('woocommerce', 'Woo Rewards Program', 'Woo Rewards', 'manage_woocommerce', 'woo-rewards-program', array($this, 'woo_rewards_program_admin_page'));
    }

    /**
     * Function that displays the markup and retrieves the settings for the woo rewards page
     * 
     */
    public function woo_rewards_program_admin_page()
    {
        include plugin_dir_path(__FILE__) . 'partials/woo-rewards-program-admin-settings.php';
    }

    /**
     * Handles the settings saved form
     * 
     */
    public function woo_rewards_program_handle_settings_save()
    {
        if (isset($_POST['save_woo_rewards']) && isset($_POST['save_woo_rewards_nonce_field']) && wp_verify_nonce($_POST['save_woo_rewards_nonce_field'], 'save_woo_rewards_nonce')) {

            // Update options in the database
            update_option('enable_woo_rewards', isset($_POST['enable_woo_rewards']) ? 1 : 0);
            update_option('points_per_dollar', isset($_POST['points_per_dollar']) ? sanitize_text_field($_POST['points_per_dollar']) : '');
            update_option('min_point_threshold', isset($_POST['min_point_threshold']) ? sanitize_text_field($_POST['min_point_threshold']) : '');
            update_option('discount_per_threshold', isset($_POST['discount_per_threshold']) ? sanitize_text_field($_POST['discount_per_threshold']) : '');
            update_option('maximum_point_limit', isset($_POST['maximum_point_limit']) ? sanitize_text_field($_POST['maximum_point_limit']) : '');

            echo '<div class="updated"><p>Settings saved successfully!</p></div>';
        }
    }

    /**
     * stores the points after a woocommerce order completion
     * 
     * @param int $order_id
     * 
     * @return boolean true if the points were successfully added
     */
    public function woo_rewards_store_points($order_id)
    {

        //Gets the order and checks if the order exists
        $order = wc_get_order($order_id);

        if (!$order) {
            return false;
        }

        //Gets the user ID and checks if the user id is != null
        $user_id = $order->get_user_id();

        if (!$user_id) {
            return false;
        }

        //Gets the order total in dollars
        $order_total = $order->get_total();

        //Converts the dollars spent into points
        $points_earnt = Woo_Rewards_Program_Utils::calculate_points_earnt($order_total);


        //Get the points that were used during the checkout session
        $points_used = WC()->session->get('loyalty_points_used');

        if ($points_used) {
            //Take the points away that were spent from the point total
            $points_earnt = $points_earnt - $points_used;

            //Remove the session variable after calculating the new total points
            WC()->session->__unset('loyalty_points_used');
        }

        //Updates the points for the user
        $points_updated = Woo_Rewards_Program_Utils::update_users_points($user_id, $points_earnt);

        return $points_updated;
    }

    /**
     * Render additional fields in the edit user interface for the Woo Rewards Program points
     * 
     * @param object $user
     * 
     * @return void
     * 
     */
    public function render_additional_user_setting_fields($user)
    {
        include plugin_dir_path(__FILE__) . 'partials/woo-rewards-program-admin-user-fields.php';
    }

    /**
     * Handling the additional fields in the edit user interface on update / save
     *
     * @param int $user_id
     * 
     * @return void
     * 
     */
    public function save_woo_rewards_edit_user($user_id)
    {

        //Early return if user doesn't have editing permissions
        if (!current_user_can('edit_user', $user_id)) {
            return;
        }

        //Updates the value
        update_user_meta($user_id, 'woo_user_points', sanitize_text_field($_POST['woo_user_points']));
    }

    /**
     * Add the Woo Rewards Points column to the manage users table
     * 
     */
    public function add_woo_rewards_users_column($columns)
    {
        $columns['woo_reward_points'] = 'Woo Reward Points';
        return $columns;
    }

    /**
     * Add the woo rewards points data to the column
     * 
     */
    public function add_woo_rewards_users_column_data($value, $column_name, $user_id)
    {
        if ($column_name === 'woo_reward_points') {
            $points = (int) Woo_Rewards_Program_Utils::get_users_current_points($user_id);
            return $points;
        }
        return $value;
    }

    /**
     * Make a sortable user column for the woo rewards points
     * 
     */
    public function woo_rewards_user_column_sortable($columns)
    {
        $columns['woo_reward_points'] = 'woo_reward_points';
        return $columns;
    }

    /**
     * Make a sortable query for the woo rewards points users column
     * 
     */
    public function woo_rewards_table_orderby($query)
    {
        if (!is_admin() || !$query->is_main_query()) {
            return;
        }

        if ($query->get('orderby') === 'woo_reward_points') {
            $query->set('meta_key', 'woo_reward_points');
            $query->set('orderby', 'meta_value_num');
        }
    }
}
