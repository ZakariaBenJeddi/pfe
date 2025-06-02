<?php
session_start();
if (empty($_SESSION['user'])) {
  header('location:../sign-in.php');
}

include('../../includes/admin/controller/controller.php');
//* deconnexion
require('../../includes/deconnexion_5s.php');
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bus Tracking System</title>
  <link rel="stylesheet" href="../../assets/css/map.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

  <style>
    /* Basic styles for loading/error states */
    .loading-message,
    .error-message {
      padding: 20px;
      text-align: center;
      font-size: 1.1em;
    }

    .loading-message {
      color: #ccc;
    }

    .error-message {
      color: #ff6666;
    }
  </style>
</head>

<body>

  <?php
  // Get route ID from URL query parameter, default to 28 if not set
  $routeId = isset($_GET['route_id']) ? (int)$_GET['route_id'] : 28;

  // Hardcoded Driver Info (could be fetched from DB along with route info)
  $driver = [
    'name' => 'Driver Name', // Replace with dynamic data if needed
    'experience' => 'N/A'       // Replace with dynamic data if needed
  ];
  ?>

  <div id="sidebar">
    <div class="route-header loading-message">
      Loading Route...
    </div>
    <div class="bus-stops">
    </div>
  </div>

  <div id="main-content">
    <div id="map-container">
      <div id="map"></div>
      <div class="status-info">
        Real-time route display
      </div>
    </div>
    <div id="driver-info">
      <span class="driver-name"><?php echo htmlspecialchars($driver['name']); ?></span>
      <span class="driver-experience"><?php echo htmlspecialchars($driver['experience']); ?></span>
    </div>
  </div>

  <script>
    // --- Map Initialization ---
    // Centered on Marrakesh as requested
    const initialCenter = [31.63, -8.00];
    const initialZoom = 12;
    const map = L.map('map').setView(initialCenter, initialZoom);

    // --- Tile Layer ---
    // Using a dark theme tile layer (CartoDB)
    //L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
    //    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
    //    subdomains: 'abcd',
    //    maxZoom: 19
    //}).addTo(map);

    L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
      attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community',
      maxZoom: 18
    }).addTo(map);

    // L.tileLayer('https://stamen-tiles-{s}.a.ssl.fastly.net/terrain/{z}/{x}/{y}{r}.png', {
    //     attribution: 'Map tiles by <a href="http://stamen.com">Stamen Design</a>, <a href="http://creativecommons.org/licenses/by/3.0">CC BY 3.0</a> &mdash; Map data &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    //     subdomains: 'abcd',
    //     maxZoom: 18
    // }).addTo(map);

    // L.tileLayer('https://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png', {
    //     attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, Tiles style by <a href="https://www.hotosm.org/" target="_blank">Humanitarian OpenStreetMap Team</a> hosted by <a href="https://openstreetmap.fr/" target="_blank">OpenStreetMap France</a>',
    //     maxZoom: 19
    // }).addTo(map);

    // L.tileLayer('https://{s}.basemaps.cartocdn.com/light_nolabels/{z}/{x}/{y}{r}.png', {
    //     attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
    //     subdomains: 'abcd',
    //     maxZoom: 19
    // }).addTo(map);

    // --- Route Data Fetching and Display ---
    const routeId = <?php echo json_encode($routeId); ?>; // Get route ID from PHP
    let currentRouteLayer = null; // To hold map markers and polyline for easy clearing

    // --- Function to Display Errors ---
    function displayError(message) {
      const sidebar = document.getElementById('sidebar');
      const routeHeader = document.querySelector('.route-header');
      const stopsContainer = document.querySelector('.bus-stops');

      if (routeHeader) routeHeader.innerHTML = `<div class="error-message">Error</div>`;
      if (stopsContainer) stopsContainer.innerHTML = `<div class="error-message">${message}</div>`;

      // Clear the map
      if (currentRouteLayer) {
        map.removeLayer(currentRouteLayer);
        currentRouteLayer = null;
      }
      // Reset view to initial center/zoom
      map.setView(initialCenter, initialZoom);
    }

    // --- Function to Update Map and Sidebar ---
    function updateRouteDisplay(data) {
      console.log(data);
      const sidebar = document.getElementById('sidebar');
      const routeHeader = document.querySelector('.route-header');
      const stopsContainer = document.querySelector('.bus-stops');
      const driverInfo = document.getElementById('driver-info');

      if (!stopsContainer || !routeHeader) {
        console.error("Sidebar elements not found!");
        displayError("Internal page structure error.");
        return;
      }

      // Clear previous content/layers
      stopsContainer.innerHTML = '';
      if (currentRouteLayer) {
        map.removeLayer(currentRouteLayer);
      }
      currentRouteLayer = L.layerGroup().addTo(map); // Create a fresh layer group

      // --- Update Header ---
      routeHeader.textContent = data.route_name || `Route #${routeId}`; // Remove loading message
      routeHeader.innerHTML += ` <span class="live-indicator route-live">LIVE</span>`; // Add Live indicator


      // --- Populate Stops/Waypoints in Sidebar and Map ---
      if (!data.stops_ordered || data.stops_ordered.length === 0) {
        stopsContainer.innerHTML = `<div class='loading-message'>No stops defined for this route.</div>`;
        return; // Don't process route geometry if no stops
      }
      data.stops_ordered.forEach((stop, index) => {
        const isWaypoint = stop.student_id === 'start' || stop.student_id === 'end';
        const stopLat = parseFloat(stop.lat);
        const stopLng = parseFloat(stop.lng);

        if (isNaN(stopLat) || isNaN(stopLng)) {
          console.warn(`Invalid coordinates for stop: ${stop.name}`, stop);
          return; // Skip stops with invalid coordinates
        }

        // Add marker to map for all stops (including start/end)
        const marker = L.marker([stopLat, stopLng]).addTo(currentRouteLayer);
        // marker.bindPopup(`<b>${stop.name || 'Waypoint'}</b>${stop.nom_classe ? '<br>'+stop.nom_classe : ''}`);
        marker.bindPopup(`<b>${stop.name|| 'Waypoint'} ${stop.prenom || 'Waypoint'}</b>${stop.nom_classe ? '<br>Classe: ' + stop.nom_classe : ''}`);

        // Only add actual student stops to the sidebar list
        if (!isWaypoint) {
          const stopDiv = document.createElement('div');
          stopDiv.className = 'stop-item';
          // Add 'active' class logic here if implementing real-time tracking

          let studentsHtml = '';
          // Assuming each "stop" corresponds to one student from the backend logic
          if (stop.name && stop.nom_classe) {
            studentsHtml = `<ul class="student-list">
                                             <li class="student-item">
                                                 <span>${htmlspecialchars(stop.name)} ${htmlspecialchars(stop.prenom)}</span>
                                                 <span class="student-grade" >${htmlspecialchars(stop.nom_classe)}</span>
                                             </li>
                                         </ul>`;
          } else if (stop.name) {
            studentsHtml = `<div style="padding-left: 15px;">${htmlspecialchars(stop.name)}</div>`;
          }

          stopDiv.innerHTML = `
                         <div class="stop-header">
                             <span class="stop-name">Stop ${index}: ${htmlspecialchars(stop.name)}</span>
                             <span class="stop-time"></span> </div>
                         ${studentsHtml}
                     `;
          stopsContainer.appendChild(stopDiv);
        }
      });

      // --- Draw Route Polyline ---
      if (data.route_geometry && data.route_geometry.length > 0) {
        // Ensure coordinates are valid numbers
        const validCoords = data.route_geometry.filter(coord =>
          !isNaN(parseFloat(coord[0])) && !isNaN(parseFloat(coord[1]))
        ).map(coord => [parseFloat(coord[0]), parseFloat(coord[1])]);

        if (validCoords.length > 1) {
          const polyline = L.polyline(validCoords, {
            color: '#4CAF50', // Green line
            weight: 5,
            opacity: 0.8
          }).addTo(currentRouteLayer);

          // Fit map view to the route bounds
          map.flyToBounds(polyline.getBounds(), {
            padding: [50, 50]
          }); // Animate zoom/pan
        } else {
          console.warn("Not enough valid coordinates to draw route line.");
          // If only one valid point, maybe just center on it?
          if (validCoords.length === 1) {
            map.flyTo(validCoords[0], 15); // Zoom closer to the single point
          }
        }

      } else {
        console.warn("No route geometry received from backend.");
        // Fit map to markers if no route line
        if (currentRouteLayer.getLayers().length > 0) {
          map.flyToBounds(currentRouteLayer.getBounds(), {
            padding: [50, 50]
          });
        }
      }

      // --- Display Route Summary Info ---
      if (driverInfo && data.total_distance_meters && data.total_duration_seconds) {
        const distanceKm = (data.total_distance_meters / 1000).toFixed(1);
        const durationMin = Math.round(data.total_duration_seconds / 60);
        // Append summary - ensure driver info isn't duplicated on refresh
        let summarySpan = driverInfo.querySelector('.route-summary');
        if (!summarySpan) {
          summarySpan = document.createElement('span');
          summarySpan.className = 'route-summary';
          summarySpan.style.marginLeft = '30px';
          summarySpan.style.color = '#ccc';
          driverInfo.appendChild(summarySpan);
        }
        summarySpan.textContent = ` | Route: ${distanceKm} km, ~${durationMin} min`;
      }

    }

    // Helper function to prevent basic XSS by escaping HTML
    function htmlspecialchars(str) {
      if (typeof str !== 'string') return '';
      const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
      };
      return str.replace(/[&<>"']/g, function(m) {
        return map[m];
      });
    }


    // --- Fetch route data on page load ---
    console.log(`Workspaceing data for route: ${routeId}`);
    fetch(`get_optimized_route.php?route_id=${routeId}`)
      .then(response => {
        if (!response.ok) {
          // Attempt to parse error JSON, otherwise use status text
          return response.json().catch(() => {
            throw new Error(`HTTP error ${response.status}: ${response.statusText}`);
          }).then(errData => {
            throw new Error(errData.error || `HTTP error ${response.status}`);
          });
        }
        return response.json(); // Parse successful response as JSON
      })
      .then(data => {
        console.log("Received data:", data); // Log received data for debugging
        if (data.error) {
          displayError(data.error); // Display error message from backend JSON
        } else if (data.status === 'success') {
          updateRouteDisplay(data); // Update display with successful data
        } else {
          displayError('Received unexpected data format from server.'); // Handle unexpected format
        }
      })
      .catch(error => {
        console.error('Fetch Error:', error); // Log detailed error to console
        displayError(error.message || 'Could not fetch route data. Check network connection and backend script.'); // Display user-friendly error
      });
  </script>

</body>

</html>