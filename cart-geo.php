<?php include 'header.php';


require_once 'admin/config/config.php';

$db = getDbInstance();
$db->join("terrain t", "t.id=l.id_terrain", "LEFT");
$lotissments = $db->get('lotissment l', null, 'l.id, l.id_terrain, l.nlot, l.titre, l.affectation, l.urbanistique, l.surface, l.consistance, l.npropr, l.cords, t.nom');

$lotissments = array_map(function ($item) {
  $item['cords'] = json_decode($item['cords']);
  return $item;
}, $lotissments);
// $lotissments = json_encode($lotissments);

$terrainIds = getDbInstance()->get('terrain');

?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.8.0/dist/leaflet.css" />
<style>
  #map {
    height: 650px;
    width: 100%;
    position: relative;
  }

  section.leaflet-control-layers-list {
    padding: 0 !important;
  }

  body {
    background-color: #eee;

  }

  .bdge {
    height: 21px;
    background-color: orange;
    color: #fff;
    font-size: 11px;
    padding: 8px;
    border-radius: 4px;
    line-height: 3px;
  }

  .comments {
    margin-bottom: -1px;
    z-index: 9;
    border-bottom: 1px solid;
  }

  .dot {
    height: 7px;
    width: 7px;
    margin-top: 3px;
    background-color: #bbb;
    border-radius: 50%;
    display: inline-block;
  }

  .hit-voting:hover {
    color: blue;
  }

  .hit-voting {
    cursor: pointer;
  }

  .mr-2,
  .mx-2 {
    margin-right: 0.5rem !important;
  }

  .mr-3,
  .mx-3 {
    margin-right: 1rem !important;
  }

  .loading:before {
    content: "";
    display: inline-block;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    margin: 0 10px -6px 0;
    border: 3px solid #0555cb;
    border-top-color: #eee;
    -webkit-animation: animate-loading 1s linear infinite;
    animation: animate-loading 1s linear infinite;
  }
</style>

<main id="main">

  <section id="contact" class="contact">
    <div class="container" data-aos="fade-up">

      <div class="section-title">
        <h2>geo</h2>
        <p>map </p>
      </div>

      <div class="row">
        <div class="col-lg-12 mt-5 mt-lg-0 d-flex align-items-stretch">
          <div class="php-email-form">

            <div class="form-group">
              <label for="name">Lotisments</label>
              <select type="text" class="form-control" name="lot" id="lot" required>
                <?php foreach ($terrainIds as $terrainId) :
                  $lotissmentsForThisTerra = array_filter($lotissments, function ($i) use ($terrainId) {
                    return $i['id_terrain'] == $terrainId['id'];
                  })
                ?>
                  <optgroup label="<?= $terrainId['nom'] ?>">
                    <?php foreach ($lotissmentsForThisTerra as $item) : ?>
                      <option value="<?= $item['id'] ?>"><?= $item['titre'] ?></option>
                    <?php endforeach; ?>
                  <?php endforeach; ?>
              </select>
            </div>

            <!-- <div class="form-group">
              <label for="name">b</label>
              <input type="text" class="form-control" name="subject" id="subject">
            </div> -->

            <div class="my-3">
              <div class="loading">Loading</div>
              <div class="error-message">azdazdazd</div>
              <div class="sent-message">Your message has been sent. Thank you!</div>
            </div>

            <div class="text-center">
              <button type="submit" onclick="search(event)">chercher</button>
            </div>
          </div>
        </div>

        <div class="col-lg-12 d-flex align-items-stretch">
          <div class="info">
            <div class="address" style="display: none;">
              <i class="bi bi-geo-alt"></i>
              <h4>Location:</h4>
              <p>xxxxxxxxxxx , sale</p>
            </div>

            <div class="lot-info">
            </div>


            <div id="map"></div>
            <div id="avis">
              <div class="container mt-5 mb-5">
                <div class="d-flex justify-content-center row">
                  <div class="d-flex flex-column col-md-8">

                    <div class="d-flex flex-row align-items-center text-left comment-top bg-white border-bottom">
                      <span class="mr-2 comments">0 comments</span>
                    </div>

                    <div class="coment-bottom bg-white p-2 px-4">
                      <div class="d-flex flex-row add-comment-section mt-4 mb-4">
                        <img class="img-fluid img-responsive rounded-circle mr-3" src="https://i.imgur.com/qdiP4DB.jpg" width="38">
                        <input type="text" name="nom" class="form-control " placeholder="Nom">
                      </div>
                      <textarea type="text" name="message" class="form-control mb-3" placeholder="Avis"></textarea>
                      <button class="btn btn-primary mb-4" onclick="submitComment(event)" type="button">Comment</button>

                      <div class="message"></div>

                      <div class="commented-section">
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <script src="https://unpkg.com/leaflet@1.8.0/dist/leaflet.js"></script>
            <script>
              var lotisments = <?= json_encode($lotissments) ?>;
              let drewLayer = null;
              let map = null;
              let selectedLot = null
              let comments = []

              const setUpMap = (center = [33.56039091314251, -7.729955566363856], zoom = 14) => {
                if (map != null)
                  map.remove()
                map = L.map('map').setView(center, zoom); //coordonnée WGS 84 + Zoom,

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
                  "Open Street Map": osm,
                  "Google Hybrid": googleHybrid,
                };
                L.control.layers(baseLayers).addTo(map);
              }

              const drawLayer = data => {
                // set up layer
                drewLayer = L.geoJSON(data).addTo(map);
              }

              const removeLayer = layer => {
                if (!layer) return false

                map.removeLayer(layer);
                return false
              }

              const search = e => {
                e.preventDefault()
                // start loader
                loading(true, e)

                // clear comments
                resetComments()

                // get cord to center map
                selectedLot = lotisments.find(item => item.id == lot.value)
                let center = JSON.parse(JSON.stringify(selectedLot.cords.coordinates[0][0])).reverse()
                let zoom = 24
                setUpMap(center, zoom)

                // print lot info
                let txt = `<h4>affectation:</h4>
                            <p>${selectedLot.affectation}</p>

                            <h4>consistance:</h4>
                            <p>${selectedLot.consistance}</p>

                            <h4>nlot:</h4>
                            <p>${selectedLot.nlot}</p>

                            <h4>affectation:</h4>
                            <p>${selectedLot.affectation}</p>

                            <h4>nom:</h4>
                            <p>${selectedLot.nom}</p>

                            <h4>npropr:</h4>
                            <p>${selectedLot.npropr}</p>

                            <h4>surface:</h4>
                            <p>${selectedLot.surface}</p>

                            <h4>titre:</h4>
                            <p>${selectedLot.titre}</p>`

                document.querySelector('.lot-info').innerHTML = txt

                // draw graph
                drawLayer(selectedLot.cords)

                // load comment for this lot
                APIGetComments(lot.value)

                // end loader
                loading(false, e)
              }

              const printMessage = msg => {
                document.querySelector(".message").innerHTML = msg
              }

              const loading = (stat, e) => {
                if (stat) {
                  e.target.classList.add('loading')
                  e.target.setAttribute("disabled", "true");
                } else {
                  e.target.classList.remove('loading')
                  e.target.removeAttribute("disabled");
                }
              }

              const setupComments = _ => {
                document.querySelector('.commented-section').innerHTML = comments.map(item => {
                  return '<div class="comment mb-5"> <div class="d-flex flex-row align-items-center commented-user"> <h5 class="mr-2">' + item.nom + '</h5> </div> <div class="comment-text-sm"><span>' + item.message + '.</span></div> </div>'
                }).join('')

                document.querySelector('.comments').textContent = comments.length + ' comments'
              }

              const resetComments = _ => {
                document.querySelector('.message').innerHTML = ''
                document.querySelector('.commented-section').innerHTML = ''
                document.querySelector('.comments').textContent = '0 comments'
                comments = []
              }

              const submitComment = e => {
                // start loading
                loading(true, e)

                let url = "submit_comment.php"
                var formData = new FormData();
                let nom = document.querySelector('[name="nom"]')
                let message = document.querySelector('[name="message"]')

                if (nom == '' || message == '') {
                  printMessage('<div class="alert alert-danger" role="alert">Error: saisir tout les champs</div>')
                  loading(false, e)
                  return false
                }

                formData.append('lot_id', lot.value);
                formData.append('nom', nom.value);
                formData.append('message', message.value);

                fetch(url, {
                    method: 'POST',
                    body: formData
                  })
                  .then(e => e.json())
                  .then(data => {
                    console.log(data)
                    if (data.success) {
                      // everything is saveed
                      printMessage('<div class="alert alert-success" role="alert">' + data.message + '</div>')

                      // lets close the form
                      comments.unshift({
                        nom: nom.value,
                        message: message.value
                      })

                      // reset message box
                      message.value = ''

                      setupComments()

                    } else
                      printMessage('<div class="alert alert-danger" role="alert">' + data.message + '</div>')
                  }).catch(e => {
                    printMessage('<div class="alert alert-danger" role="alert">Error: ' + e + '</div>')
                  })
                  .finally(_ => loading(false, e))
              }

              const APIGetComments = lot_id => {
                var formData = new FormData();
                formData.append("lot_id", lot_id)
                fetch("api_comments.php", {
                    method: 'POST',
                    body: formData
                  })
                  .then(e => e.json())
                  .then(data => {
                    if (data.success) {

                      if (data.data.length == 0) {
                        document.querySelector('.commented-section').innerHTML = "there is no comment"
                        return
                      }

                      // lets close the form
                      comments = data.data

                      setupComments()

                    } else
                      printMessage('<div class="alert alert-danger" role="alert">' + data.message + '</div>')
                  }).catch(e => {
                    printMessage('<div class="alert alert-danger" role="alert">Error: ' + e + '</div>')
                  })
              }

              setUpMap()
            </script>
          </div>

        </div>



      </div>

    </div>
  </section><!-- End Contact Section -->

</main><!-- End #main -->

<?php include 'footer.php'; ?>