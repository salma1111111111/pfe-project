<?php

session_start();
require_once 'config/config.php';
require_once BASE_PATH . '/includes/auth_validate.php';

$db = getDbInstance();
$polys = $db->get('lotissment', null, 'cords');
if ($polys)
    $polys = array_map(function ($item) {
        return json_decode($item['cords']);
    }, $polys);

$terrains = $db->get('terrain');


include BASE_PATH . '/includes/header.php';
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.8.0/dist/leaflet.css" />
<style>
    #map {
        height: 70vh;
        width: 70%;
        position: relative;
    }

    #drawButton {
        position: absolute;
        top: 13px;
        right: 85px;
        padding: 11px;
        z-index: 401;
        font-size: 13px;
    }

    #form,
    .message {
        width: 700px;
        margin: 15px;
    }

    #form {
        display: none;
    }
</style>
<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">lotissements</h1>
        </div>

        <div class="col-lg-12">
            <div id="map">
                <button id="drawButton">Start draw</button>
            </div>

            <div class="message"></div>
            <form id="form" onsubmit="submitFn(event)">
                <div class="form-group">
                    <label for="Id_terrain" class="form-label">Terrain menu</label>
                    <select class="form-control" aria-label="Default select example" name="Id_terrain" id="Id_terrain">
                        <?php foreach ($terrains as $terrain) : ?>
                            <option value="<?= $terrain['id'] ?>"><?= $terrain['nom'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="nlot" class="form-label">Num lot:</label>
                    <input type="text" class="form-control" name="nlot" id="nlot">
                </div>

                <div class="form-group">
                    <label for="titre" class="form-label">Titre:</label>
                    <input type="text" class="form-control" name="titre" id="titre">
                </div>

                <div class="form-group">
                    <label for="affectation" class="form-label">Affectation:</label>
                    <input type="text" class="form-control" name="affectation" id="affectation">
                </div>

                <div class="form-group">
                    <label for="urbanistique" class="form-label">Urbanistique:</label>
                    <input type="text" class="form-control" name="urbanistique" id="urbanistique">
                </div>

                <div class="form-group">
                    <label for="surface" class="form-label">Surface:</label>
                    <input type="text" class="form-control" name="surface" id="surface">
                </div>

                <div class="form-group">
                    <label for="consistance" class="form-label">Consistance:</label>
                    <input type="text" class="form-control" name="consistance" id="consistance">
                </div>

                <div class="form-group">
                    <label for="npropr" class="form-label">Nom propriétaire</label>
                    <input type="text" class="form-control" name="npropr" id="npropr">
                </div>

                <button type="submit" id="submit" class="btn btn-primary">Sauvegarder</button>
                <button type="submit" id="cancel" class="btn btn-secondary">Annuler</button>
            </form>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.8.0/dist/leaflet.js"></script>
    <script>
        var villa = <?= json_encode($polys) ?>;
        let mapStat = "normal";
        let isNewFormatDrew = false
        let drawerButttonText = drawButton.textContent;
        let tempGeometData = []
        let tempGeometForm = {}
        let cords = null
        var map = null
        let layers = []
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

        const setPolyGones = _ => {
            // set up layer
            villa.forEach(item => {
                layers.push(L.geoJSON(item).addTo(map));
            })
        }

        const getCordsOnClick = e => {
            let lat = e.latlng.lat;
            let lng = e.latlng.lng;

            tempGeometData.push([lng, lat])
            drawGraph()
        }

        const removeLayer = layer => {
            if (!layer) return false

            map.removeLayer(layer);
            return false
        }

        const drawGraph = e => {
            isNewFormatDrew = removeLayer(isNewFormatDrew)

            if (tempGeometData.length == 1) {
                tempGeometForm.type = "Point"
                tempGeometForm.coordinates = tempGeometData[0]
            } else if (tempGeometData.length == 2) {
                tempGeometForm.type = "LineString"
                tempGeometForm.coordinates = tempGeometData
            } else {
                tempGeometForm.type = "Polygon"
                tempGeometForm.coordinates = [tempGeometData]
            }

            // draw graph on map
            isNewFormatDrew = L.geoJSON(tempGeometForm, {
                style: _ => {
                    return {
                        color: "#f44336"
                    };
                }
            }).addTo(map);

        }

        drawButton.onclick = e => {
            e.stopPropagation()

            if (mapStat == "normal") {
                // drawing..........
                mapStat = 'drawing'
                drawButton.textContent = 'Drawing press to stop'
                map.on('click', getCordsOnClick);
                clearMessage()

            } else {
                // normal stat..........
                showForm(isNewFormatDrew)
                if (isNewFormatDrew)
                    cords = tempGeometForm

                // lets return to normal stat
                mapStat = 'normal'
                drawButton.textContent = drawerButttonText
                map.off('click', getCordsOnClick);
            }

            tempGeometForm = {}
            tempGeometData = []
        }

        const showForm = stat => {
            if (!stat) return
            form.style.display = "block";
        }

        const hideForm = _ => {
            form.style.display = "none";
        }

        const cancelFn = _ => {
            removeLayer(isNewFormatDrew)
            hideForm()
        }

        const submitFn = e => {
            e.preventDefault();
            let url = "submit-map.php";

            var formData = new FormData();
            let Id_terrain = document.querySelector('[name="Id_terrain"]').value
            let nlot = document.querySelector('[name="nlot"]').value
            let titre = document.querySelector('[name="titre"]').value
            let affectation = document.querySelector('[name="affectation"]').value
            let urbanistique = document.querySelector('[name="urbanistique"]').value
            let surface = document.querySelector('[name="surface"]').value
            let consistance = document.querySelector('[name="consistance"]').value
            let npropr = document.querySelector('[name="npropr"]').value

            if (Id_terrain == '' || nlot == '' || titre == '' || affectation == '' || urbanistique == '' || surface == '' || consistance == '' || npropr == '')
                printMessage('<div class="alert alert-danger" role="alert">Error: saisir tout les champs</div>')

            formData.append('id_terrain', Id_terrain);
            formData.append('nlot', nlot);
            formData.append('titre', titre);
            formData.append('affectation', affectation);
            formData.append('urbanistique', urbanistique);
            formData.append('surface', surface);
            formData.append('consistance', consistance);
            formData.append('npropr', npropr);
            formData.append('cords', JSON.stringify(cords));

            fetch(url, {
                    method: 'POST',
                    body: formData
                })
                .then(e => e.json())
                .then(data => {
                    if (data.success) {
                        // everything is saveed
                        printMessage('<div class="alert alert-success" role="alert">' + data.message + '</div>')

                        // lets close the form
                        hideForm()

                        // add new garaphe
                        villa.push(cords)
                        cords = null

                        // re-setup the map
                        setUpMap()
                        setPolyGones()
                    } else
                        printMessage('<div class="alert alert-danger" role="alert">' + data.message + '</div>')
                }).catch(e => {
                    printMessage('<div class="alert alert-danger" role="alert">Error: ' + e + '</div>')
                })

        }

        const printMessage = msg => {
            document.querySelector(".message").innerHTML = msg
        }

        const clearMessage = _ => {
            document.querySelector(".message").innerHTML = ''
        }


        setUpMap()
        setPolyGones()
    </script>

</div>

<?php include BASE_PATH . '/includes/footer.php'; ?>