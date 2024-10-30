(function ($) {
    // Add Color Picker to all inputs that have 'color-field' class
    $(function () {
        // Date picker
        if (typeof jQuery.fn.datepicker !== "undefined") {
            $(".listar-datepicker").datepicker({
                dateFormat: "yy-mm-dd"
            });
        }

        $('.color-field').wpColorPicker({
            palettes: listar_vars.option.color_option
        });

        $('.iconpicker').iconpicker({
            title: false,
        });

        $('.iconpicker').on('iconpickerSelected', function (event) {
            $(event.target).prev().find("i").attr("class", event.iconpickerValue);
        });

        /**
         * Image Gallery Upload
         */
        $(".listar-trigger-gallery").on("click", function (event) {
            var gallery_id = $(event.target).parent().find(".listar-gallery-ids");
            var gallery_screenshot = $(event.target).parent().find(".listar-gallery-screenshot");

            event.preventDefault();
            // If the media frame already exists, reopen it.
            if (typeof frame !== 'undefined') {
                frame.open();
                return;
            }
            // Create a new media frame
            frame = wp.media({
                title: 'Select Gallery Images',
                button: {
                    text: 'Use this media'
                },
                multiple: true  // Set to true to allow multiple files to be selected
            });

            // When an image is selected in the media frame...
            frame.on('select', function () {
                // Reset value
                gallery_screenshot.html('');
                // Assign again
                var element, preview_html = '', preview_img;
                var ids = frame.state().get('selection').models.map(
                    function (e) {
                        element = e.toJSON();
                        preview_img = typeof element.sizes.thumbnail !== 'undefined' ? element.sizes.thumbnail.url : element.url;
                        preview_html = "<div class='screen-thumb'><img src='" + preview_img + "'/></div>";
                        gallery_screenshot.append(preview_html);
                        return e.id;
                    }
                );
                gallery_id.val(ids.join(',')).trigger('change');
            });

            // Set selected attachment file when re-open
            frame.on('open', function () {
                var selection = frame.state().get('selection');
                var gallery = $('#gallery').val();

                if (gallery.length > 0) {
                    var ids = gallery.split(',');

                    ids.forEach(function (id) {
                        attachment = wp.media.attachment(id);
                        attachment.fetch();
                        selection.add(attachment ? [attachment] : []);
                    });
                }
            });

            // Finally, open the modal on click
            frame.open();
        });

        // Reset Gallery
        $(".listar-reset-gallery").on("click", function (event) {
            event.preventDefault();
            var gallery_id = $(event.target).parent().find(".listar-gallery-ids");
            var gallery_screenshot = $(event.target).parent().find(".listar-gallery-screenshot");

            // Clear html content
            gallery_screenshot.html('');

            // Clear hidden input value
            gallery_id.val("").trigger('change');
        });

        // Feature Image upload
        $(".listar-trigger-image").on("click", function (event) {
            var image_wrapper =  $(event.target).closest('.form-field').find('.listar-featured-image-wrapper');
            var image_value =  $(event.target).closest('.form-field').find('.listar-featured-image');

            event.preventDefault();
            // If the media frame already exists, reopen it.
            if (typeof frame !== 'undefined') {
                frame.open();
                return;
            }

            // Create a new media frame
            frame = wp.media({
                title: 'Select Featured Image',
                button: {
                    text: 'Set featured image'
                },
                multiple: false
            });

            // When an image is selected in the media frame...
            frame.on('select', function () {
                // Reset before set image
                image_wrapper.html('');

                var element, preview_html = '', preview_img, attachments;
                attachments = frame.state().get('selection').toJSON();
                element = attachments[0];
                preview_img = typeof element.sizes.thumbnail !== 'undefined' ? element.sizes.thumbnail.url : element.url;
                preview_html = "<div class='screen-thumb'><img src='" + preview_img + "'/></div>";

                // Render thumbnail image
                image_wrapper.append(preview_html);

                // Set attachment id
                image_value.val(element.id).trigger('change');
            });

            // Set selected attachment file when re-open
            frame.on('open', function () {
                var selection = frame.state().get('selection');
                var file_id = $('#featured-image-id').val();

                if (file_id) {
                    attachment = wp.media.attachment(file_id);
                    attachment.fetch();
                    selection.add(attachment ? [attachment] : []);
                }
            });

            // Finally, open the modal on click
            frame.open();
        });

        // Reset Featured Image
        $(".listar-trigger-image-reset").on("click", function (event) {
            var image_wrapper =  $(event.target).closest('.form-field').find('.listar-featured-image-wrapper');
            var image_value =  $(event.target).closest('.form-field').find('.listar-featured-image');

            event.preventDefault();

            // Clear html content
            image_wrapper.html('');

            // Clear hidden input value
            image_value.val("").trigger('change');
        });


        /**
         * File attachments Upload
         * @since 1.0.13
         */
        $(".listar-trigger-attachment").on("click", function (event) {
            var attachment_id = $(event.target).parent().find(".listar-attachment-ids");
            var attachment_list = $(event.target).parent().find(".listar-attachment-list");

            event.preventDefault();
            // If the media frame already exists, reopen it.
            if (typeof frame !== 'undefined') {
                frame.open();
                return;
            }
            // Create a new media frame
            frame = wp.media({
                title: 'Select File Attachments',
                button: {
                    text: 'Use this media'
                },
                multiple: true  // Set to true to allow multiple files to be selected
            });

            // When an file is selected in the media frame...
            frame.on('select', function () {
                // Reset value
                attachment_list.html('');
                // Assign again
                var element;
                var elm_html = '';
                var ids = frame.state().get('selection').models.map(
                    function (e) {
                        element = e.toJSON();
                        console.log(element);
                        elm_html = "<div class='file-attachment'>" +
                            "<span class='file-name'><a target='_blank' href='"+element.url+"'>"+element.filename+"</a></span>"+
                            "<span class='file-size'>"+element.filesizeHumanReadable+"</span>"+
                        "</div>";
                        attachment_list.append(elm_html);
                        return e.id;
                    }
                );
                attachment_id.val(ids.join(',')).trigger('change');
            });

            // Set selected attachment file when re-open
            frame.on('open', function () {
                var selection = frame.state().get('selection');
                var attachment = $('#attachment').val();

                if (attachment.length > 0) {
                    var ids = attachment.split(',');
                    ids.forEach(function (id) {
                        attachment = wp.media.attachment(id);
                        attachment.fetch();
                        selection.add(attachment ? [attachment] : []);
                    });
                }
            });

            // Finally, open the modal on click
            frame.open();
        });

        // Reset File Attachment
        $(".listar-reset-attachment").on("click", function (event) {
            event.preventDefault();
            var attachment_id = $(event.target).parent().find(".listar-attachment-ids");
            var attachment_list = $(event.target).parent().find(".listar-attachment-list");

            // Clear html content
            attachment_list.html('');

            // Clear hidden input value
            attachment_id.val("").trigger('change');
        });

        // Reset form when ajax finish called
        $(document).ajaxComplete(function (event, xhr, settings) {
            if (settings.hasOwnProperty('data')) {
                var queryStringArr = settings.data.split('&');
                if ($.inArray('action=add-tag', queryStringArr) !== -1) {
                    var xml = xhr.responseXML;
                    $response = $(xml).find('term_id').text();
                    if ($response != "") {
                        // Clear html content
                        $('.listar-featured-image-wrapper').html('');

                        // Clear hidden input value
                        $('.listar-featured-image').val("").trigger('change');

                        // Icon picker reset
                        $('.iconpicker').val("");

                        // Color Picker reset
                        $('input.wp-picker-clear').trigger('click');

                    }
                }
            }
        });

        // Add sortable
        $(".listar-add-sortable").on("click", function(event) {
            var table = $(event.target).closest('table');
            var elmClone = table.find('tr.elm-sortable-row').first().clone(true);
            var index = Math.random();

            elmClone.removeClass("hidden");
            elmClone.find('input').each(function() {
                $(this).attr("name", $(this).data('id')+'['+index+']'+'['+$(this).data('name')+']');
            });
            elmClone.appendTo(table.find('tbody'));
        });

        // Del sortable
        $(".listar-del-sortable").on("click", function(event) {
            var table = $(event.target).closest('table');
            table.find('input[class=listar-col-checkbox]:checked').each(function() {
                $(this).closest('tr').remove();
            });
        });
    });
})(jQuery);
