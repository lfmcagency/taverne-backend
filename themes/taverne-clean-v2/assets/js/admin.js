/**
 * Taverne Clean Theme - admin.js
 * 
 * Admin interactions for Taverne Editions: AJAX CRUD, image uploads via wp.media,
 * drag-sort impressions with jQuery UI sortable, status lights, debounced field saves
 * 
 * @package Taverne_Clean
 * @version 2.0
 */

(function($) {
    'use strict';

    // ==========================================================================
    // STATE
    // ==========================================================================

    let saveTimeout;
    const DEBOUNCE_DELAY = 500;

    // ==========================================================================
    // INIT
    // ==========================================================================

    $(document).ready(function() {
        initSortable();
        initImageUploads();
        initStatusLights();
        initFieldSaves();
        initAddDeleteHandlers();
    });

    // ==========================================================================
    // DRAG-SORT IMPRESSIONS
    // ==========================================================================

    function initSortable() {
        const $sortableList = $('.taverne-impression-list, .taverne-states-list');
        
        if (!$sortableList.length || typeof $.fn.sortable !== 'function') return;

        $sortableList.sortable({
            items: '.taverne-impression-card, .taverne-state-card',
            handle: '.drag-handle',
            placeholder: 'sortable-placeholder',
            tolerance: 'pointer',
            cursor: 'grabbing',
            update: function(event, ui) {
                const $list = $(this);
                const isImpressions = $list.hasClass('taverne-impression-list');
                const order = $list.sortable('toArray', { 
                    attribute: isImpressions ? 'data-imp-id' : 'data-state-id' 
                });
                
                const action = isImpressions 
                    ? 'taverne_update_impression_order' 
                    : 'taverne_update_state_order';

                $.post(window.taverneAdmin?.ajax_url || ajaxurl, {
                    action: action,
                    order: order,
                    plate_id: $('#post_ID').val(),
                    nonce: window.taverneAdmin?.nonce || ''
                }).done(function(res) {
                    if (res.success) {
                        showNotice('Order saved!', 'success');
                    }
                }).fail(function() {
                    showNotice('Failed to save order', 'error');
                });
            }
        });
    }

    // ==========================================================================
    // IMAGE UPLOADS (WP MEDIA)
    // ==========================================================================

    function initImageUploads() {
        $(document).on('click', '.taverne-upload-trigger', function(e) {
            e.preventDefault();
            
            const $btn = $(this);
            const $preview = $btn.siblings('.taverne-upload-preview');
            const $input = $btn.siblings('input[type="hidden"]');
            const impId = $btn.data('imp-id');
            const stateId = $btn.data('state-id');

            const frame = wp.media({
                title: 'Select Image',
                button: { text: 'Use this image' },
                multiple: false,
                library: { type: 'image' }
            });

            frame.on('select', function() {
                const attachment = frame.state().get('selection').first().toJSON();
                
                // Update preview
                if ($preview.length) {
                    if ($preview.is('img')) {
                        $preview.attr('src', attachment.url).show();
                    } else {
                        $preview.html(`<img src="${attachment.url}" alt="">`);
                    }
                }
                
                // Update hidden input
                $input.val(attachment.id);

                // Save via AJAX if this is an existing impression/state
                if (impId) {
                    saveField('taverne_update_impression', {
                        imp_id: impId,
                        image_id: attachment.id
                    });
                } else if (stateId) {
                    saveField('taverne_update_state', {
                        state_id: stateId,
                        image_id: attachment.id
                    });
                }
            });

            frame.open();
        });

        // Remove image button
        $(document).on('click', '.taverne-remove-image', function(e) {
            e.preventDefault();
            const $btn = $(this);
            const $preview = $btn.siblings('.taverne-upload-preview');
            const $input = $btn.siblings('input[type="hidden"]');
            
            $preview.attr('src', '').hide();
            $input.val('');
        });
    }

    // ==========================================================================
    // STATUS LIGHTS (Available / Artist / Sold)
    // ==========================================================================

    function initStatusLights() {
        $(document).on('click', '.taverne-status-light', function() {
            const $light = $(this);
            const impId = $light.data('imp-id');
            const currentStatus = $light.data('status') || 'available';
            
            // Cycle: available -> artist -> sold -> available
            const statusCycle = ['available', 'artist', 'sold'];
            const currentIndex = statusCycle.indexOf(currentStatus);
            const newStatus = statusCycle[(currentIndex + 1) % 3];

            // Update UI immediately
            $light
                .removeClass('available artist sold')
                .addClass(newStatus)
                .data('status', newStatus)
                .attr('title', newStatus.charAt(0).toUpperCase() + newStatus.slice(1));

            // Debounced save
            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(function() {
                saveField('taverne_update_impression', {
                    imp_id: impId,
                    availability: newStatus
                }).done(function() {
                    $light.addClass('pulse');
                    setTimeout(() => $light.removeClass('pulse'), 500);
                });
            }, DEBOUNCE_DELAY);
        });
    }

    // ==========================================================================
    // FIELD SAVES (Debounced)
    // ==========================================================================

    function initFieldSaves() {
        $(document).on('blur change', '.taverne-impression-field, .taverne-state-field', function() {
            const $field = $(this);
            const fieldName = $field.attr('name');
            const fieldValue = $field.val();
            const impId = $field.data('imp-id');
            const stateId = $field.data('state-id');

            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(function() {
                if (impId) {
                    saveField('taverne_update_impression', {
                        imp_id: impId,
                        [fieldName]: fieldValue
                    }).done(function() {
                        flashUpdated($field);
                    });
                } else if (stateId) {
                    saveField('taverne_update_state', {
                        state_id: stateId,
                        [fieldName]: fieldValue
                    }).done(function() {
                        flashUpdated($field);
                    });
                }
            }, DEBOUNCE_DELAY);
        });
    }

    // ==========================================================================
    // ADD / DELETE HANDLERS
    // ==========================================================================

    function initAddDeleteHandlers() {
        // Add State
        $(document).on('click', '.taverne-add-state', function() {
            const $btn = $(this);
            const plateId = $('#post_ID').val();

            $btn.prop('disabled', true).text('Adding...');

            $.post(window.taverneAdmin?.ajax_url || ajaxurl, {
                action: 'taverne_add_state',
                plate_id: plateId,
                nonce: window.taverneAdmin?.nonce || ''
            }).done(function(res) {
                if (res.success && res.data.html) {
                    $('.taverne-states-list').append(res.data.html);
                    initSortable(); // Rebind sortable
                    showNotice('State added!', 'success');
                }
            }).always(function() {
                $btn.prop('disabled', false).text('+ Add State');
            });
        });

        // Add Impression
        $(document).on('click', '.taverne-add-impression', function() {
            const $btn = $(this);
            const stateId = $btn.data('state-id');

            $btn.prop('disabled', true);

            $.post(window.taverneAdmin?.ajax_url || ajaxurl, {
                action: 'taverne_add_impression',
                state_id: stateId,
                nonce: window.taverneAdmin?.nonce || ''
            }).done(function(res) {
                if (res.success && res.data.html) {
                    $btn.closest('.taverne-state-card')
                        .find('.taverne-impression-list')
                        .append(res.data.html);
                    initSortable();
                    showNotice('Impression added!', 'success');
                }
            }).always(function() {
                $btn.prop('disabled', false);
            });
        });

        // Delete State / Impression
        $(document).on('click', '.taverne-delete-btn', function(e) {
            e.preventDefault();
            
            const $btn = $(this);
            const isState = $btn.hasClass('state-delete');
            const id = isState ? $btn.data('state-id') : $btn.data('imp-id');
            const action = isState ? 'taverne_delete_state' : 'taverne_delete_impression';
            const confirmMsg = isState 
                ? 'Delete this state and all its impressions?' 
                : 'Delete this impression?';

            if (!confirm(confirmMsg)) return;

            $.post(window.taverneAdmin?.ajax_url || ajaxurl, {
                action: action,
                id: id,
                nonce: window.taverneAdmin?.nonce || ''
            }).done(function(res) {
                if (res.success) {
                    $btn.closest(isState ? '.taverne-state-card' : '.taverne-impression-card')
                        .fadeOut(300, function() { $(this).remove(); });
                    showNotice('Deleted!', 'success');
                }
            });
        });
    }

    // ==========================================================================
    // HELPERS
    // ==========================================================================

    function saveField(action, data) {
        data.action = action;
        data.nonce = window.taverneAdmin?.nonce || '';
        return $.post(window.taverneAdmin?.ajax_url || ajaxurl, data);
    }

    function showNotice(message, type) {
        const $notice = $(`<div class="notice notice-${type} is-dismissible"><p>${message}</p></div>`);
        $('.taverne-meta-box').first().before($notice);
        setTimeout(() => $notice.fadeOut(300, function() { $(this).remove(); }), 3000);
    }

    function flashUpdated($el) {
        $el.addClass('field-updated');
        setTimeout(() => $el.removeClass('field-updated'), 1000);
    }

})(jQuery);
