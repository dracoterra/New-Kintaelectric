jQuery(document).ready(function($) {
    'use strict';
    
    // Function to toggle category field
    function toggleCategoryField() {
        $('.kintaelectric-product-type').each(function() {
            var $this = $(this);
            var $categoryField = $this.closest('.widget-content').find('.kintaelectric-category-field');
            
            if ($this.val() === 'category') {
                $categoryField.show();
            } else {
                $categoryField.hide();
            }
        });
    }
    
    // Run on page load
    toggleCategoryField();
    
    // Run when product type changes
    $(document).on('change', '.kintaelectric-product-type', function() {
        toggleCategoryField();
    });
    
    // Image widget functionality
    $(document).on('click', '.kintaelectric-select-image', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var $container = $button.closest('.kintaelectric-image-widget-container');
        var $input = $container.find('.kintaelectric-image-id');
        var $preview = $container.find('.kintaelectric-image-preview');
        var $removeBtn = $container.find('.kintaelectric-remove-image');
        
        // Check if wp.media is available
        if (typeof wp === 'undefined' || !wp.media) {
            alert('Media library not available. Please refresh the page.');
            return;
        }
        
        var frame = wp.media({
            title: 'Select Image',
            button: {
                text: 'Use this image'
            },
            multiple: false
        });
        
        frame.on('select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            $input.val(attachment.id);
            $preview.html('<img src="' + attachment.sizes.thumbnail.url + '" alt="' + attachment.alt + '">').show();
            $removeBtn.show();
        });
        
        frame.open();
    });
    
    $(document).on('click', '.kintaelectric-remove-image', function(e) {
        e.preventDefault();
        
        var $container = $(this).closest('.kintaelectric-image-widget-container');
        var $input = $container.find('.kintaelectric-image-id');
        var $preview = $container.find('.kintaelectric-image-preview');
        var $removeBtn = $(this);
        
        $input.val('');
        $preview.empty().hide();
        $removeBtn.hide();
    });
});
