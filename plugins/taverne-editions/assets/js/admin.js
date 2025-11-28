/**
 * Taverne Editions Admin JavaScript
 * Width-based size calc + States/Impressions AJAX + SEO counter + Image Uploads
 */

jQuery(document).ready(function($) {
    
    // ============================================
    // PLATE DETAILS - Auto-calculate size from WIDTH
    // ============================================
    
    function updateSize() {
        const width = parseFloat($('#plate_width').val()) || 0;
        
        // Uncheck all radios first
        $('input[name="plate_size"]').prop('checked', false);
        
        // Determine size based on WIDTH only
        if (width > 0 && width < 38) {
            $('input[name="plate_size"][value="S"]').prop('checked', true);
        } else if (width >= 38 && width < 70) {
            $('input[name="plate_size"][value="M"]').prop('checked', true);
        } else if (width >= 70) {
            $('input[name="plate_size"][value="L"]').prop('checked', true);
        }
    }
    
    // Trigger on width change (height doesn't matter for size)
    $('#plate_width').on('input change', updateSize);
    updateSize();
    
    // ============================================
    // SEO - Character counter for meta description
    // ============================================
    
    $('#taverne_meta_description').on('input', function() {
        const length = $(this).val().length;
        $('#taverne-char-counter').text(length);
        
        // Color coding
        if (length > 160) {
            $('#taverne-char-counter').css('color', '#d63638'); // Red
        } else if (length > 140) {
            $('#taverne-char-counter').css('color', '#f0b849'); // Yellow
        } else {
            $('#taverne-char-counter').css('color', '#2271b1'); // Blue
        }
    });
    
    // ============================================
    // IMPRESSION IMAGE UPLOAD
    // ============================================
    
    $(document).on('click', '.taverne-upload-trigger', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const $trigger = $(this);
        const impressionId = $trigger.data('impression-id');
        
        if (!impressionId) {
            console.error('No impression ID found');
            return;
        }
        
        // Create new frame every time (no reuse issues)
        const frame = wp.media({
            title: 'Select Impression Image',
            button: { text: 'Use this image' },
            multiple: false,
            library: { type: 'image' }
        });
        
        // Handle image selection
        frame.on('select', function() {
            const attachment = frame.state().get('selection').first().toJSON();
            const imageUrl = attachment.sizes?.thumbnail?.url || attachment.url;
            
            // Update thumbnail immediately
            $trigger.html('<img src="' + imageUrl + '" style="width:100%;height:100%;object-fit:cover;">');
            
            // Save to backend
            $.post(taverneEditions.ajaxurl, {
                action: 'taverne_update_impression',
                nonce: taverneEditions.nonce,
                impression_id: impressionId,
                field: 'image_id',
                value: attachment.id
            }, function(response) {
                if (!response.success) {
                    console.error('Save failed:', response);
                    alert('Failed to save image: ' + (response.data || 'Unknown error'));
                }
            });
        });
        
        frame.open();
    });
    
    // ============================================
    // STATES & IMPRESSIONS
    // ============================================
    
    // Add new state
    $(document).on('click', '#taverne-add-state', function() {
        const $btn = $(this);
        $btn.prop('disabled', true).text('Adding...');
        
        $.ajax({
            url: taverneEditions.ajaxurl,
            type: 'POST',
            data: {
                action: 'taverne_add_state',
                nonce: taverneEditions.nonce,
                plate_id: taverneEditions.plate_id
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function() {
                alert('Network error. Please try again.');
            },
            complete: function() {
                $btn.prop('disabled', false).text('+ Add New State');
            }
        });
    });
    
    // Update state field
    $(document).on('blur change', '.taverne-state-title, .taverne-state-excerpt, .taverne-state-description, .taverne-featured-impression', function() {
        const $field = $(this);
        const stateId = $field.data('state-id');
        const value = $field.val();
        
        let fieldName = '';
        if ($field.hasClass('taverne-state-title')) fieldName = 'title';
        else if ($field.hasClass('taverne-state-excerpt')) fieldName = 'excerpt';
        else if ($field.hasClass('taverne-state-description')) fieldName = 'description';
        else if ($field.hasClass('taverne-featured-impression')) fieldName = 'featured_impression_id';
        
        $.ajax({
            url: taverneEditions.ajaxurl,
            type: 'POST',
            data: {
                action: 'taverne_update_state',
                nonce: taverneEditions.nonce,
                state_id: stateId,
                field: fieldName,
                value: value
            },
            success: function(response) {
                if (!response.success) {
                    alert('Error updating state: ' + response.data);
                }
            }
        });
    });
    
    // Delete state
    $(document).on('click', '.taverne-delete-state', function(e) {
        e.stopPropagation();
        
        if (!confirm('Delete this state and all its impressions? This cannot be undone.')) {
            return;
        }
        
        const stateId = $(this).data('state-id');
        const $accordion = $(this).closest('.taverne-accordion-item');
        
        $.ajax({
            url: taverneEditions.ajaxurl,
            type: 'POST',
            data: {
                action: 'taverne_delete_state',
                nonce: taverneEditions.nonce,
                state_id: stateId
            },
            success: function(response) {
                if (response.success) {
                    $accordion.fadeOut(300, function() {
                        $(this).remove();
                    });
                } else {
                    alert('Error deleting state: ' + response.data);
                }
            }
        });
    });
    
    // Add new impression
    $(document).on('click', '.taverne-add-impression', function() {
        const $btn = $(this);
        const stateId = $btn.data('state-id');
        $btn.prop('disabled', true).text('Adding...');
        
        $.ajax({
            url: taverneEditions.ajaxurl,
            type: 'POST',
            data: {
                action: 'taverne_add_impression',
                nonce: taverneEditions.nonce,
                state_id: stateId
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function() {
                alert('Network error. Please try again.');
            },
            complete: function() {
                $btn.prop('disabled', false).text('+ Add Impression');
            }
        });
    });
    
    // Update impression field
    $(document).on('blur change', '.taverne-impression-field', function() {
        const $field = $(this);
        const impressionId = $field.data('impression-id');
        const fieldName = $field.data('field');
        const value = $field.val();
        
        $.ajax({
            url: taverneEditions.ajaxurl,
            type: 'POST',
            data: {
                action: 'taverne_update_impression',
                nonce: taverneEditions.nonce,
                impression_id: impressionId,
                field: fieldName,
                value: value
            },
            success: function(response) {
                if (!response.success) {
                    alert('Error updating impression: ' + response.data);
                }
            }
        });
    });
    
    // Traffic light status selector
    $(document).on('click', '.taverne-status-light', function() {
        const $light = $(this);
        const impressionId = $light.data('impression-id');
        const status = $light.data('status');
        
        // Update visual immediately
        $light.siblings('.taverne-status-light').removeClass('active');
        $light.addClass('active');
        
        // Save to backend
        $.ajax({
            url: taverneEditions.ajaxurl,
            type: 'POST',
            data: {
                action: 'taverne_update_impression',
                nonce: taverneEditions.nonce,
                impression_id: impressionId,
                field: 'availability',
                value: status
            },
            success: function(response) {
                if (!response.success) {
                    alert('Error updating availability: ' + response.data);
                }
            }
        });
    });
    
    // Delete impression
    $(document).on('click', '.taverne-delete-impression', function() {
        if (!confirm('Delete this impression? This cannot be undone.')) {
            return;
        }
        
        const impressionId = $(this).data('impression-id');
        const $row = $(this).closest('.taverne-impression-row');
        
        $.ajax({
            url: taverneEditions.ajaxurl,
            type: 'POST',
            data: {
                action: 'taverne_delete_impression',
                nonce: taverneEditions.nonce,
                impression_id: impressionId
            },
            success: function(response) {
                if (response.success) {
                    $row.fadeOut(300, function() {
                        $(this).remove();
                    });
                } else {
                    alert('Error deleting impression: ' + response.data);
                }
            }
        });
    });
    
});

// ============================================
// GLOBAL FUNCTIONS
// ============================================

function taverneToggleAccordion(index) {
    const items = document.querySelectorAll('.taverne-accordion-item');
    const item = items[index];
    const header = item.querySelector('.taverne-accordion-header');
    const content = item.querySelector('.taverne-accordion-content');
    
    header.classList.toggle('active');
    content.classList.toggle('active');
}
