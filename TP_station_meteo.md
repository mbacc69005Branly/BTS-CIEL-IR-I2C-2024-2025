# TP Arduino : Station météo avec capteurs I2C

## Objectif
Créer une station météo simple utilisant plusieurs capteurs I2C pour mesurer la température, l'humidité et la pression atmosphérique. Les données seront affichées sur un écran LCD I2C.

## Matériel nécessaire
Vous emulerez sur votre machine en utilisant Visual Studio les éléments suivants :
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
  - Wire (pour I2C)
  - Adafruit_BME280
  - Adafruit_TSL2561
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

## Partie 2 : Enregistrement des données dnas un serveur web

### Objectif
Stocker les données de la station météo dans une base de données et les afficher sur une interface web.

### 0. Simulation des données de la station météo

Télécharger le fichier `simulator.php`. Ce fichier va permettre d'émuler l'envoie des données de notre station météo vers le serveur WAMP.

#### Format des données

La station météo envoie des données au format JSON avec la structure suivante :

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

#### Description des champs
- `deviceId`: Identifiant unique de la station météo
- `timestamp`: Timestamp Unix de la mesure
- `data`: Objet contenant les mesures
  - `temperature`: Température en °C
  - `humidity`: Humidité relative en %
  - `pressure`: Pression atmosphérique en hPa
  - `lux`: Luminosité en lux
  - `heatIndex`: Indice de chaleur ressenti en °C

#### Simulation des données

Pour simuler l'envoi de données depuis la station météo, vous pouvez utiliser le script `simulator.php` de deux façons :

1. En ligne de commande :
```bash
php simulator.php
```

2. Via une requête HTTP vers votre serveur :
```bash
curl -X POST -H "Content-Type: application/json" \
     -d "$(php simulator.php)" \
     http://localhost/api/weather-data
```

### 1. Configuration du serveur WAMP
- Téléchargez et installez WAMP (Windows, Apache, MySQL, PHP) depuis le site officiel
- Lancez WAMP et assurez-vous que tous les services (Apache, MySQL) sont en cours d'exécution
- Créez un nouveau fichier PHP dans le répertoire `www` de WAMP (généralement `C:\wamp64\www\`) nommé `receive_data.php` :

```php
<?php
// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = ""; // Laissez vide par défaut pour WAMP
$dbname = "station_meteo";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Récupération des données envoyées par l'Arduino
$temperature = $_POST['temp'] ?? null;
$humidity = $_POST['humidity'] ?? null;
$pressure = $_POST['pressure'] ?? null;
$lux = $_POST['lux'] ?? null;
$heatIndex = $_POST['heatIndex'] ?? null;

// Préparation et exécution de la requête SQL
$sql = ""; // A completer avec la requete INSERT INTO correspondant à votre base de données après la partie 2. Configuration de la base de données MySQL
$stmt = $conn->prepare($sql);
$stmt->bind_param("ddddd", $temperature, $humidity, $pressure, $lux, $heatIndex);

if ($stmt->execute()) {
    echo "Données enregistrées avec succès";
} else {
    echo "Erreur: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
```

### 2. Configuration de la base de données MySQL
- Ouvrez phpMyAdmin depuis le menu WAMP (généralement accessible via http://localhost/phpmyadmin/)
- Créez une nouvelle base de données nommée `station_meteo`
- Dans cette base de données, créez une table nommée `mesures` et choisissez quels doivent en être les champs. Ajoutez ci-dessous la requête permettant de créer la table `mesure`

```sql
MON CODE ICI
```

- Assurez-vous que l'utilisateur 'root' a les permissions nécessaires pour accéder à cette base de données

### 3. Envoi des données au serveur
- Utilisez la bibliothèque HTTPClient pour envoyer des requêtes HTTP
- Créez une fonction pour envoyer les données :
  ```cpp
  void sendDataToServer(float temperature, float humidity, float pressure, float lux, float heatIndex) {
    HTTPClient http;
    String url = "http://votre-serveur.com/api/data";
    String payload = "temp=" + String(temperature) + "&humidity=" + String(humidity) + "&pressure=" + String(pressure) + "&lux=" + String(lux) + "&heatIndex=" + String(heatIndex);
    
    http.begin(client, url);
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");
    int httpCode = http.POST(payload);
    
    if (httpCode > 0) {
      String response = http.getString();
      Serial.println(response);
    }
    http.end();
  }
  ```

### 4. Création d'une interface web simple
- Créez un nouveau fichier `index.php` dans le même répertoire que `receive_data.php` :

```php
<!DOCTYPE html>
<html>
<head>
    <title>Station Météo</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <h1>Données de la Station Météo</h1>
    <canvas id="temperatureChart"></canvas>
    
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

    // Récupération des dernières données
    $sql = "SELECT * FROM mesures ORDER BY timestamp DESC LIMIT 10";
    $result = $conn->query($sql);

    $data = array();
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    $conn->close();
    ?>

    <script>
    var ctx = document.getElementById('temperatureChart').getContext('2d');
    var chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_column(array_reverse($data), 'timestamp')); ?>,
            datasets: [{
                label: 'Température (°C)',
                data: <?php echo json_encode(array_column(array_reverse($data), 'temperature')); ?>,
                borderColor: 'rgb(255, 99, 132)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    </script>
</body>
</html>
```
