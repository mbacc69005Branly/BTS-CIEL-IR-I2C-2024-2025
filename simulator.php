<?php
// simulator.php
class WeatherStationSimulator {
    private $lastValues = [
        'temperature' => 20.0,
        'humidity' => 50.0,
        'pressure' => 1013.25,
        'lux' => 500.0
    ];
    
    private function randomFloat($min, $max) {
        return $min + mt_rand() / mt_getrandmax() * ($max - $min);
    }
    
    private function calculateHeatIndex($temperature, $humidity) {
        // Conversion en Fahrenheit pour la formule
        $tempF = ($temperature * 9/5) + 32;
        
        // Formule simplifiée de l'indice de chaleur
        $hi = 0.5 * ($tempF + 61.0 + (($tempF-68.0)*1.2) + ($humidity*0.094));
        
        // Reconversion en Celsius
        return ($hi - 32) * 5/9;
    }
    
    public function generateReading() {
        // Simule des variations réalistes
        $this->lastValues['temperature'] += $this->randomFloat(-0.5, 0.5);
        $this->lastValues['humidity'] += $this->randomFloat(-2, 2);
        $this->lastValues['pressure'] += $this->randomFloat(-1, 1);
        $this->lastValues['lux'] += $this->randomFloat(-50, 50);
        
        // Garde les valeurs dans des plages réalistes
        $this->lastValues['temperature'] = max(min($this->lastValues['temperature'], 40), -10);
        $this->lastValues['humidity'] = max(min($this->lastValues['humidity'], 100), 0);
        $this->lastValues['pressure'] = max(min($this->lastValues['pressure'], 1100), 900);
        $this->lastValues['lux'] = max(min($this->lastValues['lux'], 1500), 0);
        
        // Calcule l'indice de chaleur
        $heatIndex = $this->calculateHeatIndex(
            $this->lastValues['temperature'], 
            $this->lastValues['humidity']
        );
        
        // Formate la réponse comme une trame JSON
        return json_encode([
            'deviceId' => 'STATION001',
            'timestamp' => time(),
            'data' => [
                'temperature' => round($this->lastValues['temperature'], 2),
                'humidity' => round($this->lastValues['humidity'], 2),
                'pressure' => round($this->lastValues['pressure'], 2),
                'lux' => round($this->lastValues['lux'], 2),
                'heatIndex' => round($heatIndex, 2)
            ]
        ]);
    }
}

// Point d'entrée pour la simulation
if (php_sapi_name() === 'cli') {
    $simulator = new WeatherStationSimulator();
    echo $simulator->generateReading() . "\n";
}
?>
