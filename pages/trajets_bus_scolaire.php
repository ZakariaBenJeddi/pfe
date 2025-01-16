<?php

class Location {
    public $id;
    public $address;
    public $latitude;
    public $longitude;
    
    public function __construct($id, $address, $latitude, $longitude) {
        $this->id = $id;
        $this->address = $address;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }
}

class SchoolBusRouter {
    private $locations = [];
    private $distances = [];
    private $school;
    
    public function __construct(Location $school) {
        $this->school = $school;
        $this->addLocation($school);
    }
    
    public function addLocation(Location $location) {
        $this->locations[$location->id] = $location;
    }
    
    // Calcule la distance entre deux points en utilisant la formule de Haversine
    private function calculateDistance($lat1, $lon1, $lat2, $lon2) {
        $earthRadius = 6371; // Rayon de la Terre en kilomètres
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);
             
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        return $earthRadius * $c;
    }
    
    // Construit la matrice des distances
    private function buildDistanceMatrix() {
        foreach ($this->locations as $loc1) {
            foreach ($this->locations as $loc2) {
                if ($loc1->id !== $loc2->id) {
                    $this->distances[$loc1->id][$loc2->id] = $this->calculateDistance(
                        $loc1->latitude,
                        $loc1->longitude,
                        $loc2->latitude,
                        $loc2->longitude
                    );
                }
            }
        }
    }
    
    public function findOptimalRoute() {
        $this->buildDistanceMatrix();
        
        $unvisited = array_keys($this->locations);
        $currentLocation = $this->school->id;
        $route = [$currentLocation];
        $totalDistance = 0;
        
        // Retire l'école de la liste des points à visiter
        unset($unvisited[array_search($currentLocation, $unvisited)]);
        
        // Tant qu'il reste des arrêts à visiter
        while (!empty($unvisited)) {
            $minDistance = PHP_FLOAT_MAX;
            $nextLocation = null;
            
            // Trouve le point le plus proche
            foreach ($unvisited as $locationId) {
                $distance = $this->distances[$currentLocation][$locationId];
                if ($distance < $minDistance) {
                    $minDistance = $distance;
                    $nextLocation = $locationId;
                }
            }
            
            // Ajoute le point à la route
            $route[] = $nextLocation;
            $totalDistance += $minDistance;
            $currentLocation = $nextLocation;
            
            // Retire le point visité de la liste
            unset($unvisited[array_search($nextLocation, $unvisited)]);
        }
        
        // Retour à l'école
        $totalDistance += $this->distances[$currentLocation][$this->school->id];
        $route[] = $this->school->id;
        
        return [
            'route' => $this->convertRouteToAddresses($route),
            'totalDistance' => round($totalDistance, 2)
        ];
    }
    
    private function convertRouteToAddresses($route) {
        return array_map(function($locationId) {
            return $this->locations[$locationId]->address;
        }, $route);
    }
}

// Exemple d'utilisation// Point de départ : École Al Bachir (près de Guéliz, Marrakech)
// $school = new Location(0, "École Al Bachir", 31.6416, -8.0179);
// $router = new SchoolBusRouter($school);

// // Ajout des adresses des élèves dans différents quartiers de Marrakech
// $router->addLocation(new Location(1, "Quartier Targa", 31.6305, -8.0555));
// $router->addLocation(new Location(2, "Quartier Massira", 31.6154, -8.0456));
// $router->addLocation(new Location(3, "Quartier Hay Mohammadi", 31.6438, -7.9785));
// $router->addLocation(new Location(4, "Quartier Menara", 31.6149, -8.0048));
// $router->addLocation(new Location(5, "Quartier Sidi Ghanem", 31.6422, -8.0601));
// $router->addLocation(new Location(6, "Quartier M'Hamid", 31.5841, -8.0444));
// $router->addLocation(new Location(7, "Quartier Souk Sebt", 31.6237, -7.9758));
// $router->addLocation(new Location(8, "Quartier Ennakhil", 31.6723, -7.9735));
$school = new Location(0, "hotel wazo", 31.677222941442402, -8.001995909092447);//, 
$router = new SchoolBusRouter($school);

$router->addLocation(new Location(1, "marjan", 31.6305, -8.0555));// //31.667369630079044, -8.01302136804142
$router->addLocation(new Location(2, "Quartier Saada", 31.67647677886321, -8.017583803366863));//saada , 
$router->addLocation(new Location(3, "macdo", 31.6550675428885, -8.019257985716997));//macdo //, 
$router->addLocation(new Location(4, "semlalia", 31.649061295011453, -8.015431653280015));//semlalia//, 
$router->addLocation(new Location(5, "college al massar", 31.677229458557793, -8.036302206088129));//college al massar//31.677229458557793, -8.036302206088129


// Calcul de la route optimale
$result = $router->findOptimalRoute();

// Affichage des résultats
echo "Route optimale :\n";
foreach ($result['route'] as $index => $address) {
    echo ($index + 1) . ". " . $address . "<br>";
}
echo "\nDistance totale : " . $result['totalDistance'] . " km";