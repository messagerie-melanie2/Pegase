<?php

/**
 * Ce fichier fait parti de l'application de sondage du MEDDE/METL
 * Cette application est un doodle-like permettant aux utilisateurs
 * d'effectuer des sondages sur des dates ou bien d'autres criteres
 *
 * L'application est écrite en PHP5,HTML et Javascript
 * et utilise une base de données postgresql et un annuaire LDAP pour l'authentification
 *
 * @author Thomas Payen
 * @author PNE Annuaire et Messagerie
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
namespace Program\Lib\Request;

class Session_Database implements \SessionHandlerInterface {

  private $ip;
  private $lifetime;
  private $now;
  private $session;

  /**
   * Constructeur par défaut de la classe session_memcache
   */
  public function __construct() {
    if (isset(\Config\IHM::$SESSION_LIFETIME))
      $this->lifetime = \Config\IHM::$SESSION_LIFETIME * 60;
    else
      $this->lifetime = 60 * 60;

    $this->ip = \Program\Lib\Request\Request::get_ip_address();
  }

  /**
   * (non-PHPdoc)
   *
   * @see SessionHandlerInterface::open()
   */
  public function open($save_path, $sessionid) {
    return true;
  }
  /**
   * (non-PHPdoc)
   *
   * @see SessionHandlerInterface::close()
   */
  public function close() {
    return true;
  }

  /**
   * Lit les données de session depuis le support de stockage et retourne le résultat.
   * Appelé juste après que la session démarre ou lorsque session_start() est appelée. Notez qu'avant que cette méthode ne soit appelée, SessionHandlerInterface::open() est invoquée.
   * Cette méthode est appelée par PHP lui-même lorsque la session démarre. Cette méthode devrait retourner les données de session lues depuis le support de stockage en fonction de l'ID de session. La chaine retournée devrait être encodée par le même mécanisme de sérialisation que celui utilisé pour écrire les données lors de SessionHandlerInterface::write(). Si rien n'est lu, une chaine vide est retournée.
   * Les données retournées par cette méthode seront décodées en interne par PHP en utilisant le mécanisme de désérialisation spécifié dans session.serialize_handler. Les données résultantes seront utilisées pour peupler $_SESSION.
   * Notez que l'algorithme de sérialisation peut être différent de unserialize() et peut être utilisé manuellement au moyen de session_decode().
   *
   * @param string $sessionid
   * @return string
   */
  public function read($sessionid) {
    if (isset($this->session)
        && $this->session->session_id == $sessionid) {
      $session = $this->session;
    }
    else {
      $session = \Program\Drivers\Driver::get_driver()->getSession($sessionid);
      $this->session = $session;
    }

    if (isset($session->vars)) {
      return unserialize($session->vars);
    }
    else {
      return "";
    }
  }
  /**
   * Ecrit les données de session dans le support de stockage.
   * Appelé par session_write_close(), lorsque session_register_shutdown() échoue, et aussi durant la phase de terminaison de la requête. Note: SessionHandlerInterface::close() est appelée immediatement après.
   * PHP appelera cette fonction lorsque la session est sur le point d'être sauvegardée et fermée. Il encode les données issues de $_SESSION vers une chaine sérialisée et la passe avec l'ID de session au support de stockage. La fonction de sérialisation est fournie dans session.serialize_handler.
   * Cette méthode est appelée par PHP après qu'il ait fermé les tampons de sortie, sauf si vous l'invoquez vous-même au moyen de session_write_close().
   *
   * @param string $sessionid
   * @param string $sessiondata
   */
  public function write($sessionid, $sessiondata) {
    if (isset($this->session)
        && $this->session->session_id == $sessionid) {
      $session = $this->session;
    }
    else {
      $session = \Program\Drivers\Driver::get_driver()->getSession($sessionid);
      $this->session = $session;
    }
    $this->now = time();
    if (isset($session->vars)) {
      $session->vars = serialize($sessiondata);
      $session->changed = $this->now;
      $this->session = $session;
      return \Program\Drivers\Driver::get_driver()->modifySession($session);
    }
    else {
      $session = new \Program\Data\Session([
              "session_id" => $sessionid,
              "created" => $this->now,
              "changed" => $this->now,
              "ip_address" => $this->ip,
              "vars" => serialize($sessiondata),
      ]);
      $this->session = $session;
      return \Program\Drivers\Driver::get_driver()->createSession($session);
    }
  }
  /**
   * Détruit une session.
   * Appelée par session_regenerate_id() (avec $destroy = TRUE), session_destroy() et lorsque session_decode() échoue.
   *
   * @param string $sessionid
   */
  public function destroy($sessionid) {
    if ($sessionid) {
      unset($this->session);
      \Program\Drivers\Driver::get_driver()->deleteSession($sessionid);
    }
    return true;
  }
  /**
   * Nettoie les vieilles sessions expirées.
   * Appelée par session_start(), en fonction de session.gc_divisor, session.gc_probability et session.gc_lifetime.
   *
   * @see SessionHandlerInterface::gc()
   */
  public function gc($maxlifetime) {
    \Program\Drivers\Driver::get_driver()->deleteOldSessions($maxlifetime);
    return true;
  }
}