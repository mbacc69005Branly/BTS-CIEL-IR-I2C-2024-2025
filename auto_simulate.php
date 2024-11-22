<?php
// Inclusion du simulateur
require_once('simulator.php');

// Paramètres de sécurité basique (optionnel)
$allowed_ips = array('127.0.0.1', '::1');
if (!in_array($_SERVER['REMOTE_ADDR'], $allowed_ips)) {
    die('Accès non autorisé');
}

// Nombre de simulations à effectuer (par défaut 1)
$count = isset($_GET['count']) ? intval($_GET['count']) : 1;
$count = min($count, 100); // Maximum 100 simulations par appel

// Délai entre les simulations en secondes (par défaut 0)
$delay = isset($_GET['delay']) ? intval($_GET['delay']) : 0;

$results = array();

// Création d'une instance du simulateur
$simulator = new WeatherStationSimulator();

// Boucle de simulation
for ($i = 0; $i < $count; $i++) {
    // Génération des données
    $data = $simulator->generateReading();
    
    // Envoi au script de réception
    $ch = curl_init('http://localhost/station_meteo/receive_data.php');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $results[] = json_decode($response, true);
    
    if ($delay > 0 && $i < $count - 1) {
        sleep($delay);
    }
}

// Retour des résultats
header('Content-Type: application/json');
echo json_encode($results);
?>