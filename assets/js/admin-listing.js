(function($) {
    $(function() {
        $('#listar-country').on("change", function($e) {
            $.ajax({
                type: "POST",
                url: listar_vars.admin_ajax,
                data: ({
                    action: 'get_stage_list',
                    parent_id: $('#listar-country').val()
                }),
                beforeSend: function() {
                    $('#listar-state').find("option:gt(0)").remove();
                    $('#listar-city').find("option:gt(0)").remove();
                },
                success: function(response) {
                    if (response && response.success === true) {
                        $.each(response.data, function(i, item) {
                            $('#listar-state').append($('<option>', {
                                value: item.term_id,
                                text: item.name
                            }));
                        });
                    }
                },
                error: function(xhr) {

                },
                complete: function() {

                }
            });
        });

        $('#listar-state').on("change", function($e) {
            $.ajax({
                type: "POST",
                url: listar_vars.admin_ajax,
                data: ({
                    action: 'get_stage_list',
                    parent_id: $('#listar-state').val()
                }),
                beforeSend: function() {
                    $('#listar-city').find("option:gt(0)").remove();
                },
                success: function(response) {
                    if (response && response.success === true) {
                        $.each(response.data, function(i, item) {
                            $('#listar-city').append($('<option>', {
                                value: item.term_id,
                                text: item.name
                            }));
                        });
                    }
                },
                error: function(xhr) {

                },
                complete: function() {

                }
            });
        });

        // Delete opening hour
        $(".listar-del-opening-hour").on("click", function(event) {
            $(event.target).parent("div").remove();
        });

        // Add opening hour
        $(".listar-add-opening-hour").on("click", function(event) {
            var elm = $(event.target).parent("div");
            var elmClone = $("div.div-opening-hour").clone(true);
            var day = $(event.target).data('day-of-week');

            elmClone.removeClass("hidden");
            elmClone.removeClass("div-opening-hour");
            elmClone.find("select.start-time").attr("name", "opening_hour[" + day + "][start][]");
            elmClone.find("select.end-time").attr("name", "opening_hour[" + day + "][end][]");
            elmClone.appendTo(elm.parent());
        });

        // Load the map when the page has finished loading.     
        // Check optional   
        if (listar_vars.option.map_use) {
            ListarAdminMap = new ListarAdminMap('map');
            google.maps.event.addDomListener(window, 'load', ListarAdminMap.init);
        }
    });
})(jQuery);

/**
 * @description class map only for admin page
 * @author Passion UI <passionui.com>
 * @date 2019-11-28
 * @param {*} mapId
 */
function ListarAdminMap(mapId) {
    //Will contain map object.
    this.map = null;

    //Has the user plotted their location marker? 
    this.marker = false;

    /**
     * @description Set value on form
     * @author Passion UI <passionui.com>
     * @date 2019-11-28
     * @param {float} lat
     * @param {float} lng
     */
    function setFormPosition(lat, lng) {
        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lng;
    }

    function addPointer(position) {
        var self = this;

        if (!self.marker) {
            //Create the marker.
            self.marker = new google.maps.Marker({
                position: position,
                map: self.map,
                draggable: true //make it draggable
            });

            //Listen for drag events!
            google.maps.event.addListener(self.marker, 'dragend', function(event) {
                setFormPosition(event.latLng.lat(), event.latLng.lng());
            });
        } else {
            self.marker.setPosition(position);
            setFormPosition(position.lat(), position.lng());
        }
    }

    this.init = function() {
        var selfClass = this;
        var self = this;
        var centerOfMap;
        var latElement = document.getElementById('latitude');
        var lat = lng = null;

        if(latElement) {
            lat = document.getElementById('latitude').value,
                lng = document.getElementById('longitude').value;

            //The center location of our map.
            if (lat && lng) {
                centerOfMap = new google.maps.LatLng(lat, lng);
            } else {
                centerOfMap = new google.maps.LatLng(listar_vars.option.map_center[0], listar_vars.option.map_center[1]);
            }

            //Map options.
            var options = {
                center: centerOfMap, //Set center.
                zoom: listar_vars.option.map_zoom //The zoom value.
            };

            //Create the map object.
            self.map = new google.maps.Map(document.getElementById(mapId), options);

            // Set point when edit
            if (lat && lng) {
                addPointer({lat: parseFloat(lat), lng: parseFloat(lng)});
            }

            // Listen for any clicks on the map.
            google.maps.event.addListener(self.map, 'click', (event) => {
                //Get the location that the user clicked.
                var clickedLocation = event.latLng;
                addPointer(clickedLocation);
            });

            // Searching Place
            var searchBox = new google.maps.places.SearchBox(document.getElementById('map-search'));
            google.maps.event.addListener(searchBox, 'places_changed', function () {
                searchBox.set(mapId, null);

                var places = searchBox.getPlaces();
                var bounds = new google.maps.LatLngBounds();
                var i, place;

                for (i = 0; place = places[i]; i++) {
                    (function (place) {
                        addPointer(place.geometry.location);
                        bounds.extend(place.geometry.location);
                    }(place));
                }

                self.map.fitBounds(bounds);
                searchBox.set(mapId, self.map);
                self.map.setZoom(Math.min(self.map.getZoom(), listar_vars.option.map_zoom));
            });
        }
    }
}