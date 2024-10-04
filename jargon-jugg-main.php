<?php
/*
Plugin Name: Jargon Juggler
Description: Creates an options page for business information to populate Jargon Pages like Website Terms, Privacy Page, etc.
Version: 4.0 - r 0
Author: Brighter Websites
*/

//*****************************************************************************

// Enqueue styles and scripts for the admin options page
function jargon_jugg_admin_scripts($hook) {
    // Check if we are on the appropriate admin page
    if ($hook !== 'toplevel_page_jargon_jugg') {
        return;
    }

    // Enqueue admin styles
    wp_enqueue_style('jargon_jugg_admin_css', plugin_dir_url(__FILE__) . 'css/admin-style.css');
    
    // Enqueue additional admin stylesheet if needed
wp_enqueue_style('options-styles', plugin_dir_url(__FILE__) . 'css/options-styles.css');
    
    // Enqueue jQuery (already included in WordPress core)
    wp_enqueue_script('jquery');

    // Enqueue jQuery InputMask library
    wp_enqueue_script('inputmask', 'https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.6/jquery.inputmask.min.js', array('jquery'), '5.0.6', true);
    
    // Enqueue custom admin script for ABN input mask
    wp_enqueue_script('jargon-abn-input-mask', plugin_dir_url(__FILE__) . 'js/abn-input-mask.js', array('jquery', 'inputmask'), '1.0', true);

    // Enqueue any other admin script if needed
     wp_enqueue_script('jargon_jugg_admin_js', plugin_dir_url(__FILE__) . 'js/admin-script.js', array('jquery'), null, true);
}
add_action('admin_enqueue_scripts', 'jargon_jugg_admin_scripts');


// Include the second file
plugin_dir_path(__FILE__) . 'jargon-jugg-template.php';

// Register custom page templates from the plugin directory
function custom_plugin_page_templates($templates) {
    $templates['jargon.php'] = __('All Jargon Pages', 'jargon-jugglar');
    //$templates['terms-of-service.php'] = __('Terms of Service', 'jargon-jugglar');
    // Add more templates as needed

    return $templates;
}
add_filter('theme_page_templates', 'custom_plugin_page_templates');


//*****************************************************************************
// Create the options page
function create_jargon_jugg_page() {
    add_menu_page(
        'Jargon Juggler',	   // Page title
        'Jargon Juggler',           // Menu title
        'manage_options',          // Capability required to access the page
        'jargon_jugg',   	   // Page slug (unique)
	    'render_jargon_jugg_page' // Callback function to render the page
      );
}
add_action('admin_menu', 'create_jargon_jugg_page');


//*****************************************************************************
// Callback function to render the options page content
function render_jargon_jugg_page() {
    ?>
    <div class="jargon_jugg_page_page_style wrap">
        <h1>Jargon Juggler</h1>
        <div>
            <p>Manage legal pages business and website owner information in one place, and generate legal pages with ease.</p>
        </div>
        <p class="nav-tab-wrapper">
            <a href="#tab-welcome" class="nav-tab">Jargon Juggler</a>
            <a href="#tab-business" class="nav-tab">Business Info</a>
            <a href="#tab-site" class="nav-tab">Site Owner Info</a>
            <a href="#tab-templates" class="nav-tab">Content Templates</a>
            <a href="#tab-help" class="nav-tab">Help</a>
        </p>

        <form id="jargon-main-form" method="post" action="options.php">
            <?php
            settings_fields('jargon_options_group');
            ?>
        
            <div id="tab-welcome" class="tab-content">
                <h2 class="jargon-heading">Welcome to Jargon</h2>
                <?php do_settings_sections('jargon_jugg_welcome'); ?>
            </div>

            <div id="tab-business" class="tab-content">
                <h2 class="jargon-heading">Configure Business Contact and General Information</h2>
                <?php do_settings_sections('jargon_jugg_businessinfo'); ?>
            </div>

            <div id="tab-site" class="tab-content">
                <h2 class="jargon-heading">Configure Website Owner</h2>
                <p class="description">This information relates to the website's owner and is specifically for instances where the data processing contacts are different from the website or business contacts. Enter the same information as the business unless the website owner (for DPO purposes) is different - for example, if the website is a lead generation website.</p>
                <?php do_settings_sections('jargon_jugg_siteowner'); ?>
            </div>
            
            <div id="tab-help" class="tab-content">
                <?php do_settings_sections('jargon_jugg_help'); ?>
            </div>
            
            <?php //submit_button('Save Settings'); ?>
            <?php //settings_errors(); ?>
            
            
            <!-- Add settings fields and sections here -->
            <?php submit_button('Save Settings', 'primary', 'save_settings_button'); ?>
        </form>

            <div id="tab-templates" class="tab-content">
                <h2 class="jargon-heading">Configure Jargon Pages and Content Templates</h2>
                <p class="description">Use shortcodes like {name}, {email}, {datetime}, etc.</p>
                <?php do_settings_sections('jargon_jugg_templates'); ?>
                
                <!--  //testing <form method="post" action="">
                    <input type="hidden" name="jargon_create_pages_action" value="1">
                    <?php // submit_button('Create Pages'); ?>
                </form> --> 
                
                
                <form method="post" action="">
                    <input type="hidden" name="jargon_create_pages_action" value="1">
                    <?php submit_button('Create Pages', 'primary', 'create_pages_button'); ?>
                </form>

                <?php
                // Handle form submission
                if (isset($_POST['jargon_create_pages_action']) && $_POST['jargon_create_pages_action'] == 1) {
                    // Call your function to create/update pages
                    create_legal_pages();

                    // Optionally, add a message to confirm to the user that pages were created/updated
                    echo '<div class="updated"><p>Pages created/updated successfully!</p></div>';
                }
                ?>
            </div>

          
<?php
        // Display settings errors or success messages
        settings_errors();
        ?>
            
            
       
    </div>
    <?php
}



//*****************************************************************************
// Register settings and add fields   use jargon_options_group
function jargon_jugg_settings_init() {
    register_setting('jargon_options_group', 'jargon_options', 'jargon_sanitize_callback_function');
    
     //**************************************
    // Welcome Section
    add_settings_section(
        'jargon_welcome',
        'Welcome',
        'jargon_jugg_section_callback',
        'jargon_jugg_welcome'
    );

    //**************************************
    // Business Info Section
    add_settings_section(
        'jargon_settings_businessinfo',    // Section ID
        'Business Information',      // Section title
        'jargon_jugg_section_callback', // Callback function for section content
        'jargon_jugg_businessinfo' // Page slug
    );

    add_settings_field(    
        'jargon_business_name',  // Field ID
        'Business Name',  // Field title
        'jargon_business_name_callback', // Callback function for field content
        'jargon_jugg_businessinfo', // Page slug
        'jargon_settings_businessinfo' // Section ID
    );
    
    add_settings_field(
        'business_type_schema',            // Field ID
        'Business Type Schema',            // Field title
        'jargon_business_type_schema_callback', // Callback function for field content
        'jargon_jugg_businessinfo',        // Page slug
        'jargon_settings_businessinfo'     // Section ID
    );
    
    add_settings_field(
        'business_service',                // Field ID
        'Business Service',                // Field title
        'jargon_business_service_callback', // Callback function for field content
        'jargon_jugg_businessinfo',        // Page slug
        'jargon_settings_businessinfo'     // Section ID
    );
    
    
    add_settings_field(
        'jargon_phone_number',  // Field ID
        'Phone number',  // Field title
        'jargon_phone_number_callback', // Callback function for field content
        'jargon_jugg_businessinfo', // Page slug
        'jargon_settings_businessinfo' // Section ID
    );
    
    add_settings_field(
        'jargon_email',  // Field ID
        'Email',  // Field title
        'jargon_email_callback', // Callback function for field content
        'jargon_jugg_businessinfo', // Page slug
        'jargon_settings_businessinfo' // Section ID
    );
    
    add_settings_field(
        'jargon_address',  // Field ID
        'Address',  // Field title
        'jargon_address_callback', // Callback function for field content
        'jargon_jugg_businessinfo', // Page slug
        'jargon_settings_businessinfo' // Section ID
    );
    
    add_settings_field(
        'jargon_city',  // Field ID
        'City',  // Field title
        'jargon_city_callback', // Callback function for field content
        'jargon_jugg_businessinfo', // Page slug
        'jargon_settings_businessinfo' // Section ID
    );
    
    add_settings_field(
        'jargon_state',  // Field ID
        'State',  // Field title
        'jargon_state_callback', // Callback function for field content
        'jargon_jugg_businessinfo', // Page slug
        'jargon_settings_businessinfo' // Section ID
    );
    
    add_settings_field(
        'jargon_postcode',  // Field ID
        'Postcode',  // Field title
        'jargon_postcode_callback', // Callback function for field content
        'jargon_jugg_businessinfo', // Page slug
        'jargon_settings_businessinfo' // Section ID
    );
    
    add_settings_field(
        'jargon_country',  // Field ID
        'Country',  // Field title
        'jargon_country_callback', // Callback function for field content
        'jargon_jugg_businessinfo', // Page slug
        'jargon_settings_businessinfo' // Section ID
    );
    
    add_settings_field(
        'jargon_lat',  // Field ID
        'Latitude',  // Field title
        'jargon_lat_callback', // Callback function for field content
        'jargon_jugg_businessinfo', // Page slug
        'jargon_settings_businessinfo' // Section ID
    );
    
    add_settings_field(
        'jargon_long',  // Field ID
        'Longitude',  // Field title
        'jargon_long_callback', // Callback function for field content
        'jargon_jugg_businessinfo', // Page slug
        'jargon_settings_businessinfo' // Section ID
    );
    
    add_settings_field(
        'jargon_hours',  // Field ID
        'Business Hours',  // Field title
        'jargon_hours_callback', // Callback function for field content
        'jargon_jugg_businessinfo', // Page slug
        'jargon_settings_businessinfo' // Section ID
    );
    
    add_settings_field(
        'jargon_social_link_facebook',  // Field ID
        'Facebook URL',  // Field title
        'jargon_social_link_facebook_callback', // Callback function for field content
        'jargon_jugg_businessinfo', // Page slug
        'jargon_settings_businessinfo' // Section ID
    );
    
    add_settings_field(
        'jargon_social_link_twitter',  // Field ID
        'Twitter URL',  // Field title
        'jargon_social_link_twitter_callback', // Callback function for field content
        'jargon_jugg_businessinfo', // Page slug
        'jargon_settings_businessinfo' // Section ID
    );
    
    add_settings_field(
        'jargon_social_link_instagram',  // Field ID
        'Instagram URL',  // Field title
        'jargon_social_link_instagram_callback', // Callback function for field content
        'jargon_jugg_businessinfo', // Page slug
        'jargon_settings_businessinfo' // Section ID
    );
    
    add_settings_field(
        'jargon_social_link_youtube',  // Field ID
        'YouTube URL',  // Field title
        'jargon_social_link_youtube_callback', // Callback function for field content
        'jargon_jugg_businessinfo', // Page slug
        'jargon_settings_businessinfo' // Section ID
    );
    
    add_settings_field(
        'jargon_social_link_linkedin',  // Field ID
        'LinkedIn URL',  // Field title
        'jargon_social_link_linkedin_callback', // Callback function for field content
        'jargon_jugg_businessinfo', // Page slug
        'jargon_settings_businessinfo' // Section ID
    );
    
    add_settings_field(
        'jargon_social_link_pinterest',  // Field ID
        'Pinterest URL',  // Field title
        'jargon_social_link_pinterest_callback', // Callback function for field content
        'jargon_jugg_businessinfo', // Page slug
        'jargon_settings_businessinfo' // Section ID
    );
    
    add_settings_field(
        'jargon_social_link_google_my_business',  // Field ID
        'Google My Business URL',  // Field title
        'jargon_social_link_google_my_business_callback', // Callback function for field content
        'jargon_jugg_businessinfo', // Page slug
        'jargon_settings_businessinfo' // Section ID
    );


    
    //**************************************
    // Site Owner Info Section
    add_settings_section(
        'jargon_siteowner',
        'Site Owner Info',
        'jargon_jugg_section_callback',
        'jargon_jugg_siteowner'
    );
    
     // Site Owner Name
    add_settings_field(
        'site_owner_name',
        'Owner Name',
        'jargon_site_owner_name_callback',
        'jargon_jugg_siteowner',
        'jargon_siteowner'
    );

    // Site Owner ABN
    add_settings_field(
        'site_owner_abn',
        'ABN',
        'jargon_site_owner_abn_callback',
        'jargon_jugg_siteowner',
        'jargon_siteowner'
    );

    // Site Owner Phone
    add_settings_field(
        'site_owner_phone',
        'Phone',
        'jargon_site_owner_phone_callback',
        'jargon_jugg_siteowner',
        'jargon_siteowner'
    );

    // Site Owner Email
    add_settings_field(
        'site_owner_email',
        'Email',
        'jargon_site_owner_email_callback',
        'jargon_jugg_siteowner',
        'jargon_siteowner'
    );

    // Site Owner Website
    add_settings_field(
        'site_owner_web',
        'Website',
        'jargon_site_owner_web_callback',
        'jargon_jugg_siteowner',
        'jargon_siteowner'
    );

    // Privacy DPO Contact Email
    add_settings_field(
        'privacy_dpo_contact_email',
        'Privacy DPO Contact Email',
        'jargon_privacy_dpo_contact_email_callback',
        'jargon_jugg_siteowner',
        'jargon_siteowner'
    );

    // Privacy DPO Contact Address
    add_settings_field(
        'privacy_dpo_contact_address',
        'Privacy DPO Contact Address',
        'jargon_privacy_dpo_contact_address_callback',
        'jargon_jugg_siteowner',
        'jargon_siteowner'
    );
	
    // Content Templates Section
    add_settings_section(
        'jargon_help',
        'Content Templates',
        'jargon_jugg_section_callback',
        'jargon_jugg_templates'
    );
	
	//**************************************
    // Help Section
    add_settings_section(
        'jargon_help',
        'Help',
        'jargon_jugg_section_callback',
        'jargon_jugg_help'
    );
}

add_action('admin_init', 'jargon_jugg_settings_init');


//*********************************************************************************
// Callback function for settings section

// Callback function for settings section
function jargon_jugg_section_callback($args) {
    
    
}


//*****************************************************************************
// Callback function for settings fields - Business Info Section
function jargon_business_name_callback() {
    $options = get_option('jargon_options');
    ?>

            <input type="text" id="jargon_business_name" name="jargon_options[business_name]" value="<?php echo esc_attr(isset($options['business_name']) ? $options['business_name'] : ''); ?>">
        
    <tr class="jargon-heading"><th scope="row" colspan="2"><h4>Business Service Info</h4></th></tr>
    <?php
}

// Callback function for Business Type Schema field
function jargon_business_type_schema_callback() {
    $options = get_option('jargon_options');
    ?>
    <input type="text" name="jargon_options[business_type_schema]" value="<?php echo isset($options['business_type_schema']) ? esc_attr($options['business_type_schema']) : ''; ?>">
    <?php
}

// Callback function for Business Service field
function jargon_business_service_callback() {
    $options = get_option('jargon_options');
    ?>
    <input type="text" name="jargon_options[business_service]" value="<?php echo isset($options['business_service']) ? esc_attr($options['business_service']) : ''; ?>">
    <tr class="jargon-heading"><th scope="row" colspan="2"><h4>Business Contact Info</h4></th></tr>
    <?php
}


// Callback for Phone Number
function jargon_phone_number_callback() {
    $options = get_option('jargon_options');
    ?>
    <input type="text" name="jargon_options[phone_number]" value="<?php echo isset($options['phone_number']) ? esc_attr($options['phone_number']) : ''; ?>">
    <?php
}

// Callback for Email
function jargon_email_callback() {
    $options = get_option('jargon_options');
    ?>
    <input type="email" name="jargon_options[email]" value="<?php echo isset($options['email']) ? esc_attr($options['email']) : ''; ?>">
    <?php
}

// Callback for Address
function jargon_address_callback() {
    $options = get_option('jargon_options');
    ?>
    <input type="text" name="jargon_options[address]" value="<?php echo isset($options['address']) ? esc_attr($options['address']) : ''; ?>">
    <?php
}

// Callback for City
function jargon_city_callback() {
    $options = get_option('jargon_options');
    ?>
    <input type="text" name="jargon_options[city]" value="<?php echo isset($options['city']) ? esc_attr($options['city']) : ''; ?>">
    <?php
}

// Callback for State
function jargon_state_callback() {
    $options = get_option('jargon_options');
    ?>
    <input type="text" name="jargon_options[state]" value="<?php echo isset($options['state']) ? esc_attr($options['state']) : ''; ?>">
    <?php
}

// Callback for Postcode
function jargon_postcode_callback() {
    $options = get_option('jargon_options');
    ?>
    <input type="text" name="jargon_options[postcode]" pattern="[0-9]{4}" title="Please enter 4 digits" value="<?php echo isset($options['postcode']) ? esc_attr($options['postcode']) : ''; ?>" required>

    <?php
}

// Callback for Country
function jargon_country_callback() {
    $options = get_option('jargon_options');
    ?>
    <input type="text" name="jargon_options[country]" value="<?php echo isset($options['country']) ? esc_attr($options['country']) : ''; ?>">
    <?php
}

// Callback for Latitude
function jargon_lat_callback() {
    $options = get_option('jargon_options');
    ?>
                <input type="number" name="jargon_options[lat]" step="0.000001" min="-180" max="180" value="<?php echo isset($options['lat']) ? esc_attr($options['lat']) : ''; ?>" >
    <?php
}

// Callback for Longitude
function jargon_long_callback() {
    $options = get_option('jargon_options');
    ?>
    <input type="number" name="jargon_options[long]" step="0.000001" min="-180" max="180" value="<?php echo isset($options['long']) ? esc_attr($options['long']) : ''; ?>" >
    <?php
}

// Callback for Hours
function jargon_hours_callback() {
    $options = get_option('jargon_options');
    ?>
        <textarea name="jargon_options[hours]"><?php echo isset($options['hours']) ? esc_textarea($options['hours']) : ''; ?></textarea>
    <tr class="jargon-heading"><th scope="row" colspan="2"><h4>Social Media Links</h4></th></tr>
     <?php
}

// Callback for Facebook URL
function jargon_social_link_facebook_callback() {
    $options = get_option('jargon_options');
    ?>
    <input type="url" name="jargon_options[social_link_facebook]" value="<?php echo isset($options['social_link_facebook']) ? esc_attr($options['social_link_facebook']) : ''; ?>">
    <?php
}

// Callback for Twitter URL
function jargon_social_link_twitter_callback() {
    $options = get_option('jargon_options');
    ?>
    <input type="url" name="jargon_options[social_link_twitter]" value="<?php echo isset($options['social_link_twitter']) ? esc_attr($options['social_link_twitter']) : ''; ?>">
    <?php
}

// Callback for Instagram URL
function jargon_social_link_instagram_callback() {
    $options = get_option('jargon_options');
    ?>
    <input type="url" name="jargon_options[social_link_instagram]" value="<?php echo isset($options['social_link_instagram']) ? esc_attr($options['social_link_instagram']) : ''; ?>">
    <?php
}

// Callback for YouTube URL
function jargon_social_link_youtube_callback() {
    $options = get_option('jargon_options');
    ?>
    <input type="url" name="jargon_options[social_link_youtube]" value="<?php echo isset($options['social_link_youtube']) ? esc_attr($options['social_link_youtube']) : ''; ?>">
    <?php
}

// Callback for LinkedIn URL
function jargon_social_link_linkedin_callback() {
    $options = get_option('jargon_options');
    ?>
    <input type="url" name="jargon_options[social_link_linkedin]" value="<?php echo isset($options['social_link_linkedin']) ? esc_attr($options['social_link_linkedin']) : ''; ?>">
    <?php
}

// Callback for Pinterest URL
function jargon_social_link_pinterest_callback() {
    $options = get_option('jargon_options');
    ?>
    <input type="url" name="jargon_options[social_link_pinterest]" value="<?php echo isset($options['social_link_pinterest']) ? esc_attr($options['social_link_pinterest']) : ''; ?>">
    <?php
}

// Callback for Google My Business URL
function jargon_social_link_google_my_business_callback() {
    $options = get_option('jargon_options');
    ?>
    <input type="url" name="jargon_options[social_link_google_my_business]" value="<?php echo isset($options['social_link_google_my_business']) ? esc_attr($options['social_link_google_my_business']) : ''; ?>">
    <?php
}

// Callback for Site Owner Name
function jargon_site_owner_name_callback() {
    $options = get_option('jargon_options');
    ?>
    <input type="text" name="jargon_options[site_owner_name]" value="<?php echo isset($options['site_owner_name']) ? esc_attr($options['site_owner_name']) : ''; ?>">
    <?php
}

// Callback for Site Owner ABN
function jargon_site_owner_abn_callback() {
    $options = get_option('jargon_options');
    ?>
    <!-- <input type="text" name="site_owner_abn" id="site_owner_abn" value="<?php // echo esc_attr(get_option('site_owner_abn')); ?>" /> -->
    <input type="text" name="jargon_options[site_owner_abn]" id="site_owner_abn" value="<?php echo isset($options['site_owner_abn']) ? esc_attr($options['site_owner_abn']) : ''; ?>">
    <?php
}


// Callback for Site Owner Phone
function jargon_site_owner_phone_callback() {
    $options = get_option('jargon_options');
    ?>
    <input type="text" name="jargon_options[site_owner_phone]" value="<?php echo isset($options['site_owner_phone']) ? esc_attr($options['site_owner_phone']) : ''; ?>">
    <?php
}


// Callback for Site Owner Email
function jargon_site_owner_email_callback() {
    $options = get_option('jargon_options');
    ?>
    <input type="email" name="jargon_options[site_owner_email]" value="<?php echo isset($options['site_owner_email']) ? esc_attr($options['site_owner_email']) : ''; ?>">
    <?php
}

// Callback for Site Owner Website
function jargon_site_owner_web_callback() {
    $options = get_option('jargon_options');
    ?>
    <input type="url" name="jargon_options[site_owner_web]" value="<?php echo isset($options['site_owner_web']) ? esc_url( $options['site_owner_web'] ) : ''; ?>">
    <?php
}

// Callback for Privacy DPO Contact Email
function jargon_privacy_dpo_contact_email_callback() {
    $options = get_option('jargon_options');
    ?>
    <input type="email" name="jargon_options[privacy_dpo_contact_email]" value="<?php echo isset($options['privacy_dpo_contact_email']) ? esc_attr($options['privacy_dpo_contact_email']) : ''; ?>">
    <?php
}

// Callback for Privacy DPO Contact Address
function jargon_privacy_dpo_contact_address_callback() {
    $options = get_option('jargon_options');
    ?>
    <textarea name="jargon_options[privacy_dpo_contact_address]"><?php echo isset($options['privacy_dpo_contact_address']) ? esc_textarea($options['privacy_dpo_contact_address']) : ''; ?></textarea>
    <?php
}





//*****************************************************************************
// Sanitize callback function for Jargon options.
function jargon_sanitize_callback_function($input) {
    $sanitized_input = array();

    // Define the sanitization functions for each field
    $fields_to_sanitize = array(
        'business_name' => function($value) {
            return ucwords(strtolower($value));
        },
         'business_type_schema' => function($value) {
            return ucwords(strtolower($value));
        },
         'business_service' => function($value) {
            return ucwords(strtolower($value));
        },
        
        
        'phone_number' => 'sanitize_text_field',
        'email' => 'sanitize_email',
        'address' => 'sanitize_text_field',
        'city' => 'sanitize_text_field',
        'state' => 'sanitize_text_field',
        'postcode' => function($value) {
            return preg_replace('/\D/', '', $value); // Remove non-numeric characters
        },
        'country' => 'sanitize_text_field',
        'lat' => function($value) {
            return number_format(floatval(sanitize_text_field($value)), 6); // Limit to 6 decimal places
        },
        'long' => function($value) {
            return number_format(floatval(sanitize_text_field($value)), 6); // Limit to 6 decimal places
        },
        'hours' => 'sanitize_textarea_field',
        'site_owner_name' => 'sanitize_text_field',
        /* 'site_owner_abn' => function($value) {
            return sanitize_text_field(preg_replace('/\D/', '', $value)); // Remove non-numeric characters
        },*/
        
         'site_owner_abn' => 'sanitize_text_field',

        'site_owner_phone' => 'sanitize_text_field',
        'site_owner_email' => 'sanitize_email',
        'site_owner_web' => 'esc_url_raw',
        'privacy_dpo_contact_email' => 'sanitize_email',
        'privacy_dpo_contact_address' => 'sanitize_textarea_field'
    );

    // Sanitize each field if present
    foreach ($fields_to_sanitize as $field => $sanitize_function) {
        if (isset($input[$field])) {
            $sanitized_input[$field] = call_user_func($sanitize_function, $input[$field]);
        }
    }

    // Social Media Links
    $social_links = array(
        'social_link_facebook',
        'social_link_twitter',
        'social_link_instagram',
        'social_link_youtube',
        'social_link_linkedin',
        'social_link_pinterest',
        'social_link_google_my_business'
    );

    foreach ($social_links as $link) {
        if (isset($input[$link]) && !empty($input[$link])) {
            $sanitized_input[$link] = esc_url_raw(add_http_if_missing($input[$link]));
        } else {
            $sanitized_input[$link] = ''; // Ensure empty value is saved if no input
        }
    }

    return $sanitized_input;
}


/**
 * Helper function to add http:// or https:// if missing from the URL.
 *
 * @param string $url The URL to check.
 * @return string The URL with http:// or https:// added if missing.
 */
function add_http_if_missing($url) {
    if (strpos($url, 'http://') !== 0 && strpos($url, 'https://') !== 0) {
        $url = 'https://' . $url;
    }
    return $url;
}

//*****************************************************************************
// Create Shortcodes
//
// Use it in your content like this: [jargon_jugg_setting="business_name"].
// Replace 'business_info' with your desired shortcode name.
// Use this shortcode in posts, pages, or any other content area to display
// the values of your registered settings.
//*****************************************************************************

function jargon_jugg_shortcode($atts) {
    // Define your settings here
    $settings = array(
        'business_name',
        'business_type_schema',
        'business_service',
        'delivered_services',
        'customers',
        'business_areas',
        'phone_number',
        'email',
        'address',
        'city',
        'state',
        'postcode',
        'country',
        'lat',
        'long',
        'hours',
        'social_link_facebook',
        'social_link_twitter',
        'social_link_instagram',
        'social_link_youtube',
        'social_link_linkedin',
        'social_link_pinterest',
        'social_link_google_my_business',
        'site_owner_name',
        'site_owner_abn',
        'site_owner_phone',
        'site_owner_email',
        'site_owner_web',
        'privacy_dpo_contact_email',
        'privacy_dpo_contact_address'
    );

    // Process the attributes (if any)
    $atts = shortcode_atts(array(
        'setting' => '' // Default setting name
    ), $atts);

    // Check if the 'setting' attribute is valid
    if (in_array($atts['setting'], $settings)) {
        // Get the value of the specified setting
        $setting_value = get_option($atts['setting']);

        // Sanitize the retrieved value if necessary
        if (in_array($atts['setting'], array('phone_number', 'site_owner_phone'))) {
            $setting_value = sanitize_text_field($setting_value);
        } elseif (in_array($atts['setting'], array('email', 'site_owner_email', 'privacy_dpo_contact_email'))) {
            $setting_value = sanitize_email($setting_value);
        } elseif (in_array($atts['setting'], array('social_link_facebook', 'social_link_twitter', 'social_link_instagram', 'social_link_youtube', 'social_link_linkedin', 'social_link_pinterest', 'social_link_google_my_business', 'site_owner_web'))) {
            $setting_value = esc_url($setting_value);
        } elseif (in_array($atts['setting'], array('business_name', 'business_type_schema', 'business_service', 'delivered_services', 'customers', 'business_areas', 'address', 'city', 'state', 'country', 'privacy_dpo_contact_address'))) {
            $setting_value = esc_html($setting_value);
        } elseif (in_array($atts['setting'], array('postcode'))) {
            $setting_value = intval($setting_value);
        } elseif (in_array($atts['setting'], array('lat', 'long'))) {
            $setting_value = floatval($setting_value);
        }

        // Output the setting value
        return $setting_value;
    } else {
        // Invalid or missing 'setting' attribute
        return 'Invalid or missing setting attribute.';
    }
}

// Register the shortcode
add_shortcode('jargon_jugg', 'jargon_jugg_shortcode');






