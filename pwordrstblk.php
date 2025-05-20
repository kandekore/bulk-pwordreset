<?php
/**
 * Plugin Name: Bulk Password Reset
 * Description: Allows admins to bulk-send password reset emails to selected users by role.
 * Version: 1.0.0
 * Author: Your Name
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'admin_menu', 'bpr_add_admin_menu' );
function bpr_add_admin_menu() {
    add_users_page(
        __( 'Bulk Password Reset', 'bulk-password-reset' ),
        __( 'Bulk Password Reset', 'bulk-password-reset' ),
        'manage_options',
        'bulk-password-reset',
        'bpr_render_admin_page'
    );
}

function bpr_render_admin_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'You do not have sufficient permissions to access this page.', 'bulk-password-reset' ) );
    }

    if ( isset( $_GET['bpr_sent'] ) && '1' === $_GET['bpr_sent'] ) {
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e( 'Password reset emails have been sent!', 'bulk-password-reset' ); ?></p>
        </div>
        <?php
    }
    ?>
    <div class="wrap">
        <h1><?php _e( 'Bulk Password Reset', 'bulk-password-reset' ); ?></h1>
        <p><?php _e( 'Select a user role to filter, then choose one or more users to send them a password reset link.', 'bulk-password-reset' ); ?></p>

        <form method="POST" action="">
            <?php wp_nonce_field( 'bpr_filter_users', 'bpr_filter_users_nonce' ); ?>
            <select name="bpr_selected_role" style="min-width: 200px;">
                <?php
                global $wp_roles;
                if ( ! isset( $wp_roles ) ) {
                    $wp_roles = new WP_Roles();
                }
                $all_roles = $wp_roles->roles;
                foreach ( $all_roles as $role_key => $role_data ) {
                    echo '<option value="' . esc_attr( $role_key ) . '">' . esc_html( $role_data['name'] ) . '</option>';
                }
                ?>
            </select>
            <input type="submit" name="bpr_filter_submit" class="button button-primary" value="<?php _e( 'Filter Users', 'bulk-password-reset' ); ?>">
        </form>
        <?php
        if ( isset( $_POST['bpr_selected_role'] ) && check_admin_referer( 'bpr_filter_users', 'bpr_filter_users_nonce' ) ) {
            $role = sanitize_text_field( $_POST['bpr_selected_role'] );
            $args = array(
                'role'    => $role,
                'orderby' => 'user_nicename',
                'order'   => 'ASC',
                'number'  => 200,
            );
            $users = get_users( $args );

            if ( ! empty( $users ) ) {
                ?>
                <form method="POST" action="">
                    <?php wp_nonce_field( 'bpr_send_email', 'bpr_send_email_nonce' ); ?>
                    <table class="widefat fixed striped" cellspacing="0" style="margin-top:20px;">
                        <thead>
                            <tr>
                                <th class="manage-column column-cb check-column" style="width: 50px;">
                                    <input type="checkbox" id="bpr_select_all" />
                                </th>
                                <th><?php _e( 'Username', 'bulk-password-reset' ); ?></th>
                                <th><?php _e( 'Email', 'bulk-password-reset' ); ?></th>
                                <th><?php _e( 'Last Password Reset Sent', 'bulk-password-reset' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        foreach ( $users as $user ) {
                            $last_sent    = get_user_meta( $user->ID, 'bpr_last_reset_email_sent', true );
                            $display_date = $last_sent
                                ? date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $last_sent )
                                : __( 'Not sent yet', 'bulk-password-reset' );
                            ?>
                            <tr>
                                <th class="check-column">
                                    <input type="checkbox" name="bpr_user_ids[]" value="<?php echo esc_attr( $user->ID ); ?>">
                                </th>
                                <td><?php echo esc_html( $user->user_login ); ?></td>
                                <td><?php echo esc_html( $user->user_email ); ?></td>
                                <td><?php echo esc_html( $display_date ); ?></td>
                            </tr>
                            <?php
                        }
                        ?>
                        </tbody>
                    </table>
                    <h3 style="margin-top: 30px;"><?php _e( 'Customize Email', 'bulk-password-reset' ); ?></h3>
                    <p>
                        <label for="bpr_email_subject"><?php _e( 'Subject', 'bulk-password-reset' ); ?></label><br>
                        <input type="text" name="bpr_email_subject" id="bpr_email_subject" style="width: 100%;" value="Reset Your Password" />
                    </p>
                    <p>
                        <label for="bpr_email_body"><?php _e( 'Body', 'bulk-password-reset' ); ?></label><br>
                        <textarea name="bpr_email_body" id="bpr_email_body" rows="6" style="width: 100%;">
Hello {username},

Please click the link below to reset your password:
{reset_link}

Thank you!
                        </textarea>
                    </p>
                    <input type="submit" name="bpr_send_submit" class="button button-primary" value="<?php _e( 'Send Reset Links', 'bulk-password-reset' ); ?>">
                </form>
                <script>
                document.getElementById('bpr_select_all').addEventListener('click', function() {
                    var checkboxes = document.querySelectorAll('input[name="bpr_user_ids[]"]');
                    for ( var checkbox of checkboxes ) {
                        checkbox.checked = this.checked;
                    }
                });
                </script>
                <?php
            } else {
                ?>
                <p style="margin-top:20px;"><?php _e( 'No users found for that role.', 'bulk-password-reset' ); ?></p>
                <?php
            }
        }
        ?>
    </div>
    <?php
}

add_action( 'admin_init', 'bpr_handle_send_reset_links' );
function bpr_handle_send_reset_links() {
    if ( isset( $_POST['bpr_send_submit'] ) && check_admin_referer( 'bpr_send_email', 'bpr_send_email_nonce' ) ) {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'You do not have permission to do this.', 'bulk-password-reset' ) );
        }

        $user_ids       = isset( $_POST['bpr_user_ids'] ) ? (array) $_POST['bpr_user_ids'] : array();
        $email_subject  = sanitize_text_field( $_POST['bpr_email_subject'] );
        $email_body_raw = isset( $_POST['bpr_email_body'] ) ? wp_kses_post( stripslashes( $_POST['bpr_email_body'] ) ) : '';

        foreach ( $user_ids as $user_id ) {
            $user = get_user_by( 'ID', $user_id );
            if ( $user ) {
                $reset_key = get_password_reset_key( $user );
                if ( is_wp_error( $reset_key ) ) {
                    continue;
                }
                $reset_url = network_site_url( "wp-login.php?action=rp&key=$reset_key&login=" . rawurlencode( $user->user_login ), 'login' );
                $placeholders = array(
                    '{username}'   => $user->user_login,
                    '{reset_link}' => $reset_url,
                );
                $final_email_body = str_replace(
                    array_keys( $placeholders ),
                    array_values( $placeholders ),
                    $email_body_raw
                );
                wp_mail( $user->user_email, $email_subject, $final_email_body );
                update_user_meta( $user->ID, 'bpr_last_reset_email_sent', current_time( 'timestamp' ) );
            }
        }
        wp_redirect( add_query_arg( 'bpr_sent', '1', wp_get_referer() ) );
        exit;
    }
}
