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

class OptimizedSchoolBusRouter {
    private $locations = [];
    private $distances = [];
    private $school;
    private $sectors = [];
    
    public function __construct(Location $school) {
        $this->school = $school;
        $this->addLocation($school);
    }
    
    public function addLocation(Location $location) {
        $this->locations[$location->id] = $location;
    }
    
    // Calcule la distance entre deux points en utilisant la formule de Haversine
    private function calculateDistance($lat1, $lon1, $lat2, $lon2) {
        $earthRadius = 6371;
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);
             
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        return $earthRadius * $c;
    }
    
    // Calcule l'angle par rapport à l'école
    private function calculateAngle($location) {
        $dLon = $location->longitude - $this->school->longitude;
        $y = sin($dLon) * cos($location->latitude);
        $x = cos($this->school->latitude) * sin($location->latitude) -
             sin($this->school->latitude) * cos($location->latitude) * cos($dLon);
        
        return atan2($y, $x);
    }
    
    // Organise les points en secteurs autour de l'école
    private function organizeSectors() {
        foreach ($this->locations as $location) {
            if ($location->id === $this->school->id) continue;
            
            $angle = $this->calculateAngle($location);
            $distance = $this->calculateDistance(
                $this->school->latitude,
                $this->school->longitude,
                $location->latitude,
                $location->longitude
            );
            
            // Convertit l'angle en degrés (0-360)
            $degrees = fmod((rad2deg($angle) + 360), 360);
            
            // Divise en 4 secteurs (NE, SE, SW, NW)
            $sector = floor($degrees / 90);
            
            $this->sectors[$sector][] = [
                'location' => $location,
                'distance' => $distance
            ];
        }
        
        // Trie chaque secteur par distance
        foreach ($this->sectors as &$sector) {
            usort($sector, function($a, $b) {
                return $a['distance'] <=> $b['distance'];
            });
        }
    }
    
    public function findOptimalRoute() {
        $this->organizeSectors();
        
        $route = [$this->school];
        $currentLocation = $this->school;
        $totalDistance = 0;
        
        // Parcourt les secteurs dans l'ordre (commençant par le Nord-Est)
        for ($i = 0; $i < 4; $i++) {
            if (!isset($this->sectors[$i])) continue;
            
            // Parcourt les points dans ce secteur
            foreach ($this->sectors[$i] as $point) {
                $distance = $this->calculateDistance(
                    $currentLocation->latitude,
                    $currentLocation->longitude,
                    $point['location']->latitude,
                    $point['location']->longitude
                );
                
                $totalDistance += $distance;
                $route[] = $point['location'];
                $currentLocation = $point['location'];
            }
        }
        
        // Retour à l'école
        $totalDistance += $this->calculateDistance(
            $currentLocation->latitude,
            $currentLocation->longitude,
            $this->school->latitude,
            $this->school->longitude
        );
        $route[] = $this->school;
        
        return [
            'route' => array_map(function($location) {
                return $location->address;
            }, $route),
            'totalDistance' => round($totalDistance, 2)
        ];
    }
}

// Test avec les mêmes points
$school = new Location(0, "hotel wazo", 31.677222941442402, -8.001995909092447);//, 
$router = new OptimizedSchoolBusRouter($school);

$router->addLocation(new Location(1, "marjan", 31.6305, -8.0555));// //31.667369630079044, -8.01302136804142
$router->addLocation(new Location(2, "Quartier Saada", 31.67647677886321, -8.017583803366863));//saada , 
$router->addLocation(new Location(3, "macdo", 31.6550675428885, -8.019257985716997));//macdo //, 
$router->addLocation(new Location(4, "semlalia", 31.649061295011453, -8.015431653280015));//semlalia//, 
$router->addLocation(new Location(5, "college al massar", 31.677229458557793, -8.036302206088129));//college al massar//31.677229458557793, -8.036302206088129
// $router->addLocation(new Location(6, "Quartier M'Hamid", 31.5841, -8.0444));
// $router->addLocation(new Location(7, "Quartier Souk Sebt", 31.6237, -7.9758));
// $router->addLocation(new Location(8, "Quartier Ennakhil", 31.6723, -7.9735));

$result = $router->findOptimalRoute();

echo "Route optimale :\n";
foreach ($result['route'] as $index => $address) {
    echo ($index + 1) . ". " . $address . "<br>";
}
echo "\nDistance totale : " . $result['totalDistance'] . " km";