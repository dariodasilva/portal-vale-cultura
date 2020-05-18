
            var map;

            function initialize() {
                map = new google.maps.Map(document.getElementById('map-canvas'), {
                    center: new google.maps.LatLng(-15.798342600000000000, -47.875551299999984000),
                    zoom: 16
                });
                var style = [{
                    featureType: 'all',
                    elementType: 'all',
                    stylers: [{
                        saturation: 23
                    }]
                }, {
                    featureType: 'administrative.land_parcel',
                    elementType: 'all',
                    stylers: [{
                        visibility: 'off'
                    }]
                }, {
                    featureType: 'poi',
                    elementType: 'all',
                    stylers: [{
                        visibility: 'off'
                    }]
                }, {
                    featureType: 'transit',
                    elementType: 'all',
                    stylers: [{
                        visibility: 'off'
                    }]
                }];
                var styledMapType = new google.maps.StyledMapType(style, {
                    map: map,
                    name: 'Styled Map'
                });
                map.mapTypes.set('map-style', styledMapType);
                map.setMapTypeId('map-style');
            }
            google.maps.event.addDomListener(window, 'load', initialize);
