const markerPositions = new Set();
let animationend = false;
const total = 100;
let intersectionObserver = new IntersectionObserver(
    (entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add("drop_map");
                intersectionObserver.unobserve(entry.target);
            }
        });
    },
    { threshold: 0.1 }
);

window.initMap = async function () {
    try {
        if (!google || !google.maps || !google.maps.importLibrary) {
            throw new Error(
                "Google Maps API not loaded or importLibrary unavailable"
            );
        }

        const { Map } = await google.maps.importLibrary("maps");
        const { AdvancedMarkerElement, pinElement } =
            await google.maps.importLibrary("marker");

        const position = { lat: -33.9249, lng: 18.4241 }; // Coordinates for Cape Town, South Africa

        const map = new Map(document.getElementById("map"), {
            zoom: 16,
            center: position,
            mapId: "4504f8b37365c3d0",
            minZoom: 3,
        });

        await setMarkerByAjax(map, AdvancedMarkerElement, pinElement);

        const controlDiv = document.createElement("div"),
            controlUI = document.createElement("button");

        controlUI.classList.add("ui-button");
        controlUI.innerText = "Reset";
        controlUI.addEventListener("click", () => refreshMap());
        controlDiv.appendChild(controlUI);
        map.controls[google.maps.ControlPosition.TOP_CENTER].push(controlDiv);

        map.addListener(
            "dragend",
            async () =>
                await setMarkerByAjax(map, AdvancedMarkerElement, pinElement)
        );
        map.addListener(
            "zoom_changed",
            async () =>
                await setMarkerByAjax(map, AdvancedMarkerElement, pinElement)
        );

        const currentZoomLevel = map.getZoom();

        // const marker = new AdvancedMarkerElement({
        const marker = new google.maps.Marker({
            map: map,
            position: position,
        });

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    const lat = pos?.coords?.latitude;
                    const lng = pos?.coords?.longitude;
                    map.setCenter({ lat, lng });
                    // marker.position = { lat, lng };
                },
                () => {
                    console.log("Could not get geolocation.");
                },
                { enableHighAccuracy: true }
            );
        }
    } catch (error) {
        console.error("Error initializing map:", error);
    }
};

async function setMarkerByAjax(map, AdvancedMarkerElement, pinElement) {
    try {
        const center = map.getCenter();
        const lat = center.lat();
        const lng = center.lng();
        const zoom = map.getZoom();

        let response = await $.post(
            PROPERTY_ALL_LAT_LNG_URL,
            {
                lat: lat,
                lng: lng,
                zoom: zoom,
            },
            "JSON"
        );

        if (!Array.isArray(response) || response.length === 0) {
            // console.warn("No valid data received from AJAX:", response);
            return;
        }

        markerPositions.clear();

        console.log(response);
        await Promise.all(
            response.map(async (item) => {
                const latAjax = Number(item["lat"]);
                const lngAjax = Number(item["lng"]);

                if (isNaN(latAjax) || isNaN(lngAjax)) return;

                await createMarkerDrag(
                    map,
                    AdvancedMarkerElement,
                    latAjax,
                    lngAjax,
                    item
                );
            })
        );
    } catch (error) {
        console.error("AJAX Error: ", error);
    }
}

async function createMarkerDrag(
    map,
    AdvancedMarkerElement,
    lat,
    lng,
    response = {}
) {
    const positionKey = `${lat},${lng}`;
    if (markerPositions.has(positionKey)) {
        return;
    }
    markerPositions.add(positionKey);
    try {
        const pinElement = new google.maps.marker.PinElement({
            background: "#FF0000", // Red pin for visibility
            borderColor: "#FFFFFF",
            glyphColor: "#FFFFFF",
        });

        const advancedMarker = new AdvancedMarkerElement({
            // const advancedMarker = new google.maps.Marker({
            position: { lat, lng },
            map: map,
            content: pinElement.element,
        });

        const res_id = response?.id ?? ``,
            rawImagePath = response?.images
                ? response?.images?.[0]?.path ?? ``
                : response?.photos[0]
                ? response?.photos?.[0].imgUrl ?? ``
                : `https://pocketproperty.app/_next/static/media/logo.f88f19f0.svg`,
            image_url = response?.images
                ? `${window.location.origin}/storage/${rawImagePath}`
                : rawImagePath,
            title = response?.title,
            price = response?.financials
                ? getCurrencySymbol(response?.financials?.currency) +
                  " " +
                  response?.financials?.price
                : getCurrencySymbol(response?.currency) + " " + response?.price,
            location = `${response?.suburb}, ${response?.town}, ${response?.province}`;

        // let currentInfoWindow = null;

        advancedMarker.addListener("click", () => {
            const dynamic_url = response?.financials
                    ? PROPERTY_ADMIN_VIEW_PAGE
                    : EXTERNAL_PROPERTY_ADMIN_VIEW_PAGE,
                url = dynamic_url.replace(123456, res_id),
                infoContent = `<a href='${url}' target="_blank" class="admin-map-property">
                                <div class="row w-100">
                                    <div class="col-4">
                                        <img src="${image_url}">
                                    </div>
                                    <div class="col-8">
                                        <div style="font-size: 25px;">
                                            <span style="font-weight: 700;">${title}</span>
                                        </div>
                                        <div style="font-size: 20px;">
                                            <span style="font-weight: 400;">Price :</span>
                                            <span>${price}</span>
                                        </div>
                                        <div style="font-size: 20px;">
                                            <span style="font-weight: 400;">Location :</span>
                                            <span>${location}</span>
                                        </div>
                                    </div>
                                </div>
                            </a>`;
            const infoWindow = new google.maps.InfoWindow({
                content: infoContent,
            });
            if (window.currentInfoWindow) window.currentInfoWindow.close();
            infoWindow.open(map, advancedMarker);
            window.currentInfoWindow = infoWindow;
        });

        google.maps.event.addListener(map, "click", () => {
            if (window.currentInfoWindow) {
                window.currentInfoWindow.close();
                window.currentInfoWindow = null;
            }
        });

        const content = advancedMarker.content;

        if (content && content instanceof HTMLElement && !animationend) {
            // Check if animation already ran
            if (content.dataset.hasAnimated === "true") return;

            content.style.opacity = "0";
            content.classList.add("drop_map");

            content.addEventListener(
                "animationend",
                () => {
                    content.classList.remove("drop_map");
                    content.style.opacity = "1";

                    // Mark it as animated so it doesn't happen again
                    content.dataset.hasAnimated = "true";
                },
                { once: true }
            );

            const time = (1 + Math.random()).toFixed(2);
            content.style.setProperty("--delay-time", `${time}s`);
            intersectionObserver.observe(content);
            animationend = true;
        } else {
            console.warn("Marker content is not a valid HTMLElement:", content);
        }
    } catch (error) {
        console.error("Error creating marker:", error);
    }
}

function refreshMap(map) {
    try {
        const mapContainer = document.getElementById("mapContainer");
        const oldMap = document.getElementById("map");
        if (oldMap) oldMap.remove();

        const mapDiv = document.createElement("div");
        mapDiv.id = "map";
        mapContainer.appendChild(mapDiv);
        window.initMap();
    } catch (error) {
        console.error("Error refreshing map:", error);
    }
}

if (typeof google === "undefined" || !google.maps) {
    console.warn("Google Maps not loaded yet, waiting...");
} else {
    window.initMap();
}
