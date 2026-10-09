
jQuery(document).ready(function ($) {

    $('.wp-ai-assistant form').on('submit', function () {

        const $form = $(this);
        const $button = $form.find('button[type="submit"]');

        // Prevent repeated submissions
        if ($button.prop('disabled')) {
            return false;
        }

        // Show loading state
        $button
            .prop('disabled', true)
            .text('Processing...');

    });

});