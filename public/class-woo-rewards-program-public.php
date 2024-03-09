<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://method.au
 * @since      1.0.0
 *
 * @package    Woo_Rewards_Program
 * @subpackage Woo_Rewards_Program/public
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Woo_Rewards_Program
 * @subpackage Woo_Rewards_Program/public
 * @author     Method <cullen@method.au>
 */

class Woo_Rewards_Program_Public
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

    public function enqueue_styles()
    {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/styles.css', array(), $this->version, 'all');
    }

    public function enqueue_scripts()
    {
        wp_enqueue_script('woo-rewards-program-public', plugin_dir_url(__FILE__) . 'js/woo-rewards-program-public.js', array('jquery'), null, true);

        $translation_array = array(
            'ajax_url' => admin_url('admin-ajax.php')
        );
        wp_localize_script('woo-rewards-program-public', 'wooRewardsProgram', $translation_array);
    }

    /**
     * Displays the users points as a success notice at the checkout and cart
     * 
     * @return void
     */
    public function display_user_points()
    {

        $user_id = get_current_user_id();

        if (!$user_id) {
            return false;
        }

        //Get the users points
        $user_points = Woo_Rewards_Program_Utils::get_users_current_points($user_id);

        //Display to the user the amount of points
        $notice_message = "You currently have <b> $user_points points</b>";
        wc_add_notice($notice_message, 'success');
    }

    /**
     * Adds the slider and hidden field inputs to the checkout page to allow the user to apply a discount dynamically to their order
     * The slider isn't supported by the woocommerce form fields so a hidden 'numbers' form field is added
     * The slider then updates it via JQuery and triggers an update cart
     * 
     */
    public function add_points_usage_checkbox_to_checkout($checkout)
    {
        $user_id = get_current_user_id();

        if (!$user_id) {
            return false;
        }

        //Get the point details
        $user_points = Woo_Rewards_Program_Utils::get_users_current_points($user_id);
        $point_threshold = Woo_Rewards_Program_Utils::get_min_points_threshold();

        $points_redeemable = floor($user_points / $point_threshold) * $point_threshold;

        //If the user has more or equal points to the threshold then let them use the points
        if ($user_points >= $point_threshold) {
            include plugin_dir_path(__FILE__) . 'partials/woo-rewards-program-public-point-input.php';

            woocommerce_form_field('points_used_field', array(
                'type'        => 'number',
            ), $checkout->get_value('points_used_field'));
        } else {

            //To display when the user doesnt have enough points to give an understanding of how much more they need
            $points_needed = (int) $point_threshold - $user_points;
            $percentage =  ($points_needed / $point_threshold) * 100 . '%';

            include plugin_dir_path(__FILE__) . 'partials/woo-rewards-program-public-sorry.php';
        }
    }

    /**
     * Function to calculate the discount applied for the amount of points used
     * 
     * 
     */
    public function woo_rewards_update_discount($cart)
    {

        if (!$_POST || (is_admin() && !is_ajax())) {
            return;
        }

        if (isset($_POST['post_data'])) {
            parse_str($_POST['post_data'], $post_data);
        } else {
            $post_data = $_POST;
        }

        $points_used = isset($post_data['points_used_field']) ? intval($post_data['points_used_field']) : 0;

        $discount_per_threshold = Woo_Rewards_Program_Utils::get_discount_per_threshold();
        $point_threshold = Woo_Rewards_Program_Utils::get_min_points_threshold();

        //This calculates the discount based on the parameters set by the user
        $discount = (int) ($points_used / $point_threshold) * $discount_per_threshold;

        //This ensures the points cannot make the cart go into negative
        $cart_total = $cart->cart_contents_total + $cart->tax_total;
        $max_discount = min($discount, $cart_total);

        //Display only when there is a discount
        if ($max_discount > 0) {

            //Add the discount to the cart total
            WC()->cart->add_fee('Discount from points', -$max_discount);

            //Set the sesssion variable to remove the points spent from the user
            WC()->session->set('loyalty_points_used', $points_used);
        }
    }

    /**
     * Show a notice when the use has points in their account on the my-account page
     * 
     * 
     */

    public function woo_rewards_my_account_display()
    {

        if (!is_account_page()) {
            return;
        }

        $user_id = get_current_user_id();

        if (!$user_id) {
            return;
        }

        $user_points = Woo_Rewards_Program_Utils::get_users_current_points($user_id);
        $discount_per_threshold = Woo_Rewards_Program_Utils::get_discount_per_threshold();
        $point_threshold = Woo_Rewards_Program_Utils::get_min_points_threshold();

        $message = 'Your account has ' . $user_points . ' points. Every ' . $discount_per_threshold . ' points, you can claim $' . $point_threshold . ' off at checkout';
        wc_add_notice($message, 'notice');
    }


    /***
     * Show a notice on much points will be earnt if this product is bought
     * 
     */

    public function show_points_for_product()
    {

        global $product;

        if (!$product) {
            return;
        }

        $price = $product->get_price();

        $points = Woo_Rewards_Program_Utils::calculate_points_earnt($price);

        include plugin_dir_path(__FILE__) . "partials/woo-rewards-program-public-points-for-product.php";
    }
}
