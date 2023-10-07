<link rel="stylesheet" href="https://unpkg.com/leaflet@1.8.0/dist/leaflet.css" />
<style>
    #map {
        height: 70vh;
        width: 70%;
        position: relative;
    }
</style>
<div id="map"></div>
<script src="https://unpkg.com/leaflet@1.8.0/dist/leaflet.js"></script>
<script>
    var lotisments = [];
    let drewLayer = null;
    let map = null;

    const setUpMap = _ => {
        if (map != null)
            map.remove()
        map = L.map('map').setView([33.56039091314251, -7.729955566363856], 24); //coordonnée WGS 84 + Zoom,

        // Couche OSM
        var osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            // attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        });
        osm.addTo(map);

        // Couche des Images satelitaire 
        var Esri_WorldImagery = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            // attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
        });
        Esri_WorldImagery.addTo(map);

        // Couche Google Map

        //google street
        googleStreets = L.tileLayer('http://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
            maxZoom: 20,
            subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
        });
        googleStreets.addTo(map);

        //google satelite
        googleSat = L.tileLayer('http://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
            maxZoom: 20,
            subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
        });
        googleSat.addTo(map);

        //google Hybrid
        googleHybrid = L.tileLayer('http://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}', {
            maxZoom: 20,
            subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
        });
        googleHybrid.addTo(map);


        // controlleur des couches 
        var baseLayers = {
            "Esri World Imagery": Esri_WorldImagery,
            "Google Streets": googleStreets,
            "Google Sat": googleSat,
            "Google Hybrid": googleHybrid,
            "Open Street Map": osm
        };
        L.control.layers(baseLayers).addTo(map);
    }



    setUpMap()
</script>