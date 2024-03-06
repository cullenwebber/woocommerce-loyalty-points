<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the extension of the woocommerce admin area to display the settings to the user
 *
 * @link       https://method.au
 * @since      1.0.0
 *
 * @package    The_Motion_Academy
 * @subpackage The_Motion_Academy/admin/partials
 */
?>

<div class="wrap">
    <h1>Woo Rewards Program</h1>
    <form method="post">
        <!-- Existing HTML for input fields -->

        <table class="form-table">
            <!-- Enable Rewards -->
            <tr valign="top">
                <th scope="row">Enable Rewards</th>
                <td>
                    <label>
                        <input type="checkbox" name="enable_woo_rewards" <?php echo checked(1, get_option('enable_woo_rewards'), false); ?> />
                        Enable rewards
                    </label>
                    <p class="description">Check this box to enable the point rewards system on your site.</p>
                </td>
            </tr>

            <!-- Points per Dollar -->
            <tr valign="top">
                <th scope="row">Points per Dollar</th>
                <td>
                    <input type="number" placeholder="1" name="points_per_dollar" value="<?php echo esc_attr(get_option('points_per_dollar')); ?>" />
                    <p class="description">Enter the number of reward points a user earns for every dollar spent. <br /> eg. 1 Point per $1</p>
                </td>
            </tr>

            <!-- Redeemable Limit -->
            <tr valign="top">
                <th scope="row">Minimum Point Threshold</th>
                <td>
                    <input type="number" placeholder="100" name="min_point_threshold" value="<?php echo esc_attr(get_option('min_point_threshold')); ?>" />
                    <p class="description">Set the minimum amount of points a user must acrew before they are allowed to reedem their points. <br /> eg. The user cannot redeem any discounts until they hit 100 points</p>
                </td>
            </tr>

            <!-- Discount per Redeemable Limit -->
            <tr valign="top">
                <th scope="row">Discount per Minimum Threshold </th>
                <td>
                    <input type="number" placeholder="10" name="discount_per_threshold" value="<?php echo esc_attr(get_option('discount_per_threshold')); ?>" />
                    <p class="description">Specify the discount the user receives per minimum threshold. <br /> eg. $10 off per 100 points.</p>
                </td>
            </tr>

            <!-- Maximum Point Limit -->
            <tr valign="top">
                <th scope="row">Maximum Point Limit</th>
                <td>
                    <input type="number" placeholder="1000" name="maximum_point_limit" value="<?php echo esc_attr(get_option('maximum_point_limit')); ?>" />
                    <p class="description">Define the overall maximum limit of accumulated reward points for a user. <br /> eg. A user can only have 1000 points at one time</p>
                </td>
            </tr>
        </table>

        <!-- Add a nonce field -->
        <?php wp_nonce_field('save_woo_rewards_nonce', 'save_woo_rewards_nonce_field'); ?>
        <input type="submit" name="save_woo_rewards" class="button button-primary" value="Save Settings">

    </form>

</div>