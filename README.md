PEGASE
======

Développé par le PNE Annuaire et Messagerie/MEDDE

Introduction
------------

Pegase est une application en PHP5 de type doodle. Elle permet de créer des sondages de date ou libre. Les utilisateurs pourront alors répondre au sondage avec des choix de type Oui/Non (et Possible en option).  
Les données de sondage sont stockées dans une base de données PostgreSQL, MySQL ou SQLite et l'authentification s'effectue sur un annuaire LDAP (par défaut). La personnalisation du driver permet de choisir une autre base de données et un autre type d'authentification.

Version
-------

Version 1.1
Build 1606031654

Pré-requis
------------

- Un serveur Web (Apache ou autre)
- PHP 5.4 ou supérieur
- php5-memcache
- php5-pgsql ou php5-mysql ou php5-sqlite
- php5-ldap (si auth LDAP)
- php5-mcrypt

Configuration
-------------

La configuration se trouve dans le dossier config/.
Par défault, Pégase utilise les fichiers 'default' de cette configuration. Pour modifier le choix de la configuration (possibilité d'avoir plusieurs configurations prod, preprod, dev, ...) il faut éditer le fichier config/env.php. Il est possible d'externaliser la configuration (ex dans /etc/pegase) en modifiant la valeur de CONFIGURATION_TYPE_PEGASE à TYPE_EXTERNAL_PEGASE.

- Configuration de l'application dans config/default/ihm.php
- Configuration du serveur LDAP dans config/default/ldap.php
- Configuration du serveur SQL dans config/default/sql.php
- Configuration des logs dans config/default/sql.php

Si driver personnalisé, configuration du driver dans config/default/driver.php

LICENCE
-------

L'application Pegase est distribuée sous licence GPLv3 (http://www.gnu.org/licenses/gpl.html)

Pegase Copyright (C) 2015 PNE Annuaire et Messagerie/MEDDE

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program. If not, see http://www.gnu.org/licenses/.
