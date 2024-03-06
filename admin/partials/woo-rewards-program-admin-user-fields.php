<?php

/**
 * Provide an admin area view for the plugin
 *
 * This file is used to markup the extension of the woocommerce user interface
 *
 * @link       https://method.au
 * @since      1.0.0
 *
 * @package    The_Motion_Academy
 * @subpackage The_Motion_Academy/admin/partials
 */
?>
<h2>Woo rewards program</h2>
<table class="form-table">
    <tr>
        <th><label for="woo_user_points">Woo Reward Points</label></th>
        <td>
            <input type="text" name="woo_user_points" id="woo_user_points" value="<?php echo esc_attr(get_user_meta($user->ID, 'woo_user_points', true)); ?>" class="regular-text" />
            <p class="description">The number of points this user has that can be redeemed at checkout</p>
        </td>
    </tr>
</table>