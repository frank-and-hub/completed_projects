
const lat_ = typeof LAT !== 'undefined' ? LAT : 37.39094933041195;
const lng_ = typeof LNG !== 'undefined' ? LNG : -122.02503913145092;

let map, draggableMarker, geocoder;

/*
async function initMap() {
    const {ColorScheme} = await google.maps.importLibrary("core")
    // Request needed libraries.
    const { Map, InfoWindow } = await google.maps.importLibrary("maps");
    const { AdvancedMarkerElement } = await google.maps.importLibrary("marker");
    map = new Map(document.getElementById("map"), {
        center: { lat: lat_, lng: lng_ },
        zoom: 14,
        mapId: "4504f8b37365c3d0",
        colorScheme: ColorScheme.DARK,
    });
    const infoWindow = new InfoWindow();

    draggableMarker = new AdvancedMarkerElement({
        map,
        position: { lat: lat_, lng: lng_ },
        gmpDraggable: true,
        title: "This marker is draggable.",
    });

    draggableMarker.addListener("dragend", (event) => {
        const position = draggableMarker.position;

        infoWindow.close();
        infoWindow.setContent(`Pin dropped at: ${position.lat}, ${position.lng}`);

        $('#latitude').val(`${position.lat}`);
        $('#longitude').val(`${position.lng}`);

        infoWindow.open(draggableMarker.map, draggableMarker);
    });

    const input = document.getElementById("pac-input");
    const searchBox = new google.maps.places.SearchBox(input);
    const autocomplete = new google.maps.places.Autocomplete(input);
    autocomplete.bindTo('bounds', map);

    // map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

    // Bias the SearchBox results towards current map's viewport.
    map.addListener("bounds_changed", () => {
        searchBox.setBounds(map.getBounds());
    });

    searchBox.addListener('places_changed', () => {
        const places = searchBox.getPlaces();

        if (places.length == 0) {
          return;
        }

        const bounds = new google.maps.LatLngBounds();
        console.info(bounds);

        places.forEach((place) => {
            if (!place.geometry || !place.geometry.location) {
                console.info("Returned place contains no geometry");
                return;
            }
            console.info(place.geometry.viewport);

            // Update the draggableMarker position
             draggableMarker.position = place.geometry.location;

            if (place.geometry.viewport) {
                // Only geocodes have viewport.
                bounds.union(place.geometry.viewport);
            } else {
                bounds.extend(place.geometry.location);
            }
        });
        map.fitBounds(bounds);
    });
}
*/

async function initMap() {
    const { ColorScheme } = await google.maps.importLibrary("core");
    const { Map, InfoWindow } = await google.maps.importLibrary("maps");
    const { AdvancedMarkerElement } = await google.maps.importLibrary("marker");

    geocoder = new google.maps.Geocoder();

    map = new Map(document.getElementById("map"), {
        center: { lat: lat_, lng: lng_ },
        zoom: 14,
        mapId: "4504f8b37365c3d0",
        colorScheme: ColorScheme.DARK,
    });

    const infoWindow = new InfoWindow();

    draggableMarker = new AdvancedMarkerElement({
        map,
        position: { lat: lat_, lng: lng_ },
        gmpDraggable: true,
        title: "This marker is draggable.",
    });

    draggableMarker.addListener("dragend", () => {
        const position = draggableMarker.position;
        infoWindow.close();
        infoWindow.setContent(`Pin dropped at: ${position.lat}, ${position.lng}`);
        $("#latitude").val(position.lat);
        $("#longitude").val(position.lng);
        infoWindow.open(map, draggableMarker);
    });

    const input = document.getElementById("pac-input");
    const searchBox = new google.maps.places.SearchBox(input);
    const autocomplete = new google.maps.places.Autocomplete(input);
    autocomplete.bindTo("bounds", map);

    map.addListener("bounds_changed", () => {
        searchBox.setBounds(map.getBounds());
    });

    searchBox.addListener("places_changed", () => {
        const places = searchBox.getPlaces();
        if (!places.length) return;

        const bounds = new google.maps.LatLngBounds();

        places.forEach((place) => {
            if (!place.geometry?.location) return;
            draggableMarker.position = place.geometry.location;
            map.setCenter(place.geometry.location);
            $('#latitude').val(place.geometry.location.lat());
            $('#longitude').val(place.geometry.location.lng());

            if (place.geometry.viewport) {
                bounds.union(place.geometry.viewport);
            } else {
                bounds.extend(place.geometry.location);
            }
        });

        map.fitBounds(bounds);
    });

    // Initial map set (optional): just center it
    map.setCenter({ lat: lat_, lng: lng_ });
}
