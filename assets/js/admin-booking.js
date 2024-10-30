(function( $ ) {
    $(function() {
        // Define booking class
        function BookingService() {
            const self = this;
            const $errorElm = $("#msg_error");
            const $priceElm = $("#listar-estimate-price");
            const $resourceElm = $("#resource_id");

            this.resource = {
                id: null,
                type: '',
                price: '',
                start_date: '',
                end_date: '',
                select_options: []
            }

            /**
             * Set data props
             * @param data
             */
            this.setResource = function(data) {
                $.extend(this.resource, data);
            }

            /**
             * return data type
             * @returns {*|{end_date: string, select_options: *[], price: string, type: string, start_date: string}}
             */
            this.getResource = function() {
                return this.resource;
            }

            /**
             * Call when change person, adult, children
             */
            this.estimatePrice = function () {
                // Reset message
                self.resetMsg();

                if(this.resource.type === '') {
                    self.showError('Please select listing for booking');
                } else {
                   var start_time = '';
                   var end_time = '';

                   if(this.resource.type === 'hourly') {
                       var time_arr = $("#time_slot").val().split("|");
                       start_time = time_arr[0];
                       end_time = time_arr[1];
                       $("#start_time").val(start_time);
                       $("#end_time").val(end_time);
                   } else {
                       start_time = $("#start_time").val();
                       end_time = $("#end_time").val();
                   }

                   $.ajax({
                       url: booking_vars.cart_url,
                       type: "POST",
                       dataType: "json",
                       data: {
                           booking_style: self.resource.type,
                           resource_id: self.resource.id,
                           adult: $("#adult").val(),
                           children: $("#children").val(),
                           start_date: $("#start_date").val(),
                           end_date: $("#end_date").val(),
                           start_time: start_time,
                           end_time: end_time
                       },
                       success: function(result) {
                           if(result.hasOwnProperty('success') && result.success === true) {
                               $("#listar-estimate-price").html(result.attr.total_display);
                           } else if(result.hasOwnProperty('code')) {
                               self.showError(result.message);
                           } else {
                               self.showError(result.msg);
                           }
                       },
                       error: function (result) {
                           self.showError(result.msg);
                       }
                   });
                }
            }

            this.supportFieldCheck = function () {
                switch (this.resource.type) {
                    case 'standard':
                        $("#booking-start-date").removeClass('hidden');
                        $("#booking-start-time").removeClass('hidden');
                        $("#booking-end-date").addClass('hidden');
                        $("#booking-slot").addClass('hidden');
                        break;
                    case 'daily':
                        $("#booking-start-date").removeClass('hidden');
                        $("#booking-start-time").removeClass('hidden');
                        $("#booking-end-date").removeClass('hidden');
                        $("#booking-slot").addClass('hidden');
                        break;
                    case 'hourly':
                        $("#booking-start-date").removeClass('hidden');
                        $("#booking-slot").removeClass('hidden');
                        $("#booking-start-time").addClass('hidden');
                        $("#booking-end-date").addClass('hidden');
                        break;
                    case 'slot':
                        $("#booking-start-date").addClass('hidden');
                        $("#booking-end-date").addClass('hidden');
                        $("#booking-slot").addClass('hidden');
                        break;
                }
            }

            this.showError = function (msg) {
                this.resetMsg();
                $errorElm.html(msg);
            }

            this.resetMsg = function() {
                $errorElm.html('');
                $priceElm.html('');
            }

        }

        // ================= Booking Service
        const Booking = new BookingService();

        // ================= On change event
        $("#start_date, #end_date, #start_time, #end_time, #adult, #children, #time_slot").change(function(){
            Booking.estimatePrice();
        });

        // Date Picker
        $(".booking-date-picker").datepicker({
            dateFormat: 'yy-mm-dd'
        });

        // First time set resource data
        if($("#resource_id").val() !== '') {
            $.ajax({
                url: booking_vars.form_url,
                dataType: "json",
                data: {
                    resource_id: $("#resource_id").val()
                },
                success: function (result) {
                    if (result.hasOwnProperty('success') && result.success === true) {
                        Booking.setResource(result.data);
                    } else if (result.hasOwnProperty('code')) {
                        Booking.showError(result.message);
                    }
                },
                error: function (response) {
                    console.log(response);
                }
            });
        }

        // Auto complete listing
        $(".auto-complete-listing").autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: booking_vars.listing_url,
                    dataType: "json",
                    data: {
                        s: request.term
                    },
                    success: function(result) {
                        var transformed = [];
                        var label = '';
                        if(result.hasOwnProperty('success') && result.success === true) {
                            transformed = result.data.map(function(post) {
                                if(post.booking_price) {
                                    label = post.post_title+' - '+post.booking_price;
                                } else {
                                    label = post.post_title;
                                }
                                return {
                                    label: label, // default value (display when search)
                                    value: post.ID, // default value,
                                    field: post.post_title, // display value
                                };
                            });
                        }

                        response(transformed);
                    },
                    error: function () {
                        response([]);
                    }
                });
            },
            select: function( event, ui ) {
                // Reset message
                Booking.resetMsg();

                // Apply value in select box
                $(this).val(ui.item.field);

                // Set resource id
                $("#resource_id").val(ui.item.value);

                // Render form
                $.ajax({
                    url: booking_vars.form_url,
                    dataType: "json",
                    data: {
                        resource_id: ui.item.value
                    },
                    success: function(result) {
                        if(result.hasOwnProperty('success') && result.success === true) {
                            Booking.setResource(result.data);
                            Booking.supportFieldCheck();
                        } else if(result.hasOwnProperty('code')) {
                            Booking.showError(result.message);
                        }
                    },
                    error: function (response) {
                        $("#resource_id").val('');
                        console.log(response);
                    }
                });

                // make sure label can be applied
                return false;
            }
        });

        // Auto complete user
        $(".auto-complete-user").autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: booking_vars.user_url,
                    dataType: "json",
                    data: {
                        search: request.term
                    },
                    success: function(result) {
                        var transformed = [];
                        if(result) {
                            transformed = result.map(function(post) {
                                return {
                                    label: post.name+' - '+post.user_email, // default value
                                    value: post.id, // default value
                                    user_email: post.user_email, // default value
                                };
                            });
                        }

                        response(transformed);
                    },
                    error: function () {
                        response([]);
                    }
                });
            },
            select: function( event, ui ) {
                $(this).val(ui.item.user_email); // set input field
                $("#user_id").val(ui.item.value); // set hidden field

                // make sure label can be applied
                return false;
            }
        });
    });
})(jQuery);