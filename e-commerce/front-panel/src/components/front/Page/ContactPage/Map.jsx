import React, { Component } from 'react';
import L from 'leaflet'; // Make sure you import Leaflet

class Map extends Component {
    componentDidMount() {
        // Initialize map
        const mymap = L.map('mapid').setView([-23.013104, -43.394365], 13);  // No need for third value in coordinates

        // Add tile layer
        L.tileLayer('', {
            maxZoom: 18,
            attribution: '',
            id: 'mapbox/streets-v11',
            tileSize: 512,
            zoomOffset: -1
        }).addTo(mymap);

        L.marker([-23.013104, -43.394365]).addTo(mymap)
            .bindPopup("<b>Frank And Hub Technologies</b> Pvt. Ltd. Company.<br />").openPopup();

        // Disable scroll and touch zoom
        mymap.scrollWheelZoom.disable();
        mymap.touchZoom.disable();
    }

    render() {
        return (
            <>
                <div id="mapid" style={{ width: '100%', height: '300px' }}></div>
            </>
        );
    }
}

export default Map;