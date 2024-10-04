/**
 * apply an input mask to an ABN input field via jQuery.
 *
 * ::  formats the input as 999 999 999 99, which is a common format for ABNs in Australia.
 * ::  selector - (input[name="site_owner_abn"]) to target your specific ABN input field.
 *

isnt saving so removed the mask for now. and made the field same as text field. 

jQuery(document).ready(function($) {
    // Apply input mask to the ABN field
    var abnSelector = 'input[name="site_owner_abn"]'; // Adjust the selector if necessary
    $(abnSelector).inputmask({
        mask: "999 999 999 99",
        placeholder: "___ ___ ___ __",
        showMaskOnHover: true,
        clearIncomplete: true,
        onincomplete: function() {
            alert("Please complete the ABN field.");
        }
    });
});  */
