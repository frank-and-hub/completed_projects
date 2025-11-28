

window.pin_load_elem = null;
$(document).ready(function () {
    map(long, lat);
});

function map(lng, lat) {
    console.info("lang:", lng,"lat:", lat);
    mapboxgl.accessToken = token_;
    map = new mapboxgl.Map({
        container: 'map', // container id
        style: 'mapbox://styles/mapbox/satellite-streets-v11', //hosted style id
        center: [lng, lat], // starting position
        zoom: 15,// starting zoom

    });
    map.addControl(new mapboxgl.FullscreenControl());


    //ADDING SEARCH BOX START
    var geocoder = new MapboxGeocoder({ // Initialize the geocoder
        accessToken: mapboxgl.accessToken, // Set the access token
        placeholder: 'Search for places', // set the placeholder
        mapboxgl: mapboxgl, // Set the mapbox-gl instance
        marker: false,

    });

    // Add the geocoder to the map
    map.addControl(geocoder);
    //ADDING SEARCH BOX END

    map.on('load', function () {

        pin = [$('#longitude').val(), $('#latitude').val()];
        window.pin_load_elem = new mapboxgl.Marker({
            color: '#48D33A',
            draggable: true
        }).setLngLat(pin).addTo(map);

        //When use searchbar
        geocoder.on('result', (event) => {

            $('#longitude').val(event.result.geometry.coordinates[0]);
            $('#latitude').val(event.result.geometry.coordinates[1]);
            $("#address").val(event.result.place_name);
            console.info("event", event);
            var context = event.result.context;
            context.filter(function (element, index) {

                if (element.id.split('.')[0] == 'country') {
                    $("#country").val(element.text);
                }else if(element.id.split('.')[0]=='place'){
                    $("#city").val(element.text);
                }
            })


            if (window.pin_load_elem) {
                window.pin_load_elem.remove();
            }
            window.pin_load_elem = new mapboxgl.Marker({
                color: '#48D33A',
                draggable: true
            }).setLngLat([event.result.geometry.coordinates[0], event.result.geometry.coordinates[1]]).addTo(map);

            // map.getSource('single-point').setData(event.result.geometry);
        });

        //click on the map
        map.on('click', (e) => {
            console.info(e);
            pin = [e.lngLat.lng, e.lngLat.lat];
            if (window.pin_load_elem) {
                window.pin_load_elem.remove();
            }
            window.pin_load_elem = new mapboxgl.Marker({
                color: '#48D33A',
                draggable: true
            }).setLngLat(pin).addTo(map);

            var url = "https://api.mapbox.com/geocoding/v5/mapbox.places/" + e.lngLat.lng +
                "," + e.lngLat.lat +
                ".json?access_token=" +
                mapboxgl.accessToken;
            $.get(url, function (data) {

                var context = data.features;
                context.filter(function (element, index) {
                    if (element.id.split('.')[0] == 'country') {
                        $("#country").val(element.text);
                    }
                    else if(element.id.split('.')[0]=='place'){
                        $("#city").val(element.text);
                    }
                })
                var myData = data;
                console.info(myData.features[0].place_name);
                document.getElementById("address").value = myData.features[0].place_name;
            });



            $('#latitude').val(e.lngLat.lat);
            $('#longitude').val(e.lngLat.lng);

            console.info(document.getElementById("address").value);


        });


    });

}

