jQuery(document).ready(function ($) {
    $('#add-row').on('click', function () {
        var currentLength = $('#repeatable-tbody')[0].children.length;
        var row = $('.empty-row.screen-reader-text').clone(true).removeClass('empty-row screen-reader-text');
        row.insertBefore($('#repeatable-tbody>tr:last'));
        if (currentLength == 2) {
            $('#repeatable-tbody>tr:first>td:last').append(
                '<a class="button remove-row" href="#">Ta bort</a>',
            );
        }
        return false;
    });

    $('#repeatable-tbody').on('click', '.remove-row', function () {
        $(this).parent().parent().remove();
        var currentLength = $('#repeatable-tbody')[0].children.length;
        if (currentLength == 2) {
            $('#repeatable-tbody>tr:first').find('.remove-row').remove();
        }
        return false;
    });

    $('.enabled_pages_wrapper').on('click', 'input[type="checkbox"]', function () {
        var currentPagesEl = $('#enabled_pages');
        var enabledPages = [];
        var newValue = $(this).val();

        if ($(currentPagesEl).val() === '') {
            enabledPages.push(newValue);
        } else {
            enabledPages = $(currentPagesEl).val().split(',');
            enabledPages.indexOf(newValue) === -1
                ? enabledPages.push(newValue)
                : enabledPages.splice(enabledPages.indexOf(newValue), 1);
        }

        $(currentPagesEl).attr('value', enabledPages.join(','));
    });

    // Hook suggestion handling (moved from psf-settings-markup.php)
    $('.psf-hook-suggestion').on('click', function () {
        var hookName = $(this).data('hook');
        $('#woo_visual_hook').val(hookName);
        $('.psf-hook-suggestion').removeClass('button-primary').addClass('button-secondary');
        $(this).removeClass('button-secondary').addClass('button-primary');
    });

    // Highlight currently selected hook
    var currentHook = $('#woo_visual_hook').val();
    if (currentHook) {
        $('.psf-hook-suggestion[data-hook="' + currentHook + '"]')
            .removeClass('button-secondary')
            .addClass('button-primary');
    }

    // Load detected hooks from localStorage
    function loadDetectedHooks() {
        var detectedHooks = localStorage.getItem('psf_detected_hooks');
        if (detectedHooks) {
            try {
                var hooks = JSON.parse(detectedHooks);
                var hooksList = $('#psf_hooks_list');
                if (hooks.length > 0) {
                    var html = '<strong>Found ' + hooks.length + ' hooks:</strong><br>';
                    hooks.forEach(function (hook) {
                        html +=
                            '<button type="button" class="button-link psf-detected-hook" data-hook="' +
                            hook +
                            '" style="display: block; text-align: left; margin: 2px 0; color: #0073aa;">' +
                            hook +
                            '</button>';
                    });
                    hooksList.html(html);

                    // Handle clicks on detected hooks
                    $('.psf-detected-hook').on('click', function () {
                        var hookName = $(this).data('hook');
                        $('#woo_visual_hook').val(hookName);
                        $('.psf-hook-suggestion').removeClass('button-primary').addClass('button-secondary');
                    });
                }
            } catch (e) {
                console.log('Error parsing detected hooks:', e);
            }
        }
    }

    // Refresh hooks detection
    $('#psf_refresh_hooks').on('click', function () {
        localStorage.removeItem('psf_detected_hooks');
        $('#psf_hooks_list').html(
            '<em style="color: #666;">Visit a product category page to detect available hooks...</em>',
        );
        alert('Hook detection cleared. Visit a product category page to detect hooks again.');
    });

    // Load detected hooks on page load
    loadDetectedHooks();

    // Handle hook testing
    $('#psf_test_hook_btn').on('click', function () {
        var testHook = $('#psf_test_hook').val().trim();
        var resultDiv = $('#psf_test_result');

        if (!testHook) {
            resultDiv.html('<span style="color: #d63638;">Please enter a hook name to test.</span>');
            return;
        }

        resultDiv.html('<span style="color: #0073aa;">Testing hook: <code>' + testHook + '</code>...</span>');

        // Show instructions
        setTimeout(function () {
            var instructions =
                '<div style="background: #fff3cd; padding: 10px; border: 1px solid #ffc107; border-radius: 3px; margin-top: 5px;">';
            instructions += '<strong>Test Instructions:</strong><br>';
            instructions += '1. Save your settings with the hook "<code>' + testHook + '</code>"<br>';
            instructions += '2. Visit a product category page (e.g., /perfume/men/)<br>';
            instructions += '3. Look for the FAQ section on the page<br>';
            instructions += '4. Return here to try a different hook if needed';
            instructions += '</div>';
            resultDiv.html(instructions);

            // Auto-fill the hook input
            $('#woo_visual_hook').val(testHook);
            $('.psf-hook-suggestion').removeClass('button-primary').addClass('button-secondary');
        }, 1000);
    });

    // Allow enter key in test input
    $('#psf_test_hook').on('keypress', function (e) {
        if (e.which === 13) {
            $('#psf_test_hook_btn').click();
        }
    });

    // Auto-fill test input when hook suggestions are clicked
    $('.psf-hook-suggestion').on('click', function () {
        var hookName = $(this).data('hook');
        $('#psf_test_hook').val(hookName);
    });

    // Show helpful status message
    var statusHtml =
        '<div style="background: #d1ecf1; padding: 10px; border: 1px solid #bee5eb; border-radius: 3px; margin-top: 15px;">';
    statusHtml += '<strong>💡 Tips:</strong><br>';
    statusHtml += '• Click any hook button above to quickly select it<br>';
    statusHtml += '• Visit <code>/perfume/men/</code> after saving to see FAQ placement<br>';
    statusHtml += '• Use "Refresh Hook Detection" if you visit new category pages';
    statusHtml += '</div>';
    $('#psf_hook_discovery').append(statusHtml);

    // Function to toggle hook position visibility (moved from psf-settings-markup.php)
    function toggleHookPosition() {
        var hookRenderingEnabled = $('input[name="hook_rendering_enabled"]:checked').val();
        var hookPositionRow = $('#hook_position_setting');
        var nextRows = hookPositionRow.nextUntil(
            'tr:has(th:contains("Shortcode Usage"), th:contains("Debug"))',
        );

        if (hookRenderingEnabled === 'yes') {
            hookPositionRow.show();
            nextRows.show();
        } else {
            hookPositionRow.hide();
            nextRows.hide();
        }
    }

    // Initial toggle
    toggleHookPosition();

    // Toggle when radio buttons change
    $('input[name="hook_rendering_enabled"]').change(function () {
        toggleHookPosition();
    });
});
