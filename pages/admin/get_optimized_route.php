<?php

// --- Configuration ---
header('Content-Type: application/json');

// IMPORTANT: Replace with your actual ORS API key
// Consider loading from an environment variable or a secure config file
$orsApiKey = '5b3ce3597851110001cf624877e06b178a9f4fbf9b1b06210dbca764';

// --- Database Connection ---
$servername = "localhost";        // Or your DB host
$username = "root"; // Your DB username
$password = ""; // Your DB password
$dbname = "pfe1";   // Your Database name (CORRIGÉ)

$routeId = isset($_GET['route_id']) ? (int)$_GET['route_id'] : 1; // Changé à 1 par défaut

$GLOBALS['ROUTE_CONFIG'] = [
    'max_route_time_minutes' => 120,     // Maximum allowed route time
    'max_route_distance_km' => 150,      // Maximum route distance
    'fuel_efficiency_factor' => 0.3,     // Average fuel consumption per km
    'carbon_emission_factor' => 0.2,     // CO2 emissions per km
];

// Additional function for advanced route analysis
function analyzeRouteEfficiency(array $routeData): array {
    $totalDistance = $routeData['total_distance_meters'] / 1000; // Convert to km
    $totalDuration = $routeData['total_duration_seconds'] / 60;  // Convert to minutes
    
    $fuelConsumption = $totalDistance * $GLOBALS['ROUTE_CONFIG']['fuel_efficiency_factor'];
    $carbonEmission = $totalDistance * $GLOBALS['ROUTE_CONFIG']['carbon_emission_factor'];
    
    $isOptimal = 
        $totalDuration <= $GLOBALS['ROUTE_CONFIG']['max_route_time_minutes'] &&
        $totalDistance <= $GLOBALS['ROUTE_CONFIG']['max_route_distance_km'];
    
    return [
        'is_optimal' => $isOptimal,
        'fuel_consumption_liters' => round($fuelConsumption, 2),
        'carbon_emission_kg' => round($carbonEmission, 2),
        'optimization_details' => [
            'total_distance_km' => round($totalDistance, 2),
            'total_duration_minutes' => round($totalDuration, 2),
            'student_count' => count($routeData['stops_ordered'])
        ]
    ];
}

// Log route performance to database
function logRoutePerformance(int $routeId, array $performanceData) {
    global $conn; // Assuming database connection
    
    $stmt = $conn->prepare("
        INSERT INTO route_performance_logs 
        (route_id, is_optimal, fuel_consumption, carbon_emission, total_distance, total_duration, log_timestamp) 
        VALUES 
        (:route_id, :is_optimal, :fuel_consumption, :carbon_emission, :total_distance, :total_duration, NOW())
    ");
    
    $stmt->execute([
        ':route_id' => $routeId,
        ':is_optimal' => $performanceData['is_optimal'] ? 1 : 0,
        ':fuel_consumption' => $performanceData['fuel_consumption_liters'],
        ':carbon_emission' => $performanceData['carbon_emission_kg'],
        ':total_distance' => $performanceData['optimization_details']['total_distance_km'],
        ':total_duration' => $performanceData['optimization_details']['total_duration_minutes']
    ]);
}

//!========================================================= 

// --- Helper Function for cURL Requests ---
function callOrsApi(string $url, string $apiKey, ?string $postData = null): ?array {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: ' . $apiKey,
        'Content-Type: application/json; charset=utf-8',
        'Accept: application/json, application/geo+json, application/gpx+xml, img/png; charset=utf-8'
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // Verify SSL certificate
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); // 30 seconds timeout

    if ($postData !== null) {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    }

    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        error_log("ORS API cURL Error for $url: " . $error);
        return null;
    }
    if ($httpcode >= 400) {
         error_log("ORS API HTTP Error for $url: Status $httpcode - Response: $response");
         // Try to decode error response from ORS
         $errorData = json_decode($response, true);
         return ['error' => $errorData['error']['message'] ?? "HTTP Error $httpcode", 'http_code' => $httpcode];
    }

    return json_decode($response, true);
}

// --- Main Logic ---
try {
    // DEBUG: Afficher les paramètres de démarrage
    error_log("=== DEBUT DU SCRIPT ===");
    error_log("Route ID demandé: " . $routeId);
    error_log("Base de données: " . $dbname);
    error_log("API Key configurée: " . (empty($orsApiKey) ? 'NON' : 'OUI'));
    
    if ($orsApiKey === 'YOUR_OPENROUTESERVICE_API_KEY' || empty($orsApiKey)) {
         throw new Exception("API Key not configured.");
    }

    // DEBUG: Test de connexion à la base de données
    error_log("Tentative de connexion à la base de données...");
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("SET NAMES utf8"); // Ensure UTF8 for addresses
    error_log("Connexion à la base de données réussie");

    // DEBUG: Vérifier quelles routes existent
    error_log("Vérification des routes disponibles...");
    $stmtCheckRoutes = $conn->prepare("SELECT route_id, route_name FROM routes");
    $stmtCheckRoutes->execute();
    $availableRoutes = $stmtCheckRoutes->fetchAll(PDO::FETCH_ASSOC);
    error_log("Routes disponibles: " . json_encode($availableRoutes));

    // --- 1. Fetch Route Start/End and Students ---
    error_log("Recherche de la route ID: " . $routeId);
    $stmtRoute = $conn->prepare("SELECT start_latitude, start_longitude, end_latitude, end_longitude FROM routes WHERE route_id = :route_id");
    $stmtRoute->bindParam(':route_id', $routeId, PDO::PARAM_INT);
    $stmtRoute->execute();
    $routeInfo = $stmtRoute->fetch(PDO::FETCH_ASSOC);

    error_log("Résultat de la requête route: " . json_encode($routeInfo));

    if (!$routeInfo) {
        error_log("ERREUR: Route non trouvée. Routes disponibles: " . json_encode($availableRoutes));
        throw new Exception("Route not found. Available routes: " . json_encode($availableRoutes));
    }
    
    error_log("Route trouvée: " . json_encode($routeInfo));

    // CORRIGÉ: Utilisation des noms de colonnes de votre table 'eleves'
    error_log("Recherche des étudiants pour la route ID: " . $routeId);
    
    // DEBUG: Vérifier d'abord si des étudiants existent
    $stmtCheckStudents = $conn->prepare("SELECT COUNT(*) as count FROM eleves WHERE route_id = :route_id");
    $stmtCheckStudents->bindParam(':route_id', $routeId, PDO::PARAM_INT);
    $stmtCheckStudents->execute();
    $studentCount = $stmtCheckStudents->fetch(PDO::FETCH_ASSOC);
    error_log("Nombre d'étudiants trouvés pour route_id " . $routeId . ": " . $studentCount['count']);
    
    // DEBUG: Voir tous les étudiants avec leurs route_id
    $stmtAllStudents = $conn->prepare("SELECT id_eleve, nom, route_id FROM eleves");
    $stmtAllStudents->execute();
    $allStudents = $stmtAllStudents->fetchAll(PDO::FETCH_ASSOC);
    error_log("Tous les étudiants dans la base: " . json_encode($allStudents));
    
    // $stmtStudents = $conn->prepare("SELECT e.id_eleve, e.nom, e.adresse, e.latitude, e.longitude , classe.nom_classe FROM eleves e JOIN classe ON eleves.id_classe = classe.id_classe WHERE route_id = :route_id ORDER BY stop_order ASC, id_eleve ASC");
    $stmtStudents = $conn->prepare("SELECT 
        eleves.id_eleve, 
        eleves.nom, 
        eleves.prenom, 
        eleves.id_classe, 
        eleves.adresse, 
        eleves.latitude, 
        eleves.longitude,
        classe.nom_classe
    FROM eleves
    JOIN classe ON eleves.id_classe = classe.id_classe
    WHERE eleves.route_id = :route_id
    ORDER BY eleves.stop_order ASC, eleves.id_eleve ASC");

    $stmtStudents->bindParam(':route_id', $routeId, PDO::PARAM_INT);
    $stmtStudents->execute();
    $students = $stmtStudents->fetchAll(PDO::FETCH_ASSOC);
    
    error_log("Étudiants récupérés: " . json_encode($students));

    if (!$students) {
        error_log("ERREUR: Aucun étudiant trouvé pour cette route. Vérifiez que les étudiants ont route_id = " . $routeId);
        throw new Exception("No students found for this route. Check that students have route_id = " . $routeId);
    }

    $waypoints = []; // Array to hold coordinates for the Directions API [[lon, lat], [lon, lat], ...]
    $studentDetailsMap = []; // Map coordinates back to student details ['lon,lat' => student_data]
    $needsUpdate = []; // Store students needing DB update after geocoding

    // Add Start Point (School/Depot) - ORS uses [longitude, latitude]
    $startPoint = [$routeInfo['start_longitude'], $routeInfo['start_latitude']];
    $waypoints[] = $startPoint;
    error_log("Point de départ ajouté: " . json_encode($startPoint));
    $studentDetailsMap[implode(',', $startPoint)] = ['id_eleve' => 'start', 'name' => 'Start/Depot', 'lat' => $startPoint[1], 'lng' => $startPoint[0]];


    // --- 2. Geocode Students if Necessary ---
    error_log("Début du géocodage des étudiants...");
    foreach ($students as $student) {
        error_log("Traitement de l'étudiant: " . json_encode($student));
        $coords = null;
        if (!empty($student['latitude']) && !empty($student['longitude'])) {
            $coords = [(float)$student['longitude'], (float)$student['latitude']];
             error_log("Using stored coords for student {$student['id_eleve']}: " . implode(',', $coords));
        } else {
            // Geocode required - CORRIGÉ: utilisation de 'adresse' au lieu de 'full_address'
            error_log("Géocodage nécessaire pour l'étudiant {$student['id_eleve']}, adresse: {$student['adresse']}");
            $geocodeUrl = "https://api.openrouteservice.org/geocode/search?api_key={$orsApiKey}&text=" . urlencode($student['adresse']);
            // Optional: Bias search towards Marrakesh
            // $geocodeUrl .= "&boundary.circle.lon=-8.00&boundary.circle.lat=31.63&boundary.circle.radius=50"; // 50km radius approx

            error_log("Geocoding student {$student['id_eleve']}: {$student['adresse']}...");
            error_log("URL de géocodage: " . $geocodeUrl);
            usleep(200000); // Small delay to respect potential free tier rate limits

            $geocodeResult = callOrsApi($geocodeUrl, $orsApiKey);
            error_log("Résultat du géocodage: " . json_encode($geocodeResult));

            if ($geocodeResult && isset($geocodeResult['features'][0]['geometry']['coordinates'])) {
                $coords = $geocodeResult['features'][0]['geometry']['coordinates']; // [lon, lat]
                $needsUpdate[$student['id_eleve']] = ['lat' => $coords[1], 'lng' => $coords[0]];
                 error_log("Geocoded successfully: " . implode(',', $coords));
            } elseif ($geocodeResult && isset($geocodeResult['error'])) {
                 error_log("Geocoding failed for student {$student['id_eleve']}: " . $geocodeResult['error']);
                 // Skip this student or handle error appropriately
                 continue; // Skip this student for routing if geocoding fails
            } else {
                 error_log("Geocoding failed for student {$student['id_eleve']}: Unknown error or no results.");
                 // Skip this student
                 continue;
            }
        }

        if ($coords) {
            $waypoints[] = $coords; // Add [lon, lat] to waypoints
            $coordKey = implode(',', $coords);
            // $studentDetailsMap[$coordKey] = $student;
            //  $studentDetailsMap[$coordKey]['lat'] = $coords[1]; // Ensure lat/lon are set for details map
            //  $studentDetailsMap[$coordKey]['lng'] = $coords[0];
            $studentDetailsMap[$coordKey] = [
            'id_eleve' => $student['id_eleve'],
            'nom' => $student['nom'],
            'prenom' => $student['prenom'],
            'id_classe' => $student['id_classe'],
            'nom_classe' => $student['nom_classe'], // ← CORRECTION: Assurer que nom_classe est inclus
            'adresse' => $student['adresse'],
            'lat' => $coords[1],
            'lng' => $coords[0]
        ];
             error_log("Waypoint ajouté pour l'étudiant {$student['id_eleve']}: " . implode(',', $coords));
        }
    }
    
    error_log("Total des waypoints après traitement des étudiants: " . count($waypoints));

    // Add End Point (usually same as start)
    $endPoint = [$routeInfo['end_longitude'], $routeInfo['end_latitude']];
    // Avoid adding if identical to the last student waypoint AND start point (for circular routes)
    $lastWp = end($waypoints);
    if ($lastWp[0] != $endPoint[0] || $lastWp[1] != $endPoint[1] || count($waypoints) == 1) {
         $waypoints[] = $endPoint;
         $studentDetailsMap[implode(',', $endPoint)] = ['id_eleve' => 'end', 'name' => 'End Point', 'lat' => $endPoint[1], 'lng' => $endPoint[0]];
    }


    // --- 3. Update Database with Newly Geocoded Coordinates ---
    if (!empty($needsUpdate)) {
        $updateStmt = $conn->prepare("UPDATE eleves SET latitude = :lat, longitude = :lng WHERE id_eleve = :id");
        foreach ($needsUpdate as $studentId => $coords) {
             try {
                 $updateStmt->execute([':lat' => $coords['lat'], ':lng' => $coords['lng'], ':id' => $studentId]);
             } catch (PDOException $e) {
                 error_log("DB Update failed for student $studentId: " . $e->getMessage());
                 // Continue execution even if DB update fails, but log it
             }
        }
        error_log("Updated DB for " . count($needsUpdate) . " students.");
    }


    // --- 4. Get Directions from ORS ---
    if (count($waypoints) < 2) {
        throw new Exception("Not enough valid points (at least 2 required) for routing after geocoding.");
    }

    $directionsUrl = "https://api.openrouteservice.org/v2/directions/driving-car/geojson"; // Profile: driving-car
    $postData = json_encode([
        'coordinates' => $waypoints,
        'instructions' => false, // We only need the geometry for drawing
        'geometry' => true,
        // 'preference' => 'shortest' // Or 'fastest'
    ]);

    error_log("Requesting ORS directions with " . count($waypoints) . " waypoints...");
    $directionsResult = callOrsApi($directionsUrl, $orsApiKey, $postData);

    if (!$directionsResult || isset($directionsResult['error']) || !isset($directionsResult['features'][0]['geometry']['coordinates'])) {
         throw new Exception("Failed to get directions from ORS. Error: " . ($directionsResult['error'] ?? 'Unknown ORS directions error'));
    }

    // --- 5. Prepare Response Data ---
    $routeGeometry = $directionsResult['features'][0]['geometry']['coordinates']; // Array of [lon, lat]
    $summary = $directionsResult['features'][0]['properties']['summary'];

    $orderedStops = [];
    // The waypoints are returned in the order they are visited by the route.
    // Match the geometry points back to our original waypoints to get student details in order.
    // Note: The Directions API doesn't re-order stops for optimization, it follows the order you provide.
    // For TSP optimization, ORS has other tools/APIs (Matrix + external solver, or dedicated TSP if available).
     foreach ($waypoints as $wp) { // Use the original waypoint order as ORS Directions follows it
        $coordKey = implode(',', $wp);
        if (isset($studentDetailsMap[$coordKey])) {
             $detail = $studentDetailsMap[$coordKey];
             $orderedStops[] = [
                 'name' => $detail['nom'] ?? $detail['name'] ?? 'Waypoint',
                 'prenom' => $detail['prenom'] ?? $detail['prenom'] ?? 'Waypoint',
                 'lat' => $detail['lat'], // Use the stored/geocoded lat
                 'lng' => $detail['lng'], // Use the stored/geocoded lng
                 'id_classe' => $detail['id_classe'] ?? null,
                'nom_classe' => $detail['nom_classe'] ?? null,
                 'id_eleve' => $detail['id_eleve'] ?? null
             ];
        }
     }

    $responseData = [
        'status' => 'success',
        'route_name' => "Route #{$routeId}",
        'stops_ordered' => $orderedStops, // Stops in the sequence they are routed
        'route_geometry' => array_map(fn($p) => [$p[1], $p[0]], $routeGeometry), // Convert [lon, lat] to [lat, lon] for Leaflet
        'total_distance_meters' => round($summary['distance']),
        'total_duration_seconds' => round($summary['duration'])
    ];

    // OPTIONNEL: Ajouter l'analyse d'efficacité
    $efficiency = analyzeRouteEfficiency($responseData);
    $responseData['efficiency_analysis'] = $efficiency;

    echo json_encode($responseData);
} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    error_log("Error in get_optimized_route.php: " . $e->getMessage());
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    $conn = null; // Close DB connection
}

?>