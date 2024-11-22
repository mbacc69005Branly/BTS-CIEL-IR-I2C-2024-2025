# TP Arduino : Station météo avec capteurs I2C

## Objectif
Créer une station météo simple utilisant plusieurs capteurs I2C pour mesurer la température, l'humidité et la pression atmosphérique. Les données seront affichées sur un écran LCD I2C.

## Matériel nécessaire
Vous emulerez via Tinkercad les éléments suivants :
- 1 carte Arduino (Uno R3)
- 1 capteur d'humidité Micro:bit
- 1 capteur de température dans la catégorie "Autres composants"
- 1 écran LCD 16x2 avec module I2C

## Branchements
- Connectez tous les dispositifs I2C aux broches SDA et SCL de l'Arduino.
- Alimentez tous les composants en 5V et GND.

## Partie 1

### 1. Configuration de l'environnement
- Installez les bibliothèques nécessaires :
  - LiquidCrystal_I2C

### 2. Programmation
- Initialisez la communication I2C et les capteurs
- Créez des fonctions pour lire les données de chaque capteur
- Affichez les données sur l'écran LCD
- Implémentez une logique pour alterner l'affichage des différentes mesures

### 3. Fonctionnalités avancées
- Calculez et affichez l'indice de confort thermique en utilisant la température et l'humidité

#### Formule de l'indice de confort thermique (Heat Index)

L'indice de confort thermique est une mesure qui combine les effets de la température et de l'humidité sur la sensation de chaleur perçue par le corps humain. La formule mathématique pour calculer cet indice est complexe et basée sur des analyses de régression multiple. Voici une explication de la formule :

Formule simplifiée (pour des conditions modérées) :

> HI = 0.5 * {T + 61.0 + [(T-68.0)1.2] + (RH0.094)}

Où :
- HI est l'indice de chaleur en degrés Fahrenheit
- T est la température en degrés Fahrenheit
- RH est l'humidité relative en pourcentage

Formule complète (pour une plus grande précision, notamment dans des conditions extrêmes) :

> HI = -42.379 + 2.04901523T + 10.14333127RH - 0.22475541TRH - 0.00683783T^2 - 0.05481717RH^2 + 0.00122874T^2RH + 0.00085282TRH^2 - 0.00000199T^2RH^2
Où T et RH sont définis comme précédemment.

Cette formule complète est une équation polynomiale de degré 2 en T et RH, avec des termes d'interaction entre T et RH. Elle prend en compte les effets non linéaires de la température et de l'humidité sur la sensation de chaleur.
Pour utiliser ces formules dans votre projet :

Convertissez d'abord la température de Celsius en Fahrenheit.
Utilisez la formule simplifiée pour un calcul rapide.
Pour une plus grande précision, surtout lorsque la température dépasse 26.7°C (80°F) ou que l'humidité est très élevée, utilisez la formule complète.
Convertissez le résultat de Fahrenheit en Celsius pour l'affichage final.

Dans votre code Arduino, vous devrez implémenter ces formules en tenant compte des limites de précision des calculs en virgule flottante sur les microcontrôleurs.

# Partie 2 : Enregistrement et visualisation des données

## Objectif
Stocker les données simulées de la station météo dans une base de données et les afficher sur une interface web.

## 1. Configuration docker

En vous appuyant sur le cours et les TP de docker, construisez un container docker contenant à minima :
- Apache
- PHP
- MySQL

## 2. Configuration de la base de données
1. Dans phpMyAdmin, créez une nouvelle base de données nommée `station_meteo`
2. Créez la table `mesures` avec la requête SQL qui correspond à ce Modlèe Physique de données :

![image](https://github.com/user-attachments/assets/ba59a1b8-7756-4915-aa2e-6b715772d00e)

## 3. Installation du système de réception des données
1. Dans le répertoire `C:\wamp64\www\`, créez un dossier `station_meteo`
2. Créez le fichier `receive_data.php` dans ce dossier :

```php
<?php
// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "station_meteo";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Récupération du JSON envoyé
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

// Extraction des données
$device_id = $data['deviceId'];
$timestamp = date('Y-m-d H:i:s', $data['timestamp']);
$temperature = $data['data']['temperature'];
$humidity = $data['data']['humidity'];
$pressure = $data['data']['pressure'];
$lux = $data['data']['lux'];
$heat_index = $data['data']['heatIndex'];

// Préparation et exécution de la requête SQL
$sql = "INSERT INTO mesures (device_id, timestamp, temperature, humidity, pressure, lux, heat_index) VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssddddd", $device_id, $timestamp, $temperature, $humidity, $pressure, $lux, $heat_index);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Données enregistrées avec succès']);
} else {
    echo json_encode(['status' => 'error', 'message' => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
```

## 4. Installation du simulateur de données

Téléchargez les fichiers `simulator.php` et `auto_simulate.php` fourni et placez-le dans le dossier `station_meteo`

Le simulateur génère des données au format JSON :
```json
{
    "deviceId": "STATION001",
    "timestamp": 1637589632,
    "data": {
        "temperature": 22.45,
        "humidity": 48.32,
        "pressure": 1012.25,
        "lux": 487.65,
        "heatIndex": 23.12
    }
}
```

## 5. Test du système
Vous avez plusieurs options pour tester le système :

### Option 1 : Via le navigateur
Accédez à l'URL suivante dans votre navigateur :
```
http://localhost/station_meteo/auto_simulate.php?count=5&delay=2
```
- `count` : nombre de mesures à simuler (défaut : 1, max : 100)
- `delay` : délai en secondes entre chaque mesure (défaut : 0)

### Option 2 : Via curl dans le terminal
```bash
curl "http://localhost/station_meteo/auto_simulate.php?count=5&delay=2"
```

### Option 3 : Ligne de commande PHP
Si vous préférez utiliser directement PHP en ligne de commande :
```bash
php simulator.php | curl -X POST -H "Content-Type: application/json" -d @- http://localhost/station_meteo/receive_data.php
```

Vous pouvez vérifier dans phpMyAdmin que les données sont bien enregistrées dans la table `mesures`.

## 6. Création de l'interface de visualisation
Créez le fichier `index.php` dans le dossier `station_meteo` :

```php
<!DOCTYPE html>
<html>
<head>
    <title>Station Météo</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .container { max-width: 800px; margin: 0 auto; padding: 20px; }
        .chart-container { margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Données de la Station Météo</h1>
        
        <div class="chart-container">
            <h2>Température</h2>
            <canvas id="temperatureChart"></canvas>
        </div>
        
        <div class="chart-container">
            <h2>Humidité</h2>
            <canvas id="humidityChart"></canvas>
        </div>
    </div>
    
    <?php
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "station_meteo";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT timestamp, temperature, humidity 
            FROM mesures 
            ORDER BY timestamp DESC 
            LIMIT 20";
    $result = $conn->query($sql);

    $data = array();
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    $conn->close();
    
    // Inverser les données pour l'affichage chronologique
    $data = array_reverse($data);
    ?>

    <script>
    // Données pour les graphiques
    const timestamps = <?php echo json_encode(array_column($data, 'timestamp')); ?>;
    const temperatures = <?php echo json_encode(array_column($data, 'temperature')); ?>;
    const humidities = <?php echo json_encode(array_column($data, 'humidity')); ?>;

    // Configuration commune des graphiques
    const commonOptions = {
        responsive: true,
        scales: {
            y: { beginAtZero: false },
            x: { ticks: { maxRotation: 45, minRotation: 45 } }
        }
    };

    // Graphique de température
    new Chart(document.getElementById('temperatureChart'), {
        type: 'line',
        data: {
            labels: timestamps,
            datasets: [{
                label: 'Température (°C)',
                data: temperatures,
                borderColor: 'rgb(255, 99, 132)',
                tension: 0.1
            }]
        },
        options: commonOptions
    });

    // Graphique d'humidité
    new Chart(document.getElementById('humidityChart'), {
        type: 'line',
        data: {
            labels: timestamps,
            datasets: [{
                label: 'Humidité (%)',
                data: humidities,
                borderColor: 'rgb(54, 162, 235)',
                tension: 0.1
            }]
        },
        options: commonOptions
    });
    </script>
</body>
</html>
```

## 7. Test de l'interface
1. Accédez à `http://localhost/station_meteo/` dans votre navigateur
2. Pour générer des données de test, exécutez plusieurs fois la commande d'envoi :
```bash
php simulator.php | curl -X POST -H "Content-Type: application/json" -d @- http://localhost/station_meteo/receive_data.php
```

## Extensions possibles
- Ajout d'un script pour envoyer automatiquement des données toutes les X secondes
- Création de graphiques supplémentaires pour la pression et la luminosité
- Ajout de statistiques (moyennes, min/max, etc.)
- Mise en place de filtres par période
- Ajout d'alertes si certains seuils sont dépassés
