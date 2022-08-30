# hacking-detector
Script PHP rapide et puissant, � utiliser de pr�f�rence dans une t�che cron, pour d�tecter des fichiers ou des r�pertories qui ont �t� modifi�s depus l'ex�cution pr�c�dente du script. Envoie un rapport par mail avec tous les changements. Un script PHP pratique et utile pour d�tecter un hacking, des modifications non autoris�es de fichiers, des violiations d'acc�s au site web. Pas d'utilisation de base de donn�es ni de stockage dans un fichier, le script PHP est autonome.

## Requirements
PHP >= 5.3 

## Langues support�es pour le rapport
FRan�ais
ENglish

## Installation
Changez les param�tres en fonction de votre configuration et t�l�chargez simplement le fichier check4change.php dans le r�pertoire de votre choix sur votre serveur web

### Recommandation de s�curit�
Nous vous recommandons de mettre le script dans un r�pertoire en dehors de la racine de votre site web. Cela rendra l'acc�s au script par un �ventuel hackeur plus difficile.

## Utilisation avec cron
Ex�cutez simplement le script directement sans param�tres.
Notez qu'en fonction de votre environnement vous devrez sp�cifier PHP dans la ligne de commande du cron.
```
php -f /my/path/check4change.php
php-cli -f /my/path/check4change.php
/usr/bin/php5 -f /my/path/check4change.php
```

## Configuration
Pour configurer le script en fonction de votre serveur, email, intervalle et langue du rapport, changez simplement les param�tres du constructeur et m�thode suivants.
```
$scan = new scanDirectory( dirname(__DIR__), 'FR');
$scan->MailReport( 'emetteur@mydomain.com', 'destinataire@domain.com', 'Alerte modifications: www.votresite.com');
```

###Exemples
Scan du r�pertoire parent toutes les 10 minutes
```
$scan = new scanDirectory( dirname(__DIR__), 'EN', 600);
```

Scan � partir d'un chemin specifique toutes les 5 minutes
```
$scan = new scanDirectory( '/home/www/mydir', 'FR', 300);
```

Scan � partir d'un chemin specifique toutes les 5 minutes et exclure des r�pertoires
```
$scan = new scanDirectory( '/home/www/mydir', 'EN', 300, array('cache','temp'));

```
Scan � partir d'un chemin specifique toutes les 10 minutes et exclure des fichiers
```
$scan = new scanDirectory( '/home/www/mydir', 'EN', 600, null, array('log.txt','sitemap.xml'));
```
