<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
  <title>Carte Leaflet - Marjane Route Casablanca</title>
  <style>
    #map {
      height: 400px;
      width: 100%;
    }
  </style>
</head>

<body>
  <div id="map"></div>

  <script>
    // Initialiser la carte à Marjane Route Casablanca, Marrakech
    var map = L.map('map').setView([31.6685, -8.0105], 15);

    // Ajouter une couche de tuiles à la carte (OpenStreetMap)
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '© OpenStreetMap'
    }).addTo(map);

    // Ajouter un marqueur pour Marjane Route Casablanca
    L.marker([31.6685, -8.0105]).addTo(map)
      .bindPopup('<b>Marjane Route Casablanca</b><br>Marrakech, Maroc')
      .openPopup();
  </script>
</body>

</html>
