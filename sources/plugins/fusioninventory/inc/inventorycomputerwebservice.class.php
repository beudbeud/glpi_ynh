<?php

/*
   ------------------------------------------------------------------------
   FusionInventory
   Copyright (C) 2010-2013 by the FusionInventory Development Team.

   http://www.fusioninventory.org/   http://forge.fusioninventory.org/
   ------------------------------------------------------------------------

   LICENSE

   This file is part of FusionInventory project.

   FusionInventory is free software: you can redistribute it and/or modify
   it under the terms of the GNU Affero General Public License as published by
   the Free Software Foundation, either version 3 of the License, or
   (at your option) any later version.

   FusionInventory is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
   GNU Affero General Public License for more details.

   You should have received a copy of the GNU Affero General Public License
   along with FusionInventory. If not, see <http://www.gnu.org/licenses/>.

   ------------------------------------------------------------------------

   @package   FusionInventory
   @author    David Durieux
   @co-author
   @copyright Copyright (c) 2010-2013 FusionInventory team
   @license   AGPL License 3.0 or (at your option) any later version
              http://www.gnu.org/licenses/agpl-3.0-standalone.html
   @link      http://www.fusioninventory.org/
   @link      http://forge.fusioninventory.org/projects/fusioninventory-for-glpi/
   @since     2010

   ------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginFusioninventoryInventoryComputerWebservice {


   /**
   * Method for import XML by webservice
   *
   * @param $params array ID of the agent
   * @param $protocol value the communication protocol used
   *
   * @return array or error value
   *
   **/
   static function loadInventory($params, $protocol) {

      if (isset ($params['help'])) {
         return array('base64'  => 'string, mandatory',
                      'help'    => 'bool, optional');
      }
      if (!isset ($_SESSION['glpiID'])) {
         return PluginWebservicesMethodCommon::Error($protocol, WEBSERVICES_ERROR_NOTAUTHENTICATED);
      }

      $content = base64_decode($params['base64']);

      $pfCommunication = new PluginFusioninventoryCommunication();
      $pfCommunication->handleOCSCommunication($content);

      $msg = __('Computer injected into GLPI', 'fusioninventory');

      return PluginWebservicesMethodCommon::Error($protocol, WEBSERVICES_ERROR_FAILED, '', $msg);
   }



   static function methodExtendedInfo($params, $protocol) {
      $response = array();

      if (!isset($params['computers_id'])
              || !is_numeric($params['computers_id'])) {
         return $response;
      }
      $pfInventoryComputerComputer = new PluginFusioninventoryInventoryComputerComputer();
      $a_computerextend = current($pfInventoryComputerComputer->find(
                                              "`computers_id`='".$params['computers_id']."'",
                                              "", 1));
      if (empty($a_computerextend)) {
         return $response;
      }
      return $a_computerextend;
   }

}

?>
