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
    
    // Function to update image preview visibility
    function updateImagePreview($container) {
        var $input = $container.find('.kintaelectric-image-id');
        var $preview = $container.find('.kintaelectric-image-preview');
        var $removeBtn = $container.find('.kintaelectric-remove-image');
        var imageId = $input.val();
        
        if (imageId && imageId !== '' && imageId !== '0') {
            // Check if preview has content (image rendered by PHP)
            var previewContent = $preview.html().trim();
            if (previewContent !== '' || $preview.find('img').length > 0) {
                $preview.show();
                $removeBtn.show();
            } else {
                // If preview is empty but image ID exists, try to fetch and display it
                // This handles cases where the image wasn't rendered by PHP
                if (typeof wp !== 'undefined' && wp.media && wp.media.attachment) {
                    var attachment = wp.media.attachment(imageId);
                    attachment.fetch().done(function() {
                        var imageUrl = '';
                        if (attachment.attributes.sizes && attachment.attributes.sizes.thumbnail) {
                            imageUrl = attachment.attributes.sizes.thumbnail.url;
                        } else if (attachment.attributes.sizes && attachment.attributes.sizes.medium) {
                            imageUrl = attachment.attributes.sizes.medium.url;
                        } else {
                            imageUrl = attachment.attributes.url;
                        }
                        if (imageUrl) {
                            $preview.html('<img src="' + imageUrl + '" alt="" style="max-width: 150px; height: auto;">').show();
                            $removeBtn.show();
                        }
                    }).fail(function() {
                        // If fetch fails, at least show the preview container if it has any content
                        if ($preview.html().trim() !== '') {
                            $preview.show();
                            $removeBtn.show();
                        }
                    });
                }
            }
        } else {
            // No image ID, hide preview and remove button
            if ($preview.find('img').length === 0) {
                $preview.hide();
            }
            $removeBtn.hide();
        }
    }
    
    // Initialize image previews on page load
    function initializeImagePreviews() {
        $('.kintaelectric-image-widget-container').each(function() {
            updateImagePreview($(this));
        });
    }
    
    // Run on page load
    initializeImagePreviews();
    
    // Also run when widgets are updated (for customizer and widget save)
    $(document).on('widget-updated widget-added', function(e, widget) {
        if (widget) {
            $(widget).find('.kintaelectric-image-widget-container').each(function() {
                updateImagePreview($(this));
            });
        } else {
            initializeImagePreviews();
        }
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
        
        // If there's already an image selected, set it as the selected one
        var selectedImageId = $input.val();
        var frame = wp.media({
            title: 'Select Image',
            button: {
                text: 'Use this image'
            },
            multiple: false,
            library: {
                type: 'image'
            }
        });
        
        // If there's a selected image, set it as selected in the media frame
        if (selectedImageId) {
            frame.on('open', function() {
                var selection = frame.state().get('selection');
                var attachment = wp.media.attachment(selectedImageId);
                attachment.fetch();
                selection.add(attachment ? [attachment] : []);
            });
        }
        
        frame.on('select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            var imageUrl = '';
            
            // Get thumbnail URL, fallback to full size if thumbnail doesn't exist
            if (attachment.sizes && attachment.sizes.thumbnail) {
                imageUrl = attachment.sizes.thumbnail.url;
            } else if (attachment.sizes && attachment.sizes.medium) {
                imageUrl = attachment.sizes.medium.url;
            } else {
                imageUrl = attachment.url;
            }
            
            $input.val(attachment.id);
            $preview.html('<img src="' + imageUrl + '" alt="' + (attachment.alt || '') + '" style="max-width: 150px; height: auto;">').show();
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
