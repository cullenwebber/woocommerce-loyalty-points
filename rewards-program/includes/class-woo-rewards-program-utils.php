<?php

/**
 * The utils functionality of the plugin.
 *
 * @link       https://method.au
 * @since      1.0.0
 *
 * @package    Woo_Rewards_Program
 * @subpackage Woo_Rewards_Program/includes
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Woo_Rewards_Program
 * @subpackage Woo_Rewards_Program/includes
 * @author     Method <cullen@method.au>
 */

class Woo_Rewards_Program_Utils
{

    /**
     * Checks whether the point system is enabled
     * 
     * @return boolean true if the point system is enabled
     */
    public static function woo_rewards_is_enabled()
    {
        $is_enable = get_option('enable_woo_rewards');

        if ($is_enable == 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns how many points are earned per dollar spent
     * 
     * @return int Points accrued per dollar spent
     */
    public static function get_points_per_dollar()
    {
        return (int) get_option('points_per_dollar', 0);
    }

    /**
     * Returns the minimum number of points needed to claim the points
     * 
     * @return int Minimum number of points needed to claim
     */
    public static function get_min_points_threshold()
    {
        return (int) get_option('min_point_threshold', 0);
    }

    /**
     * Returns the discount given per threshold value
     * 
     * @return int Returns the discount given per threshold value
     */
    public static function get_discount_per_threshold()
    {
        return (int) get_option('discount_per_threshold', 0);
    }

    /**
     * Get the maximum point limit for each user to accrue
     * 
     * @return int Returns the maximum point limit a user can accrue
     */
    public static function get_maximum_point_limit()
    {
        return (int) get_option('maximum_point_limit', 0);
    }


    /**
     * Get users current amount of points accumulated
     * 
     * @param int $user_id
     * 
     * @return int Returns the current amount of points accumulated points
     */
    public static function get_users_current_points($user_id)
    {
        return (int) get_user_meta($user_id, 'woo_user_points', true);
    }

    /**
     * Update the users points based on their previous points
     * Wont update the points past the limit set by the admin
     * 
     * @param int $user_id
     * @param int $points Number of points to add to the current point accumulated
     * 
     * @return boolean Returns whether the update was successful
     * 
     */
    public static function update_users_points($user_id, $points)
    {

        $points_limit = Woo_Rewards_Program_Utils::get_maximum_point_limit();

        $current_points = Woo_Rewards_Program_Utils::get_users_current_points($user_id);

        //Finds whether the points about to be added or the point limit is the lowest, the assigns it to the points to update
        $points_to_update = min($points_limit, $current_points + $points);

        return update_user_meta($user_id, 'woo_user_points', $points_to_update);
    }

    /**
     * Calculate the total points earnt per dollar spent
     * 
     * @param int $spent Amount of dollars spent
     * 
     * @return int Returns the total points earnt
     */
    public static function calculate_points_earnt($spent)
    {

        $points_per_dollar = Woo_Rewards_Program_Utils::get_points_per_dollar();

        $total_points = $spent * $points_per_dollar;

        return $total_points;
    }
}
