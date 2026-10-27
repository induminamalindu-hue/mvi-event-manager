<?php
/**
 * Plugin Name: MVI Event & Workshop Manager
 * Plugin URI:  https://github.com/yourusername/mvi-event-manager
 * Description: විශ්වවිද්‍යාලයේ සිසුන්ගේ events සහ workshops කළමනාකරණය කිරීමට හැදූ ප්ලගිනයකි.
 * Version:     1.0.0
 * Author:      Malindu Indumina
 * Text Domain: mvi-event-manager
 */

// කවුරුහරි කෙලින්ම මේ file එකට URL එක හරහා එන එක නැවැත්වීමට (Security)
if ( ! defined( 'ABSPATH' ) ) {
    exit; // කෙලින්ම access කළොත් ඉවත් වන්න
}

// අපි අපේ ඉතුරු කේත (code) ලියන්නේ මෙතනින් පහළටයි...



// Events සඳහා Custom Post Type එකක් සෑදීම
function mvi_register_events_post_type() {
    $labels = array(
        'name'               => 'Events',
        'singular_name'      => 'Event',
        'menu_name'          => 'Events',
        'add_new'            => 'Add New Event',
        'add_new_item'       => 'Add New Event',
        'edit_item'          => 'Edit Event',
        'view_item'          => 'View Event',
        'all_items'          => 'All Events',
        'search_items'       => 'Search Events',
        'not_found'          => 'No events found.',
    );

    $args = array(
        'labels'              => $labels,
        'public'              => true,
        'has_archive'         => true,
        'menu_position'       => 5, // Posts මෙනුවට යටින් පෙන්වන්න
        'menu_icon'           => 'dashicons-calendar-alt', // Calendar icon එක
        'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt' ), // Title, Description, සහ Image දාන්න පුළුවන් වෙන්න
        'rewrite'             => array( 'slug' => 'events' ),
    );

    register_post_type( 'mvi_events', $args );
}
// WordPress load වෙද්දි මේ function එක run කරන්න කියන්න
add_action( 'init', 'mvi_register_events_post_type' );

// ==========================================
// 1. Custom Post Type හැදීම (Events)
// ==========================================
function mvi_create_event_post_type() {
    $args = array(
        'labels' => array(
            'name'          => __( 'Events', 'mvi-event-manager' ),
            'singular_name' => __( 'Event', 'mvi-event-manager' ),
            'add_new_item'  => __( 'Add New Event', 'mvi-event-manager' ),
            'edit_item'     => __( 'Edit Event', 'mvi-event-manager' )
        ),
        'public'        => true,
        'has_archive'   => true,
        'menu_icon'     => 'dashicons-calendar-alt',
        // Title, Editor (Description), සහ Thumbnail (Image) මෙතනින් සක්‍රීය කර ඇත 
        'supports'      => array( 'title', 'editor', 'thumbnail' ), 
        'show_in_rest'  => true
    );
    register_post_type( 'mvi_events', $args );
}
add_action( 'init', 'mvi_create_event_post_type' );

// ==========================================
// 2. Custom Meta Boxes හැදීම (Date, Time, Location)
// ==========================================
function mvi_add_event_meta_boxes() {
    add_meta_box(
        'mvi_event_details',
        __( 'Event Details', 'mvi-event-manager' ),
        'mvi_event_details_callback',
        'mvi_events',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'mvi_add_event_meta_boxes' );

function mvi_event_details_callback( $post ) {
    wp_nonce_field( 'mvi_save_event_details', 'mvi_event_details_nonce' );
    
    $date = get_post_meta( $post->ID, '_mvi_event_date', true );
    $time = get_post_meta( $post->ID, '_mvi_event_time', true );
    $location = get_post_meta( $post->ID, '_mvi_event_location', true );
    ?>
    <div style="padding: 10px 0;">
        <p>
            <label for="mvi_event_date"><b><?php esc_html_e( 'Date (දිනය):', 'mvi-event-manager' ); ?></b></label><br>
            <input type="date" id="mvi_event_date" name="mvi_event_date" value="<?php echo esc_attr( $date ); ?>" style="width: 100%; margin-top: 5px;">
        </p>
        <p>
            <label for="mvi_event_time"><b><?php esc_html_e( 'Time (වෙලාව):', 'mvi-event-manager' ); ?></b></label><br>
            <input type="time" id="mvi_event_time" name="mvi_event_time" value="<?php echo esc_attr( $time ); ?>" style="width: 100%; margin-top: 5px;">
        </p>
        <p>
            <label for="mvi_event_location"><b><?php esc_html_e( 'Location (ස්ථානය):', 'mvi-event-manager' ); ?></b></label><br>
            <input type="text" id="mvi_event_location" name="mvi_event_location" value="<?php echo esc_attr( $location ); ?>" placeholder="e.g. ICBT Main Auditorium" style="width: 100%; margin-top: 5px;">
        </p>
    </div>
    <?php
}

// ==========================================
// 3. Meta Box දත්ත Database එකේ Save කිරීම
// ==========================================
function mvi_save_event_details( $post_id ) {
    if ( ! isset( $_POST['mvi_event_details_nonce'] ) || ! wp_verify_nonce( $_POST['mvi_event_details_nonce'], 'mvi_save_event_details' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    if ( isset( $_POST['mvi_event_date'] ) ) {
        update_post_meta( $post_id, '_mvi_event_date', sanitize_text_field( $_POST['mvi_event_date'] ) );
    }
    if ( isset( $_POST['mvi_event_time'] ) ) {
        update_post_meta( $post_id, '_mvi_event_time', sanitize_text_field( $_POST['mvi_event_time'] ) );
    }
    if ( isset( $_POST['mvi_event_location'] ) ) {
        update_post_meta( $post_id, '_mvi_event_location', sanitize_text_field( $_POST['mvi_event_location'] ) );
    }
}
add_action( 'save_post', 'mvi_save_event_details' );

// 4. වෙබ් අඩවියේ (Front-end) Event විස්තර පෙන්වීම
function mvi_display_event_details_in_front( $content ) {
    // මේක 'mvi_events' කියන අපේ custom post එකක්ද කියලා බලනවා
    if ( is_singular( 'mvi_events' ) && in_the_loop() && is_main_query() ) {
        $post_id = get_the_ID();
        
        // Database එකෙන් අපේ දත්ත ටික ගන්නවා
        $event_date     = get_post_meta( $post_id, '_mvi_event_date', true );
        $event_time     = get_post_meta( $post_id, '_mvi_event_time', true );
        $event_location = get_post_meta( $post_id, '_mvi_event_location', true );
        
        // ලස්සනට පේන්න HTML Box එකක් හැදීම
        $event_html = '<div style="background-color: #f0f8ff; padding: 15px; border-left: 5px solid #0073aa; margin-bottom: 20px; border-radius: 5px;">';
        $event_html .= '<h3 style="margin-top: 0;">Event Details</h3>';
        $event_html .= '<ul style="list-style-type: none; padding-left: 0;">';
        
        if ( ! empty( $event_date ) ) {
            $event_html .= '<li style="margin-bottom: 8px;"><strong>📅 දිනය:</strong> ' . esc_html( $event_date ) . '</li>';
        }
        if ( ! empty( $event_time ) ) {
            $event_html .= '<li style="margin-bottom: 8px;"><strong>⏰ වෙලාව:</strong> ' . esc_html( $event_time ) . '</li>';
        }
        if ( ! empty( $event_location ) ) {
            $event_html .= '<li><strong>📍 ස්ථානය:</strong> ' . esc_html( $event_location ) . '</li>';
        }
        
        $event_html .= '</ul>';
        $event_html .= '</div>';
        
        // මුල් විස්තරයට (Description) කලින් අපේ HTML Box එක එකතු කරනවා
        $content = $event_html . $content;
    }
    
    return $content;
}
add_filter( 'the_content', 'mvi_display_event_details_in_front' );

// ==========================================
// 5. Frontend එකේ Events පෙන්වීම (Shortcode)
// ==========================================
function mvi_events_list_shortcode() {
    $args = array(
        'post_type'      => 'mvi_events',
        'posts_per_page' => -1,
        'post_status'    => 'publish'
    );
    $events_query = new WP_Query( $args );

    $output = '<div class="mvi-events-grid">';

    if ( $events_query->have_posts() ) {
        while ( $events_query->have_posts() ) {
            $events_query->the_post();
            $post_id = get_the_ID();
            $event_date = get_post_meta( $post_id, '_mvi_event_date', true );
            $event_time = get_post_meta( $post_id, '_mvi_event_time', true );
            $event_location = get_post_meta( $post_id, '_mvi_event_location', true );

            // Card එකේ ප්‍රධාන රාමුව
            $output .= '<div class="mvi-event-card" style="padding: 0; display: flex; flex-direction: column; overflow: hidden;">';
            
            // පින්තූරය (Featured Image) පෙන්වන කොටස
            if ( has_post_thumbnail() ) {
                $img_url = get_the_post_thumbnail_url( $post_id, 'medium_large' );
                $output .= '<div style="width: 100%; height: 200px; background-image: url(' . esc_url($img_url) . '); background-size: cover; background-position: center; border-bottom: 4px solid #2563eb;"></div>';
            } else {
                // පින්තූරයක් නැත්නම් පෙන්වන සාමාන්‍ය පසුබිම
                $output .= '<div style="width: 100%; height: 200px; background: linear-gradient(135deg, #e2e8f0, #cbd5e1); display: flex; align-items: center; justify-content: center; color: #64748b; font-weight: 500; border-bottom: 4px solid #2563eb;">No Image Available</div>';
            }
            
            // විස්තර (Details) කොටස
            $output .= '<div style="padding: 25px;">';
            $output .= '<h3 class="mvi-event-title"><a href="' . get_permalink() . '">' . get_the_title() . '</a></h3>';
            $output .= '<p style="color: #475569; margin: 10px 0;">📅 <strong>' . esc_html__( 'Date:', 'mvi-event-manager' ) . '</strong> ' . esc_html( $event_date ) . '</p>';
            $output .= '<p style="color: #475569; margin: 10px 0;">⏰ <strong>' . esc_html__( 'Time:', 'mvi-event-manager' ) . '</strong> ' . esc_html( $event_time ) . '</p>';
            $output .= '<p style="color: #475569; margin: 10px 0;">📍 <strong>' . esc_html__( 'Location:', 'mvi-event-manager' ) . '</strong> ' . esc_html( $event_location ) . '</p>';
            $output .= '<div style="margin-top: 20px;"><a href="' . get_permalink() . '" class="mvi-btn-modern">' . esc_html__( 'View Details', 'mvi-event-manager' ) . '</a></div>';
            $output .= '</div>'; 
            
            $output .= '</div>'; 
        }
        wp_reset_postdata();
    } else {
        $output .= '<p>' . esc_html__( 'No events are scheduled at the moment.', 'mvi-event-manager' ) . '</p>';
    }

    $output .= '</div>';
    return $output;
}
add_shortcode( 'mvi_events', 'mvi_events_list_shortcode' );




// ==========================================
// 6. සිසුන්ට Register වෙන්න Form එකක් හැදීම (ලොග් වූ අයට පමණයි)
// ==========================================
function mvi_event_registration_form( $content ) {
    if ( is_singular( 'mvi_events' ) && in_the_loop() && is_main_query() ) {
        $post_id = get_the_ID();
        
        $form_html = '<div class="mvi-reg-form-container">';
        $form_html .= '<h3>' . esc_html__( 'Register for this Event', 'mvi-event-manager' ) . '</h3>';
        
        // පරිශීලකයා ලොග් වී ඇත්දැයි පරීක්ෂා කිරීම
        if ( is_user_logged_in() ) {
            
            // ලොග් වී ඇති කෙනාගේ දත්ත ලබා ගැනීම
            $current_user = wp_get_current_user();
            $user_name = $current_user->display_name;
            $user_email = $current_user->user_email;

            $form_html .= '<div id="mvi-form-message" style="display:none; padding: 15px; border-radius: 6px; margin-bottom: 15px; font-weight: 500;"></div>';
            
            $form_html .= '<form id="mvi-registration-form">';
            $form_html .= wp_nonce_field( 'mvi_ajax_register', 'mvi_security', true, false );
            $form_html .= '<input type="hidden" name="action" value="mvi_ajax_register_student">';
            $form_html .= '<input type="hidden" name="event_id" value="' . esc_attr( $post_id ) . '">';
            
            // නම සහ ඊමේල් එක Auto-fill කරලා, වෙනස් කරන්න බැරි වෙන්න (Readonly) සාදා ඇත
            $form_html .= '<div class="mvi-form-group"><label>' . esc_html__( 'Full Name', 'mvi-event-manager' ) . '</label><input type="text" name="student_name" class="mvi-form-control" value="' . esc_attr( $user_name ) . '" readonly style="background-color: #f1f5f9 !important; color: #64748b; cursor: not-allowed;"></div>';
            
            $form_html .= '<div class="mvi-form-group"><label>' . esc_html__( 'Email Address', 'mvi-event-manager' ) . '</label><input type="email" name="student_email" class="mvi-form-control" value="' . esc_attr( $user_email ) . '" readonly style="background-color: #f1f5f9 !important; color: #64748b; cursor: not-allowed;"></div>';
            
            // ශිෂ්‍යයාට type කරන්න තියෙන්නේ Student ID එක විතරයි
            $form_html .= '<div class="mvi-form-group"><label>' . esc_html__( 'Student ID', 'mvi-event-manager' ) . '</label><input type="text" name="student_id" class="mvi-form-control" placeholder="' . esc_attr__( 'e.g., STU-2026-001', 'mvi-event-manager' ) . '" required></div>';
            
            $form_html .= '<button type="submit" id="mvi-submit-btn" class="mvi-submit-btn">' . esc_html__( 'Confirm Registration', 'mvi-event-manager' ) . '</button>';
            $form_html .= '</form>';
            
            // AJAX JavaScript
            $ajax_url = admin_url( 'admin-ajax.php' );
            $loading_text = esc_js( __( 'Registering...', 'mvi-event-manager' ) );
            $default_btn_text = esc_js( __( 'Confirm Registration', 'mvi-event-manager' ) );
            $error_text = esc_js( __( 'An error occurred.', 'mvi-event-manager' ) );

            $form_html .= "
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.getElementById('mvi-registration-form');
                const msgBox = document.getElementById('mvi-form-message');
                const btn = document.getElementById('mvi-submit-btn');

                if(form) {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault(); 
                        btn.disabled = true;
                        btn.innerHTML = '{$loading_text}'; 
                        msgBox.style.display = 'none';

                        fetch('{$ajax_url}', {
                            method: 'POST',
                            body: new FormData(form)
                        })
                        .then(response => response.json())
                        .then(data => {
                            btn.disabled = false;
                            btn.innerHTML = '{$default_btn_text}';
                            msgBox.style.display = 'block';
                            
                            if(data.success) {
                                msgBox.style.backgroundColor = '#ecfdf5';
                                msgBox.style.color = '#047857';
                                msgBox.innerHTML = '✅ ' + data.data;
                                form.querySelector('input[name=\"student_id\"]').value = ''; // ID එක පමණක් මකා දමයි
                            } else {
                                msgBox.style.backgroundColor = '#fef2f2';
                                msgBox.style.color = '#b91c1c';
                                msgBox.innerHTML = '❌ ' + (data.data || '{$error_text}');
                            }
                        })
                        .catch(error => {
                            btn.disabled = false;
                            btn.innerHTML = '{$default_btn_text}';
                            msgBox.style.display = 'block';
                            msgBox.style.backgroundColor = '#fef2f2';
                            msgBox.style.color = '#b91c1c';
                            msgBox.innerHTML = '❌ {$error_text}';
                        });
                    });
                }
            });
            </script>";
            
        } else {
            // ලොග් වී නැති අයට පෙන්වන කොටස (Login / Register බොත්තම්)
            $login_url = site_url( '/login/' ); 
            $register_url = site_url( '/register/' ); 

            $form_html .= '<div style="text-align: center; padding: 20px 0;">';
            $form_html .= '<p style="color: #64748b; margin-bottom: 25px; font-size: 15px;">' . esc_html__( 'You must be logged in to register for this event.', 'mvi-event-manager' ) . '</p>';
            
            $form_html .= '<a href="' . esc_url( $login_url ) . '" class="mvi-submit-btn" style="text-decoration: none; display: inline-block; margin-right: 10px; width: auto;">' . esc_html__( 'Log In', 'mvi-event-manager' ) . '</a>';
            
            $form_html .= '<a href="' . esc_url( $register_url ) . '" class="mvi-btn-modern" style="margin: 0;">' . esc_html__( 'Create Account', 'mvi-event-manager' ) . '</a>';
            $form_html .= '</div>';
        }
        
        $form_html .= '</div>';
        $content .= $form_html;
    }
    return $content;
}
add_filter( 'the_content', 'mvi_event_registration_form' );


// ==========================================
// 7. AJAX හරහා එන දත්ත Database එකේ Save කිරීම සහ ඊමේල් යැවීම (Email Notifications)
// ==========================================
function mvi_ajax_handle_registration() {
    check_ajax_referer( 'mvi_ajax_register', 'mvi_security' );

    $event_id      = isset( $_POST['event_id'] ) ? intval( $_POST['event_id'] ) : 0;
    $student_name  = isset( $_POST['student_name'] ) ? sanitize_text_field( $_POST['student_name'] ) : '';
    $student_id    = isset( $_POST['student_id'] ) ? sanitize_text_field( $_POST['student_id'] ) : '';
    $student_email = isset( $_POST['student_email'] ) ? sanitize_email( $_POST['student_email'] ) : '';

    if ( $event_id && $student_name && $student_id && $student_email ) {
        $event_title = get_the_title( $event_id );

        $registration_data = array(
            'post_title'   => $student_name . ' - ' . $event_title,
            'post_content' => "Student Name: $student_name \nStudent ID: $student_id \nEmail: $student_email \nEvent: $event_title",
            'post_status'  => 'publish',
            'post_type'    => 'mvi_registrations'
        );

        $post_id = wp_insert_post( $registration_data );

        if ( $post_id ) {
            // --- අලුතින් එකතු කළ ඊමේල් යැවීමේ කේතය ---
            
            // Email Headers (HTML විදිහට යැවීමට)
            $headers = array( 'Content-Type: text/html; charset=UTF-8' );
            
            // Email Subject (මාතෘකාව)
            $admin_subject = get_option( 'mvi_email_subject', __( 'Registration Confirmation:', 'mvi-event-manager' ) );
            $subject = $admin_subject . ' ' . $event_title;
            
            // Email Body (පණිවිඩය - HTML භාවිතයෙන් ලස්සන කර ඇත)
            $message  = '<div style="font-family: Arial, sans-serif; padding: 20px; color: #333;">';
            $message .= '<h2 style="color: #0073aa;">' . esc_html__( 'Hello', 'mvi-event-manager' ) . ' ' . esc_html( $student_name ) . ',</h2>';
            $message .= '<p>' . esc_html__( 'You have successfully registered for the event:', 'mvi-event-manager' ) . ' <strong>' . esc_html( $event_title ) . '</strong>.</p>';
            $message .= '<div style="background: #f4f4f4; padding: 15px; border-left: 4px solid #0073aa; margin: 20px 0;">';
            $message .= '<p style="margin: 0;"><strong>' . esc_html__( 'Your Student ID:', 'mvi-event-manager' ) . '</strong> ' . esc_html( $student_id ) . '</p>';
            $message .= '</div>';
            $message .= '<p>' . esc_html__( 'Thank you for registering. We look forward to seeing you!', 'mvi-event-manager' ) . '</p>';
            $message .= '</div>';
            
            // WordPress හරහා ඊමේල් යැවීම
            wp_mail( $student_email, $subject, $message, $headers );
            // ---------------------------------------------

            // සාර්ථක වූ පණිවිඩය (අලුත් කරන ලදි)
            $admin_success_msg = get_option( 'mvi_success_message', __( 'Registration Successful! A confirmation email has been sent.', 'mvi-event-manager' ) );
            wp_send_json_success( $admin_success_msg );
        } else {
            wp_send_json_error( __( 'An error occurred during registration.', 'mvi-event-manager' ) );
        }
    } else {
        wp_send_json_error( __( 'Please fill in all the details.', 'mvi-event-manager' ) );
    }
}
add_action( 'wp_ajax_nopriv_mvi_ajax_register_student', 'mvi_ajax_handle_registration' );
add_action( 'wp_ajax_mvi_ajax_register_student', 'mvi_ajax_handle_registration' );


// ==========================================
// 8. ලියාපදිංචි වූ සිසුන් බලාගන්න Admin Dashboard එකට මෙනුවක් හැදීම
// ==========================================
function mvi_register_registrations_post_type() {
    $args = array(
        'labels'              => array(
            'name'          => 'Registrations',
            'singular_name' => 'Registration',
        ),
        'public'              => false, // වෙබ්සයිට් එකට එන අයට මේක පෙන්වන්න ඕන නැති නිසා
        'show_ui'             => true,  // Admin dashboard එකේ පෙන්වන්න
        'menu_position'       => 6,     // Events මෙනුවට යටින් පෙන්වන්න
        'menu_icon'           => 'dashicons-clipboard', // Clipboard icon එක
        'supports'            => array( 'title', 'editor' ),
    );
    register_post_type( 'mvi_registrations', $args );
}
add_action( 'init', 'mvi_register_registrations_post_type' );

// ==========================================
// 9. Export to CSV පහසුකම (Registrations)
// ==========================================

// 9.1 Registrations පිටුවට 'Export to CSV' බටන් එකක් එකතු කිරීම
function mvi_add_export_button_to_registrations( $which ) {
    global $typenow;
    
    // මේක Registrations පිටුව නම් සහ මේක උඩ කොටස (top) නම් විතරක් බටන් එක පෙන්වන්න
    if ( 'mvi_registrations' === $typenow && 'top' === $which ) {
        $export_url = admin_url( 'admin-post.php?action=mvi_export_registrations_csv' );
        echo '<div class="alignleft actions">';
        echo '<a href="' . esc_url( $export_url ) . '" class="button button-primary" style="background-color: #10b981; border-color: #10b981;">' . esc_html__( 'Export to CSV', 'mvi-event-manager' ) . '</a>';
        echo '</div>';
    }
}
add_action( 'manage_posts_extra_tablenav', 'mvi_add_export_button_to_registrations' );


// 9.2 බටන් එක click කළාම CSV එක හැදී Download වීම
function mvi_export_registrations_csv() {
    // Admin කෙනෙක්ට විතරක් මේක කරන්න පුළුවන් වෙන්න ආරක්ෂාව (Security check) තහවුරු කිරීම
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'You do not have permission to download this file.', 'mvi-event-manager' ) );
    }

    // බ්‍රවුසරයට මේක CSV ගොනුවක් විදිහට හඳුන්වා දීම (Headers)
    header( 'Content-Type: text/csv; charset=utf-8' );
    header( 'Content-Disposition: attachment; filename=Event-Registrations-' . date( 'Y-m-d' ) . '.csv' );

    $output = fopen( 'php://output', 'w' );
    
    // Pro Tip: Excel එකේ සිංහල අකුරු කොටු කොටු විදිහට පේන්නේ නැතුව හරියට පේන්න UTF-8 BOM එකතු කිරීම
    fputs( $output, "\xEF\xBB\xBF" );
    
    // CSV එකේ මාතෘකා පේළිය (Column Headers)
    fputcsv( $output, array( 
        __( 'Registration ID', 'mvi-event-manager' ), 
        __( 'Event & Student Name', 'mvi-event-manager' ), 
        __( 'Full Details (Name, ID, Email, Event)', 'mvi-event-manager' ), 
        __( 'Registered Date', 'mvi-event-manager' ) 
    ) );

    // Database එකෙන් Registrations ඔක්කොම ගන්නවා
    $args = array(
        'post_type'      => 'mvi_registrations',
        'posts_per_page' => -1, // සියලුම දත්ත
        'post_status'    => 'publish'
    );
    $registrations_query = new WP_Query( $args );

    // දත්ත තියෙනවා නම් එකින් එක (row by row) CSV එකට ලියනවා
    if ( $registrations_query->have_posts() ) {
        while ( $registrations_query->have_posts() ) {
            $registrations_query->the_post();
            
            $id = get_the_ID();
            $title = get_the_title(); // උදා: Malindu - Test Event
            $content = get_the_content(); // සම්පූර්ණ විස්තරය
            $date = get_the_date( 'Y-m-d H:i:s' );
            
            fputcsv( $output, array( $id, $title, $content, $date ) );
        }
        wp_reset_postdata();
    }
    
    // File එක වසා දැමීම
    fclose( $output );
    exit;
}
// WordPress admin-post.php හරහා මේක run කරන්න අදාළ Hook එක දීම
add_action( 'admin_post_mvi_export_registrations_csv', 'mvi_export_registrations_csv' );

// ==========================================
// 10. Admin Settings Page (Options API)
// ==========================================

// 10.1 "Events" මෙනුව යටතේ "Settings" කියලා අලුත් Sub-menu එකක් හැදීම
function mvi_event_manager_settings_menu() {
    add_submenu_page(
        'edit.php?post_type=mvi_events', // කොතනද පෙන්වන්නේ (Events යටතේ)
        __( 'Event Settings', 'mvi-event-manager' ), // පිටුවේ නම
        __( 'Settings', 'mvi-event-manager' ),       // මෙනුවේ නම
        'manage_options',                            // කාටද පේන්නේ (Admin ලට පමණයි)
        'mvi-event-settings',                        // Menu Slug එක
        'mvi_event_settings_page_html'               // HTML පෙන්වන function එක
    );
}
add_action( 'admin_menu', 'mvi_event_manager_settings_menu' );

// 10.2 Settings පිටුවේ අතුරුමුහුණත (UI / HTML Design)
function mvi_event_settings_page_html() {
    if ( ! current_user_can( 'manage_options' ) ) return;
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Event & Registration Settings', 'mvi-event-manager' ); ?></h1>
        <form action="options.php" method="post" style="background: #fff; padding: 20px; margin-top: 15px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
            <?php
            // ආරක්ෂාව සහ options register කළ බව තහවුරු කිරීම
            settings_fields( 'mvi_event_settings_group' );
            do_settings_sections( 'mvi-event-settings' );
            submit_button( __( 'Save Changes', 'mvi-event-manager' ) );
            ?>
        </form>
    </div>
    <?php
}

// 10.3 Settings Fields දත්ත ගබඩාවේ (Database) ලියාපදිංචි කිරීම
function mvi_register_event_settings() {
    // Database එකේ save වෙන නම් 2 (Success Msg එක සහ Email Subject එක)
    register_setting( 'mvi_event_settings_group', 'mvi_success_message' );
    register_setting( 'mvi_event_settings_group', 'mvi_email_subject' );

    add_settings_section( 'mvi_main_settings_section', __( 'General Messaging Options', 'mvi-event-manager' ), null, 'mvi-event-settings' );

    add_settings_field( 'mvi_success_message_field', __( 'Success Message Text', 'mvi-event-manager' ), 'mvi_success_message_cb', 'mvi-event-settings', 'mvi_main_settings_section' );
    add_settings_field( 'mvi_email_subject_field', __( 'Email Subject Prefix', 'mvi-event-manager' ), 'mvi_email_subject_cb', 'mvi-event-settings', 'mvi_main_settings_section' );
}
add_action( 'admin_init', 'mvi_register_event_settings' );

// 10.4 Input Fields වල HTML කේතයන්
function mvi_success_message_cb() {
    $default = __( 'Registration Successful! A confirmation email has been sent.', 'mvi-event-manager' );
    $val = get_option( 'mvi_success_message', $default );
    echo '<input type="text" name="mvi_success_message" value="' . esc_attr( $val ) . '" class="regular-text" style="width: 100%; max-width: 500px;">';
    echo '<p class="description">' . esc_html__( 'මෙම පණිවිඩය සිසුන් සාර්ථකව ලියාපදිංචි වූ පසු තිරයේ දිස්වේ.', 'mvi-event-manager' ) . '</p>';
}

function mvi_email_subject_cb() {
    $default = __( 'Registration Confirmation:', 'mvi-event-manager' );
    $val = get_option( 'mvi_email_subject', $default );
    echo '<input type="text" name="mvi_email_subject" value="' . esc_attr( $val ) . '" class="regular-text" style="width: 100%; max-width: 500px;">';
    echo '<p class="description">' . esc_html__( 'සිසුන්ට යන ඊමේල් එකේ මාතෘකාව. මෙයට අගින් Event එකේ නම ස්වයංක්‍රීයව එකතු වේ.', 'mvi-event-manager' ) . '</p>';
}

// ==========================================
// 11. Custom REST API Endpoint (For Front-end Apps like React)
// ==========================================

function mvi_register_events_api_endpoint() {
    // wp-json/mvi/v1/events කියන ලින්ක් එක හැදීම
    register_rest_route( 'mvi/v1', '/events', array(
        'methods'             => 'GET', // දත්ත පිටතට ගැනීම පමණක් සිදු කරන නිසා
        'callback'            => 'mvi_get_events_api_response',
        'permission_callback' => '__return_true' // ඕනෑම කෙනෙකුට Events බලාගන්න Public API එකක් ලෙස දීම
    ) );
}
add_action( 'rest_api_init', 'mvi_register_events_api_endpoint' );

// API එකට කවුරුහරි ආවම දත්ත (JSON) යවන Function එක
function mvi_get_events_api_response( $request ) {
    $args = array(
        'post_type'      => 'mvi_events',
        'posts_per_page' => -1,
        'post_status'    => 'publish'
    );
    $events_query = new WP_Query( $args );
    $events_data = array(); // Array එකක් සූදානම් කරගැනීම

    if ( $events_query->have_posts() ) {
        while ( $events_query->have_posts() ) {
            $events_query->the_post();
            $post_id = get_the_ID();
            
            // අපි හදපු Custom Meta Data ටික ගන්නවා
            $events_data[] = array(
                'id'       => $post_id,
                'title'    => get_the_title(),
                'date'     => get_post_meta( $post_id, '_mvi_event_date', true ),
                'time'     => get_post_meta( $post_id, '_mvi_event_time', true ),
                'location' => get_post_meta( $post_id, '_mvi_event_location', true ),
                'link'     => get_permalink()
            );
        }
        wp_reset_postdata();
    }

    // දත්ත ටික JSON format එකෙන් (Status code 200 සමග) පිටතට යැවීම
    return new WP_REST_Response( $events_data, 200 );
}

// ==========================================
// 12. Frontend එක සඳහා අලුත් CSS File එක සම්බන්ධ කිරීම (Enqueue Scripts)
// ==========================================
function mvi_enqueue_custom_styles() {
    // assets/style.css file එක load කිරීම
    wp_enqueue_style( 'mvi-custom-style', plugin_dir_url( __FILE__ ) . 'assets/style.css', array(), '1.0.0' );
}
add_action( 'wp_enqueue_scripts', 'mvi_enqueue_custom_styles' );

// ==========================================
// 13. Frontend User Authentication (Login & Register)
// ==========================================

// 13.1 Register Form Shortcode [mvi_register]
function mvi_register_form_shortcode() {
    if ( is_user_logged_in() ) {
        return '<div class="mvi-reg-form-container" style="text-align:center;"><h3>' . esc_html__( 'You are already logged in.', 'mvi-event-manager' ) . '</h3></div>';
    }

    ob_start(); ?>
    <div class="mvi-reg-form-container" style="max-width: 500px; margin: 0 auto;">
        <h3><?php esc_html_e( 'Create a Student Account', 'mvi-event-manager' ); ?></h3>
        <div id="mvi-auth-msg" style="display:none; padding: 10px; border-radius: 5px; margin-bottom: 15px;"></div>
        
        <form id="mvi-register-form">
            <?php wp_nonce_field( 'mvi_auth_nonce', 'mvi_auth_sec' ); ?>
            <input type="hidden" name="action" value="mvi_ajax_user_register">
            
            <div class="mvi-form-group">
                <label><?php esc_html_e( 'Username', 'mvi-event-manager' ); ?></label>
                <input type="text" name="mvi_username" class="mvi-form-control" required>
            </div>
            <div class="mvi-form-group">
                <label><?php esc_html_e( 'Email Address', 'mvi-event-manager' ); ?></label>
                <input type="email" name="mvi_email" class="mvi-form-control" required>
            </div>
            <div class="mvi-form-group">
                <label><?php esc_html_e( 'Password', 'mvi-event-manager' ); ?></label>
                <input type="password" name="mvi_password" class="mvi-form-control" required>
            </div>
            
            <button type="submit" class="mvi-submit-btn" style="width: 100%;"><?php esc_html_e( 'Sign Up', 'mvi-event-manager' ); ?></button>
        </form>
    </div>

    <script>
    document.getElementById('mvi-register-form').addEventListener('submit', function(e) {
        e.preventDefault();
        let form = this;
        let msgBox = document.getElementById('mvi-auth-msg');
        let btn = form.querySelector('button');
        
        btn.innerHTML = 'Creating Account...';
        btn.disabled = true;

        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            body: new FormData(form)
        })
        .then(res => res.json())
        .then(data => {
            msgBox.style.display = 'block';
            if(data.success) {
                msgBox.style.backgroundColor = '#ecfdf5';
                msgBox.style.color = '#047857';
                msgBox.innerHTML = '✅ ' + data.data;
                setTimeout(() => { window.location.reload(); }, 1500); // සාර්ථක නම් පිටුව Refresh වේ
            } else {
                msgBox.style.backgroundColor = '#fef2f2';
                msgBox.style.color = '#b91c1c';
                msgBox.innerHTML = '❌ ' + data.data;
                btn.innerHTML = 'Sign Up';
                btn.disabled = false;
            }
        });
    });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode( 'mvi_register', 'mvi_register_form_shortcode' );


// 13.2 Login Form Shortcode [mvi_login]
function mvi_login_form_shortcode() {
    if ( is_user_logged_in() ) {
        return '<div class="mvi-reg-form-container" style="text-align:center;"><h3>' . esc_html__( 'You are already logged in.', 'mvi-event-manager' ) . '</h3></div>';
    }

    ob_start(); ?>
    <div class="mvi-reg-form-container" style="max-width: 500px; margin: 0 auto;">
        <h3><?php esc_html_e( 'Student Login', 'mvi-event-manager' ); ?></h3>
        <div id="mvi-login-msg" style="display:none; padding: 10px; border-radius: 5px; margin-bottom: 15px;"></div>
        
        <form id="mvi-login-form">
            <?php wp_nonce_field( 'mvi_auth_nonce', 'mvi_auth_sec' ); ?>
            <input type="hidden" name="action" value="mvi_ajax_user_login">
            
            <div class="mvi-form-group">
                <label><?php esc_html_e( 'Username or Email', 'mvi-event-manager' ); ?></label>
                <input type="text" name="mvi_username" class="mvi-form-control" required>
            </div>
            <div class="mvi-form-group">
                <label><?php esc_html_e( 'Password', 'mvi-event-manager' ); ?></label>
                <input type="password" name="mvi_password" class="mvi-form-control" required>
            </div>
            
            <button type="submit" class="mvi-submit-btn" style="width: 100%;"><?php esc_html_e( 'Log In', 'mvi-event-manager' ); ?></button>
        </form>
    </div>

    <script>
    document.getElementById('mvi-login-form').addEventListener('submit', function(e) {
        e.preventDefault();
        let form = this;
        let msgBox = document.getElementById('mvi-login-msg');
        let btn = form.querySelector('button');
        
        btn.innerHTML = 'Logging in...';
        btn.disabled = true;

        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            body: new FormData(form)
        })
        .then(res => res.json())
        .then(data => {
            msgBox.style.display = 'block';
            if(data.success) {
                msgBox.style.backgroundColor = '#ecfdf5';
                msgBox.style.color = '#047857';
                msgBox.innerHTML = '✅ ' + data.data;
                setTimeout(() => { window.location.reload(); }, 1500);
            } else {
                msgBox.style.backgroundColor = '#fef2f2';
                msgBox.style.color = '#b91c1c';
                msgBox.innerHTML = '❌ ' + data.data;
                btn.innerHTML = 'Log In';
                btn.disabled = false;
            }
        });
    });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode( 'mvi_login', 'mvi_login_form_shortcode' );


// 13.3 Database එකට දත්ත යවන AJAX Handlers
function mvi_ajax_user_register_handler() {
    check_ajax_referer( 'mvi_auth_nonce', 'mvi_auth_sec' );
    
    $username = sanitize_user( $_POST['mvi_username'] );
    $email    = sanitize_email( $_POST['mvi_email'] );
    $password = $_POST['mvi_password'];
    
    if ( username_exists( $username ) || email_exists( $email ) ) {
        wp_send_json_error( __( 'Username or Email already exists!', 'mvi-event-manager' ) );
    }
    
    $user_id = wp_create_user( $username, $password, $email );
    
    if ( ! is_wp_error( $user_id ) ) {
        // ගිණුම සෑදූ විගස ස්වයංක්‍රීයව ලොග් කරවීම
        wp_set_current_user( $user_id );
        wp_set_auth_cookie( $user_id );
        wp_send_json_success( __( 'Account created successfully! Redirecting...', 'mvi-event-manager' ) );
    } else {
        wp_send_json_error( $user_id->get_error_message() );
    }
}
add_action( 'wp_ajax_nopriv_mvi_ajax_user_register', 'mvi_ajax_user_register_handler' );

function mvi_ajax_user_login_handler() {
    check_ajax_referer( 'mvi_auth_nonce', 'mvi_auth_sec' );
    
    $creds = array(
        'user_login'    => sanitize_user( $_POST['mvi_username'] ),
        'user_password' => $_POST['mvi_password'],
        'remember'      => true
    );
    
    $user = wp_signon( $creds, false );
    
    if ( is_wp_error( $user ) ) {
        wp_send_json_error( __( 'Invalid username or password.', 'mvi-event-manager' ) );
    } else {
        wp_send_json_success( __( 'Login successful! Redirecting...', 'mvi-event-manager' ) );
    }
}
add_action( 'wp_ajax_nopriv_mvi_ajax_user_login', 'mvi_ajax_user_login_handler' );

// ==========================================
// 14. User Dashboard / My Profile (ලියාපදිංචි වූ Events පෙන්වීම)
// ==========================================

function mvi_user_dashboard_shortcode() {
    // ලොග් වී නැත්නම්, කරුණාකර ලොග් වන්න යැයි පෙන්වීම
    if ( ! is_user_logged_in() ) {
        return '<div class="mvi-reg-form-container" style="text-align:center;"><h3>' . esc_html__( 'Please log in to view your profile.', 'mvi-event-manager' ) . '</h3><a href="' . site_url( '/login/' ) . '" class="mvi-btn-modern">Log In</a></div>';
    }

    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;

    ob_start(); ?>
    <div class="mvi-reg-form-container" style="max-width: 800px; margin: 0 auto;">
        
        <!-- Profile Header කොටස -->
        <div style="text-align: center; margin-bottom: 30px;">
            <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #2563eb, #9333ea); color: #fff; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 32px; font-weight: bold; margin-bottom: 15px; box-shadow: 0 4px 15px rgba(37,99,235,0.3);">
                <?php echo esc_html( strtoupper( substr( $current_user->display_name, 0, 1 ) ) ); // නමේ මුල් අකුර ?>
            </div>
            <h2 style="margin: 0; color: #1e293b; font-weight: 600;"><?php esc_html_e( 'Welcome, ', 'mvi-event-manager' ); echo esc_html( $current_user->display_name ); ?>!</h2>
            <p style="color: #64748b; margin-top: 5px;"><?php echo esc_html( $current_user->user_email ); ?></p>
        </div>

        <h3 style="border-bottom: 2px solid #f1f5f9; padding-bottom: 10px; margin-bottom: 20px; color: #0f172a;"><?php esc_html_e( 'My Registered Events', 'mvi-event-manager' ); ?></h3>

        <!-- ලියාපදිංචි වූ Events Database එකෙන් ලබා ගැනීම -->
        <?php
        $args = array(
            'post_type'      => 'mvi_registrations',
            'author'         => $user_id, // මේ ලොග් වී සිටින පරිශීලකයාගේ පමණක් දත්ත
            'posts_per_page' => -1,
            'post_status'    => 'publish'
        );
        $user_events = new WP_Query( $args );

        if ( $user_events->have_posts() ) {
            echo '<div class="mvi-events-grid" style="grid-template-columns: 1fr; gap: 15px; margin-top: 0;">';
            while ( $user_events->have_posts() ) {
                $user_events->the_post();
                ?>
                <div class="mvi-event-card" style="padding: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                    <div>
                        <h4 style="margin: 0 0 8px 0; color: #1e293b; font-size: 18px;"><?php the_title(); ?></h4>
                        <span style="font-size: 13px; color: #64748b;">
                            🗓️ <?php esc_html_e( 'Registered on: ', 'mvi-event-manager' ); echo get_the_date(); ?>
                        </span>
                    </div>
                    <span style="background: #ecfdf5; color: #047857; padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: 600; border: 1px solid #a7f3d0;">
                        ✅ <?php esc_html_e( 'Confirmed', 'mvi-event-manager' ); ?>
                    </span>
                </div>
                <?php
            }
            wp_reset_postdata();
            echo '</div>';
        } else {
            // කිසිදු Event එකක් නැත්නම් පෙන්වන පණිවිඩය
            echo '<div style="background: #f8fafc; padding: 30px 20px; text-align: center; border-radius: 12px; color: #64748b; border: 1px dashed #cbd5e1;">';
            esc_html_e( 'You have not registered for any events yet.', 'mvi-event-manager' );
            echo '<br><br><a href="' . site_url('/upcoming-events/') . '" class="mvi-btn-modern">' . esc_html__( 'Browse Events', 'mvi-event-manager' ) . '</a>';
            echo '</div>';
        }
        ?>
        
        <!-- Logout Button -->
        <div style="margin-top: 40px; text-align: center;">
            <a href="<?php echo wp_logout_url( site_url( '/login/' ) ); ?>" style="color: #ef4444; text-decoration: none; font-weight: 500; font-size: 15px; padding: 10px 20px; background: #fef2f2; border-radius: 8px; transition: all 0.3s;">
                🚪 <?php esc_html_e( 'Log Out', 'mvi-event-manager' ); ?>
            </a>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
// Shortcode ලියාපදිංචි කිරීම
add_shortcode( 'mvi_my_profile', 'mvi_user_dashboard_shortcode' );