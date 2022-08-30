# hacking-detector
Script PHP rapide et puissant, à utiliser de préférence dans une tâche cron, pour détecter des fichiers ou des répertories qui ont été modifiés depus l'exécution précédente du script. Envoie un rapport par mail avec tous les changements. Un script PHP pratique et utile pour détecter un hacking, des modifications non autorisées de fichiers, des violiations d'accès au site web. Pas d'utilisation de base de données ni de stockage dans un fichier, le script PHP est autonome.

## Requirements
PHP >= 5.3 

## Langues supportées pour le rapport
FRançais
ENglish

## Installation
Changez les paramètres en fonction de votre configuration et téléchargez simplement le fichier check4change.php dans le répertoire de votre choix sur votre serveur web

### Recommandation de sécurité
Nous vous recommandons de mettre le script dans un répertoire en dehors de la racine de votre site web. Cela rendra l'accès au script par un éventuel hackeur plus difficile.

## Utilisation avec cron
Exécutez simplement le script directement sans paramètres.
Notez qu'en fonction de votre environnement vous devrez spécifier PHP dans la ligne de commande du cron.
```
php -f /my/path/check4change.php
php-cli -f /my/path/check4change.php
/usr/bin/php5 -f /my/path/check4change.php
```

## Configuration
Pour configurer le script en fonction de votre serveur, email, intervalle et langue du rapport, changez simplement les paramètres du constructeur et méthode suivants.
```
$scan = new scanDirectory( dirname(__DIR__), 'FR');
$scan->MailReport( 'emetteur@mydomain.com', 'destinataire@domain.com', 'Alerte modifications: www.votresite.com');
```

###Exemples
Scan du répertoire parent toutes les 10 minutes
```
$scan = new scanDirectory( dirname(__DIR__), 'EN', 600);
```

Scan à partir d'un chemin specifique toutes les 5 minutes
```
$scan = new scanDirectory( '/home/www/mydir', 'FR', 300);
```

Scan ? partir d'un chemin specifique toutes les 10 minutes et exclure des fichiers
```
$scan = new scanDirectory( '/home/www/mydir', 'EN', 600, null, array('log.txt','sitemap.xml'));
```
