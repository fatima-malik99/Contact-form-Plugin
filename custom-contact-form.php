<?php
/*
Plugin Name: Custom Contact Form
Description: A simple contact form plugin.
Version: 1.0
Author: Fatima Malik
*/

function register_contact_submission_post_type() {
    $labels = array(
        'name' => 'Submissions',
        'singular_name' => 'Submission',
        'edit_item' => 'View Submission',
        'menu_name' => 'Submissions',
        'view_item' => 'View Submission',
        'view_items' => 'View Submissions',
        'search_items' => 'Search Submissions',
        'not_found' => 'No submissions found',
        'not_found_in_trash' => 'No submissions found in trash',
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'exclude_from_search' => true,
        'publicly_queryable' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_icon' => 'dashicons-email-alt',
        'capability_type' => 'post',
        'capabilities'       => [
          'create_posts' => false,
      ],
      'map_meta_cap'       => true,
        'hierarchical' => false,
        'supports' => false,
        'has_archive' => true,
        'rewrite' => false,
        'query_var' => false,
        'menu_position' => 30,
    );
  
    register_post_type('contact_submission', $args);
}
add_action('init', 'register_contact_submission_post_type');


function create_meta_box()
{
    // Create custom meta box to display submission
    add_meta_box('custom_contact_form', 'Contact Submission Details', 'display_contact_submission_details', 'contact_submission', 'normal', 'high');
}
add_action('add_meta_boxes', 'create_meta_box');

function display_contact_submission_details($post)
{
    // Retrieve the submission data from the post content
    $submission_data = get_post_field('post_content', $post->ID);

    // Output the submission data in the meta box
    echo '<pre>' . esc_html($submission_data) . '</pre>';
}

// Shortcode 
function custom_contact_form_shortcode() {
    ob_start(); ?>
    <section class="contact_section layout_padding">
    <div class="container">
      <div class="heading_container">
        <h2>
          Request A Call Back
        </h2>
      </div>
      <div class="">
        <div class="">
          <div class="row">
            <div class="col-md-9 mx-auto">
              <div class="contact-form">
    <form method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">
        <div>
        <label for="name"></label>
        <input type="text" placeholder="Full Name" name="name" required>
        </div>
        <div>
        <label for="phone"></label>
        <input type="tel" placeholder="Phone Number" name="phone" required>
        </div>
        <div>
        <label for="email"></label>
        <input type="email" placeholder="Email Address" name="email" required>
        </div>
        <div>
        <label for="message"></label>
        <input type="text" placeholder="Message" class="input_message" name="message" required>
        </div>
        <div class="d-flex justify-content-center">
        <button type="submit" class="btn_on-hover" name="submit" value="Send">
            Send
        </button>
        </div>
    </form>
               </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
    <?php
    return ob_get_clean();
}
add_shortcode('custom_contact_form', 'custom_contact_form_shortcode');

// Contact form data handling
function handle_contact_form_submission() {
    if (isset($_POST['submit'])) {
        $name = sanitize_text_field($_POST['name']);
        $phone = sanitize_text_field($_POST['phone']);
        $email = sanitize_email($_POST['email']);
        $message = sanitize_textarea_field($_POST['message']);

        // Get the latest ID from options or default to 0
        $latest_id = get_option('latest_contact_submission_id', 0);

        // Generate a unique ID starting from 0
        $unique_id = $latest_id;

        $post_data = array(
            'post_title' =>  $name . ' - ' . $unique_id,
            'post_content' => "Name: $name\nPhone: $phone\nEmail: $email\nMessage: $message",
            'post_status' => 'publish',
            'post_type' => 'contact_submission',
        );

        $post_id = wp_insert_post($post_data);

        if ($post_id) {
            // Increment the latest ID for the next submission
            update_option('latest_contact_submission_id', $latest_id + 1);

            // Set the post name (slug) to include the unique ID
            $new_post_data = array(
                'ID' => $post_id,
                'post_name' => sanitize_title_with_dashes($name . '-' . $unique_id),
            );
            wp_update_post($new_post_data);

            // Success, you can redirect or display a success message here
            wp_redirect(site_url('/thank-you/'));
            exit;
        } else {
            // Handle error
            wp_redirect(site_url('/error/'));
            exit;
        }
    }
}
add_action('init', 'handle_contact_form_submission', 20);

// Enqueue the custom CSS file
function enqueue_custom_contact_form_styles() {
    // Get the plugin directory URL
    $plugin_url = plugin_dir_url(__FILE__);

    // Enqueue the custom CSS file
    wp_enqueue_style('custom-contact-form-styles', $plugin_url . 'css/custom-contact-form.css');
}

add_action('wp_enqueue_scripts', 'enqueue_custom_contact_form_styles');
