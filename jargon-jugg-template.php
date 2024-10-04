<?php
//Register custom page templates from the plugin directory
//create pages if not already created or update a page with content

// Function to create or update a page with content
function create_or_update_page($title, $content, $page_type, $template) {
   
    // Check if the page exists (including trashed pages)
    $page = get_page_by_path(sanitize_title($title), OBJECT, 'page');
    
       if (!$page || $page->post_status === 'trash') {
        // Page doesn't exist or is in trash, create it
        
        $new_page = array(
            'post_title' => $title,
            'post_content' => $content,
            'post_status' => 'publish',
            'post_type' => 'page',
            'meta_input' => array(
                '_wp_page_template' => $template // Set the page template
            )
        );
        
        $page_id = wp_insert_post($new_page);

        // Assign custom taxonomy
        wp_set_object_terms($page_id, 'legal', 'page_type');
        
    } else {
        
        // Page exists (including trashed), update content if needed
        
        $update_existing_content = true; // Ensure this variable is defined or set accordingly
        
       if ($update_existing_content) {
            $page->post_content = $content;
            // Update meta key for page template
           //update_post_meta($page->ID, '_wp_page_template', plugin_basename($template));  <--Previous
            
            update_post_meta($page->ID, '_wp_page_template', $template);
            
            wp_update_post($page);
            
            // Assign custom taxonomy
            wp_set_object_terms($page->ID, 'legal', 'page_type');
        }
    }
}

// Hook into WordPress to create pages if not already created
add_action('admin_init', 'create_legal_pages');


function create_legal_pages() {
    
    // Check if the button was pressed
    if (isset($_POST['jargon_create_pages_action']) && $_POST['jargon_create_pages_action'] == 1) {
      
        $pages = [
            'Privacy' => [
                'template' => 'jargon.php',
                'content_file' => plugin_dir_path(__FILE__) . 'templates/privacy-policy-template.php',
            
            //'Privacy' => plugin_dir_path(__FILE__) . 'templates/privacy-policy-template.php',
            //'Website Terms' => plugin_dir_path(__FILE__) . 'templates/website-terms-template.php',
            //'Accessibility Statement' => plugin_dir_path(__FILE__) . 'templates/accessibility-statement-template.php',
            //'SiteMap' => plugin_dir_path(__FILE__) . 'templates/sitemap-template.php',
            //'Test my Jargon' => plugin_dir_path(__FILE__) . 'templates/test-my-jargon-template.php',
            ]
        ];
        
     
       //foreach ($pages as $title => $page_info) {
       //     if (file_exists($page_info['content'])) {
       //         $content = include $page_info['content'];
       //         create_or_update_page($title, $content, 'legal', $page_info['template']);
       //     }
       // }
        
        foreach ($pages as $title => $settings) {
            if (file_exists($settings['content_file'])) {
                // Load content from file
                $content = file_get_contents($settings['content_file']);
                // Create or update the page with the loaded content and specified template
                create_or_update_page($title, $content, $settings['template']);
            }
        }
        
        
        // Optionally, add a message to confirm to the user that pages were created/updated
        echo '<div class="updated"><p>Pages created/updated successfully!</p></div>';
    }
}


// Register custom page templates from the plugin directory
function custom_plugin_page_templates($templates) {
    $templates[plugin_dir_path(__FILE__) . 'templates/jargon.php'] = __('All Jargon Pages', 'jargon-jugglar');
    // Add more templates as needed

    return $templates;
}
add_filter('theme_page_templates', 'custom_plugin_page_templates');
