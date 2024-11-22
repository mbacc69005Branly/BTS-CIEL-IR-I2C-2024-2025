# TP : Développement d'une calculatrice avec un écran LCD via I2C

## Objectif

Dans ce TP, vous allez apprendre à utiliser le protocole I2C pour interfacer un écran LCD avec un microcontrôleur (Arduino). L'objectif final est de programmer une calculatrice simple qui s'affichera sur l'écran LCD.

### Matériel nécessaire

* 1 carte Arduino (Uno, Mega, etc.)
* 1 écran LCD avec interface I2C (LiquidCrystal_I2C)
* Câbles de connexion
* Résistances de pull-up (4.7kΩ ou 10kΩ si nécessaires)

## Contexte

L'interface I2C est largement utilisée pour connecter des composants comme des capteurs, des mémoires ou des afficheurs à des microcontrôleurs. Dans ce TP, vous allez utiliser un écran LCD 16x2 qui communique avec votre Arduino via I2C.

Le but est de développer un programme qui permet à l'utilisateur d'effectuer des calculs (addition, soustraction, multiplication, division) en entrant des nombres via le moniteur série de l'IDE Arduino, et de voir les résultats affichés sur l'écran LCD.

## Étapes

### 1. Crée un nouveau circuit sur [Tinkercad](https://www.tinkercad.com/).

### 2. Connexion de l'écran LCD à l'Arduino

Utilisez les broches **SDA** et **SCL** de l'Arduino pour connecter votre écran LCD. Ces broches varient selon le modèle d'Arduino :

* **Arduino Uno** : SDA -> A4, SCL -> A5

N'oubliez pas de connecter les broches d'alimentation (VCC et GND) et d'ajouter les résistances de pull-up sur les lignes SDA et SCL si nécessaire.

![image](https://github.com/user-attachments/assets/85a83573-db7b-4f82-b9d3-eb25afb41467)

### 3. Initialisation de la bibliothèque I2C

Pour faciliter l'utilisation de l'écran LCD via I2C, nous allons utiliser la bibliothèque LiquidCrystal_I2C. Dans votre code Arduino, vous devrez inclure cette bibliothèque et initialiser l'écran avec la bonne adresse I2C.

```cpp
#include <Wire.h>
#include <LiquidCrystal_I2C.h>

// Création d'un objet LCD avec l'adresse I2C de l'écran
LiquidCrystal_I2C lcd(0x27, 16, 2);
```

Vérifiez bien l'adresse I2C de votre écran et que le type soit paramétré sur PCF si vous avez des doutes.

### 4. Initialisation de l'écran LCD

Afin d'utiliser l'écran, il faut l'initialiser de la façon suivante :

```cpp
lcd.begin(16,2);
lcd.init();
lcd.backlight();
```

### 5. Développement de la calculatrice

Votre calculatrice devra fonctionner selon les étapes suivantes :

1. L'utilisateur entre le premier nombre via le moniteur série.
2. L'utilisateur entre le second nombre.
3. L'utilisateur choisit une opération (+, -, *, /).
4. Le résultat est calculé et affiché sur l'écran LCD.
5. Le programme demande à l'utilisateur s'il veut effectuer une nouvelle opération.

Le programme devra gérer les erreurs, comme la division par zéro, et afficher un message d'erreur sur le LCD si cela se produit.

### Bonus

* Améliorer l'affichage avec des messages plus détaillés ou des symboles.
* Ajouter la possibilité de calculs en chaîne sans réinitialiser les valeurs.

### Pull Request

Votre pull request contiendra deux fichiers :
- Un fichier .ino avec le code Arduino
- Un fichier .brd avec votre montage
