/**
 * Admin page navigation function
 *
 * Allows the tabs in the admin page to switch. 
 * 
 * 
 **/
jQuery(document).ready(function($) {
    // Hide all tab contents except the first one
    $('.tab-content').hide();
    $('.tab-content:first').show();
    $('.nav-tab:first').addClass('nav-tab-active');

    // Remember the initially active tab
    var activeTab = $('.nav-tab-active').attr('href');

    // Tab navigation
    $(document).on('click', '.nav-tab', function(e) {
        e.preventDefault();
        $('.nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        $('.tab-content').hide();

        var selectedTab = $(this).attr('href');
        $(selectedTab).show();

        // Toggle visibility of Save Settings button based on active tab
        if (selectedTab === '#tab-templates') {
            $('#jargon-main-form').hide();
             $('#jargon-main-form > p.submit').hide(); // Hide the submit paragraph
        } else {
            $('#jargon-main-form').show();
            $('#jargon-main-form > p.submit').show(); // Show the submit paragraph for other tabs
        }

        // Store the selected tab in sessionStorage
        sessionStorage.setItem('active_tab', selectedTab);
    });

    // Restore the active tab on page load
    var storedTab = sessionStorage.getItem('active_tab');
    if (storedTab) {
        $('.nav-tab').removeClass('nav-tab-active');
        $('a[href="' + storedTab + '"]').addClass('nav-tab-active');
        $('.tab-content').hide();
        $(storedTab).show();
        
        // Ensure Save Settings button visibility matches the restored tab
        if (storedTab === '#tab-templates') {
            $('#jargon-main-form').hide();
             $('#jargon-main-form > p.submit').hide(); // Hide the submit paragraph
        } else {
            $('#jargon-main-form').show();
            $('#jargon-main-form > p.submit').show(); // Show the submit paragraph for other tabs
        }
    } else {
        $(activeTab).show(); // Show initially active tab if no stored tab found
    }

    // Form submission handling
    $('form').on('submit', function() {
        var formAction = $(this).attr('action');

        if (formAction === '' || formAction === 'options.php') {
            // Handle save settings form submission
            sessionStorage.setItem('active_tab', $('.nav-tab-active').attr('href'));
        } else {
            // Handle other form submissions (if any)
        }
    });
});
