PEGASE
======

Développé par le PNE Annuaire et Messagerie/MEDDE

Introduction
------------

Pegase est une application en PHP5 de type doodle type. Elle permet de créer des sondages de date ou libre. Les utilisateurs pourront alors répondre au sondage avec des choix de type Oui/Non.  
Les données de sondage sont stockées dans une base de données PostgreSQL et l'authentification s'effectue sur un annuaire LDAP. La personnalisation du driver permet de choisir une autre base de données et un autre type d'authentification (qui ne sont pas implémentés par défaut).

Version
-------

Version 0.9.1
Build 1505061856
https://github.com/messagerie-melanie2/Pegase/releases/tag/v0.9.1

Pré-requis
------------

Un serveur Web (Apache ou autre)
PHP 5.3 ou suppérieur
php5-memcache
php5-pgsql
php5-ldap
php5-mcrypt

Configuration
-------------

Configuration de l'application dans config/default/ihm.php
Configuration du serveur LDAP dans config/default/ldap.php
Configuration du serveur SQL dans config/default/sql.php
Configuration des logs dans config/default/sql.php

Si driver personnalisé, configuration du driver dans config/default/driver.php

LICENCE
-------

L'application Pegase est distribuée sous licence GPLv3 (http://www.gnu.org/licenses/gpl.html)

Pegase Copyright (C) 2015 PNE Annuaire et Messagerie/MEDDE

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program. If not, see http://www.gnu.org/licenses/.