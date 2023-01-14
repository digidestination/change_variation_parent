<?php

/**
 * Plugin Name: Change Variation Parent
 * Description: A plugin to change the parent product for a variation product in WooCommerce.
 * Version: 1.0.0
 * Author: Ivan Sorkin
 * Author URI: https://www.digidestination.com
 */

function change_variation_parent_func($parent_id, $variation_id)
{
    // Get the variable product object
    $variation = wc_get_product($variation_id);

    // Check if the product is a variation
    if ($variation && $variation->is_type('variation')) {
        // Set the parent ID
        $variation->set_parent_id($parent_id);

        // Save the data
        $variation->save();
    }
}

function change_variation_parent_menu()
{
    add_submenu_page(
        'edit.php?post_type=product',
        'Change Parent',
        'Change Parent',
        'manage_options',
        'change-variation-parent',
        'change_variation_parent_options'
    );
}
add_action('admin_menu', 'change_variation_parent_menu');

function change_variation_parent_options()
{
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    // Variables for the field and option names
    $hidden_field_name = 'mt_submit_hidden';
    $variation_sku_field_name = 'mt_variation_sku';
    $parent_sku_field_name = 'mt_parent_sku';

    // Read in existing option values from database
    $variation_sku_val = get_option($variation_sku_field_name);
    $parent_sku_val = get_option($parent_sku_field_name);

    // See if the user has posted us some information
    // If they did, this hidden field will be set to 'Y'
    if (isset($_POST[$hidden_field_name]) && $_POST[$hidden_field_name] == 'Y') {
        // Read their posted values
        $variation_sku_val = $_POST[$variation_sku_field_name];
        $parent_sku_val = $_POST[$parent_sku_field_name];

        // Save the posted values in the database
        update_option($variation_sku_field_name, $variation_sku_val);
        update_option($parent_sku_field_name, $parent_sku_val);

        // Look up the product IDs using the SKUs
        $variation_id = wc_get_product_id_by_sku($variation_sku_val);
        $parent_id = wc_get_product_id_by_sku($parent_sku_val);

        // Change the parent product for the variation product
        if ($variation_id && $parent_id) {
            // Call the function to change the parent product
            change_variation_parent_func($parent_id, $variation_id);

            // Put an settings updated message on the screen
?>
            <div class="updated">
                <p><strong><?php _e('Settings saved.', 'menu-test'); ?></strong></p>
            </div>
        <?php
        } else {
            // Put an error message on the screen
        ?>
            <div class="error">
                <p><strong><?php _e('Error: Invalid SKUs.', 'menu-test'); ?></strong></p>
            </div>
    <?php
        }
    }

    // Now display the settings editing screen
    echo '<div class="wrap">';

    // header
    echo "<h2>" . __('Change Parent Settings', 'menu-test') . "</h2>";

    // settings form
    ?>

    <form name="form1" method="post" action="">
        <input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">

        <p><?php _e("Variation SKU:", 'menu-test'); ?>
            <input type="text" name="<?php echo $variation_sku_field_name; ?>" value="<?php echo $variation_sku_val; ?>" size="20">
        </p>
        <p><?php _e("Parent SKU:", 'menu-test'); ?>
            <input type="text" name="<?php echo $parent_sku_field_name; ?>" value="<?php echo $parent_sku_val; ?>" size="20">
        </p>
        <p class="submit">
            <input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
        </p>
    </form>
    </div>

<?php
}
