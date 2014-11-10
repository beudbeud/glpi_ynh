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

function pluginFusioninventoryInstall($version, $migration='') {
   global $DB;

   ini_set("memory_limit", "-1");
   ini_set("max_execution_time", "0");

   
   if ($migration == '') {
      $migration = new Migration($version);
   }

   /*
    * Load classes
    */
   require_once(GLPI_ROOT . "/plugins/fusioninventory/inc/profile.class.php");
   require_once(GLPI_ROOT . "/plugins/fusioninventory/inc/agentmodule.class.php");
   require_once(GLPI_ROOT . "/plugins/fusioninventory/inc/staticmisc.class.php");
   require_once(GLPI_ROOT . "/plugins/fusioninventory/inc/setup.class.php");
   require_once(GLPI_ROOT . "/plugins/fusioninventory/inc/unknowndevice.class.php");
   require_once(GLPI_ROOT . "/plugins/fusioninventory/inc/inventoryruleimportcollection.class.php");
   require_once(GLPI_ROOT . "/plugins/fusioninventory/inc/inventoryruleimport.class.php");
   require_once(GLPI_ROOT . "/plugins/fusioninventory/inc/module.class.php");
   require_once(GLPI_ROOT . "/plugins/fusioninventory/inc/mapping.class.php");
   require_once(GLPI_ROOT . "/plugins/fusioninventory/inc/config.class.php");
   require_once(GLPI_ROOT . "/plugins/fusioninventory/inc/communicationrest.class.php");
   require_once GLPI_ROOT . "/plugins/fusioninventory/inc/lock.class.php";
   require_once GLPI_ROOT . "/plugins/fusioninventory/inc/inventorycomputerlib.class.php";
   require_once GLPI_ROOT . "/plugins/fusioninventory/inc/inventorycomputerlibhook.class.php";
   require_once(GLPI_ROOT . "/plugins/fusioninventory/inc/snmpmodel.class.php");
   require_once(GLPI_ROOT . "/plugins/fusioninventory/inc/snmpmodeldevice.class.php");
   require_once(GLPI_ROOT . "/plugins/fusioninventory/inc/snmpmodelmib.class.php");
   require_once(GLPI_ROOT . "/plugins/fusioninventory/inc/snmpmodelmiblabel.class.php");
   require_once(GLPI_ROOT . "/plugins/fusioninventory/inc/snmpmodelmibobject.class.php");
   require_once(GLPI_ROOT . "/plugins/fusioninventory/inc/snmpmodelmiboid.class.php");
   require_once(GLPI_ROOT . "/plugins/fusioninventory/inc/snmpmodelimportexport.class.php");
   require_once(GLPI_ROOT . "/plugins/fusioninventory/inc/networkequipment.class.php");
   require_once(GLPI_ROOT . "/plugins/fusioninventory/inc/printer.class.php");
   require_once(GLPI_ROOT . "/plugins/fusioninventory/inc/configlogfield.class.php");
   require_once(GLPI_ROOT . "/plugins/fusioninventory/inc/networkporttype.class.php");
   require_once(GLPI_ROOT . "/plugins/fusioninventory/inc/toolbox.class.php");

   $migration->displayMessage("Installation of plugin FusionInventory");


   // Get informations of plugin

   /*
    * Clean if Fusion / Tracker has been installed and uninstalled (not clean correctly)
    */
      $migration->displayMessage("Clean data from old installation of the plugin");
      $sql = "DELETE FROM `glpi_displaypreferences`
         WHERE `itemtype`='5150'";
      $DB->query($sql);
      $sql = "DELETE FROM `glpi_displaypreferences`
         WHERE `itemtype`='5151'";
      $DB->query($sql);
      $sql = "DELETE FROM `glpi_displaypreferences`
         WHERE `itemtype`='5152'";
      $DB->query($sql);
      $sql = "DELETE FROM `glpi_displaypreferences`
         WHERE `itemtype`='5153'";
      $DB->query($sql);
      $sql = "DELETE FROM `glpi_displaypreferences`
         WHERE `itemtype`='5156'";
      $DB->query($sql);
      $sql = "DELETE FROM `glpi_displaypreferences`
         WHERE `itemtype`='5157'";
      $DB->query($sql);
      $sql = "DELETE FROM `glpi_displaypreferences`
         WHERE `itemtype`='5158'";
      $DB->query($sql);
      $sql = "DELETE FROM `glpi_displaypreferences`
         WHERE `itemtype`='5159'";
      $DB->query($sql);
      $sql = "DELETE FROM `glpi_displaypreferences`
         WHERE `itemtype`='5161'";
      $DB->query($sql);
      $sql = "DELETE FROM `glpi_displaypreferences`
         WHERE `itemtype`='5165'";
      $DB->query($sql);
      $sql = "DELETE FROM `glpi_displaypreferences`
         WHERE `itemtype`='5166'";
      $DB->query($sql);
      $sql = "DELETE FROM `glpi_displaypreferences`
         WHERE `itemtype`='5167'";
      $DB->query($sql);
      $sql = "DELETE FROM `glpi_displaypreferences`
         WHERE `itemtype`='5168'";
      $DB->query($sql);
      $sql = "DELETE FROM `glpi_displaypreferences`
         WHERE `itemtype` LIKE 'PluginFusioninventory%'";
      $DB->query($sql);
      $sql = "DELETE FROM `glpi_displaypreferences`
         WHERE `itemtype` LIKE 'PluginFusinvinventory%'";
      $DB->query($sql);
      $sql = "DELETE FROM `glpi_displaypreferences`
         WHERE `itemtype` LIKE 'PluginFusinvsnmp%'";
      $DB->query($sql);
         // Purge network ports have itemtype tp 5153
         $networkPort = new NetworkPort();
         $sql = "SELECT * FROM `glpi_networkports`
            WHERE `itemtype`='5153'";
         $result=$DB->query($sql);
         while ($data=$DB->fetch_array($result)) {
            $networkPort->delete(array('id'=>$data['id']), 1);
         }


   /*
    * Remove old rules
    */
      $migration->displayMessage("Clean rules from old installation of the plugin");
      $Rule = new Rule();
      $a_rules = $Rule->find("`sub_type`='PluginFusioninventoryInventoryRuleImport'");
      foreach ($a_rules as $data) {
         $Rule->delete($data);
      }
      $a_rules = $Rule->find("`sub_type`='PluginFusinvinventoryRuleEntity'");
      foreach ($a_rules as $data) {
         $Rule->delete($data);
      }

      $a_rules = $Rule->find("`sub_type`='PluginFusinvinventoryRuleLocation'");
      foreach ($a_rules as $data) {
         $Rule->delete($data);
      }


   /*
    * Create DB structure
    */
      $migration->displayMessage("Creation tables in database");
      $DB_file = GLPI_ROOT ."/plugins/fusioninventory/install/mysql/plugin_fusioninventory-empty.sql";
      if (!$DB->runFile($DB_file)) {
         $migration->displayMessage("Error on creation tables in database");
      }
      if (!$DB->runFile(GLPI_ROOT ."/plugins/fusioninventory/install/mysql/usbid.sql")) {
         $migration->displayMessage("Error on creation table usbid in database");
      }
      if (!$DB->runFile(GLPI_ROOT ."/plugins/fusioninventory/install/mysql/pciid.sql")) {
         $migration->displayMessage("Error on creation table pciid in database");
      }
      if (!$DB->runFile(GLPI_ROOT ."/plugins/fusioninventory/install/mysql/oui.sql")) {
         $migration->displayMessage("Error on creation table oui in database");
      }

   /*
    * Creation of folders
    */
      $migration->displayMessage("Creation of folders");
      if (!is_dir(GLPI_PLUGIN_DOC_DIR.'/fusioninventory')) {
         mkdir(GLPI_PLUGIN_DOC_DIR.'/fusioninventory');
      }
      if (!is_dir(GLPI_PLUGIN_DOC_DIR.'/fusioninventory/tmp')) {
         mkdir(GLPI_PLUGIN_DOC_DIR.'/fusioninventory/tmp');
      }
      if (!is_dir(GLPI_PLUGIN_DOC_DIR.'/fusioninventory/xml')) {
         mkdir(GLPI_PLUGIN_DOC_DIR.'/fusioninventory/xml');
      }
      if (!is_dir(GLPI_PLUGIN_DOC_DIR.'/fusioninventory/xml/computer')) {
         mkdir(GLPI_PLUGIN_DOC_DIR.'/fusioninventory/xml/computer');
      }
      if (!is_dir(GLPI_PLUGIN_DOC_DIR.'/fusioninventory/xml/printer')) {
         mkdir(GLPI_PLUGIN_DOC_DIR.'/fusioninventory/xml/printer');
      }
      if (!is_dir(GLPI_PLUGIN_DOC_DIR.'/fusioninventory/xml/networkequipment')) {
         mkdir(GLPI_PLUGIN_DOC_DIR.'/fusioninventory/xml/networkequipment');
      }
      if (!is_dir(GLPI_PLUGIN_DOC_DIR.'/fusioninventory/upload')) {
         mkdir(GLPI_PLUGIN_DOC_DIR.'/fusioninventory/upload');
      }
      if (!is_dir(GLPI_PLUGIN_DOC_DIR.'/fusioninventory/walks')) {
         mkdir(GLPI_PLUGIN_DOC_DIR.'/fusioninventory/walks');
      }
      if (!is_dir(GLPI_PLUGIN_DOC_DIR.'/fusioninventory/tmpmodels')) {
         mkdir(GLPI_PLUGIN_DOC_DIR.'/fusioninventory/tmpmodels');
      }
   /*
    * Deploy folders
    */
      if (!is_dir(GLPI_PLUGIN_DOC_DIR.'/fusioninventory/files')) {
         mkdir(GLPI_PLUGIN_DOC_DIR.'/fusioninventory/files');
      }
      if (!is_dir(GLPI_PLUGIN_DOC_DIR.'/fusioninventory/files/repository')) {
         mkdir(GLPI_PLUGIN_DOC_DIR.'/fusioninventory/files/repository');
      }
      if (!is_dir(GLPI_PLUGIN_DOC_DIR.'/fusioninventory/files/manifests')) {
         mkdir(GLPI_PLUGIN_DOC_DIR.'/fusioninventory/files/manifests');
      }

   /*
    * Manage profiles
    */
      $migration->displayMessage("Initialize profiles");
      PluginFusioninventoryProfile::initProfile('fusioninventory');



   /*
    * bug of purge network port when purge unknown devices, so we clean
    */
      $sql = "SELECT `glpi_networkports`.`id` as nid FROM `glpi_networkports`
         LEFT JOIN `glpi_plugin_fusioninventory_unknowndevices`
            ON `glpi_plugin_fusioninventory_unknowndevices`.`id` = `glpi_networkports`.`items_id`
         WHERE `itemtype`='PluginFusioninventoryUnknownDevice'
            AND `glpi_plugin_fusioninventory_unknowndevices`.`id` IS NULL ";
      $result=$DB->query($sql);
      while ($data=$DB->fetch_array($result)) {
         $networkPort->delete(array('id'=>$data['nid']), 1);
      }


   /*
    * Add config
    */
      $migration->displayMessage("Initialize configuration");
      $pfConfig = new PluginFusioninventoryConfig();
      $pfConfig->initConfigModule();

      $configLogField = new PluginFusioninventoryConfigLogField();
      $configLogField->initConfig();




   /*
    * Register Agent TASKS
    */
      $migration->displayMessage("Initialize agent TASKS");
      PluginFusioninventoryProfile::changeProfile();
      $pfAgentmodule = new PluginFusioninventoryAgentmodule();
      $input = array();
      $input['modulename'] = "WAKEONLAN";
      $input['is_active']  = 0;
      $input['exceptions'] = exportArrayToDB(array());
      $pfAgentmodule->add($input);

      $input = array();
      $input['modulename'] = "INVENTORY";
      $input['is_active']  = 1;
      $input['exceptions'] = exportArrayToDB(array());
      $pfAgentmodule->add($input);

      $input = array();
      $input['modulename'] = "InventoryComputerESX";
      $input['is_active']  = 0;
      $input['exceptions'] = exportArrayToDB(array());
      $pfAgentmodule->add($input);

      $input = array();
      $input['modulename'] = "NETWORKINVENTORY";
      $input['is_active']  = 0;
      $input['exceptions'] = exportArrayToDB(array());
      $pfAgentmodule->add($input);

      $input = array();
      $input['modulename'] = "NETWORKDISCOVERY";
      $input['is_active']  = 0;
      $input['exceptions'] = exportArrayToDB(array());
      $pfAgentmodule->add($input);

      $input = array();
      $input['modulename'] = "DEPLOY";
      $input['is_active']  = 0;
      $input['exceptions'] = exportArrayToDB(array());
      $pfAgentmodule->add($input);

      $input = array();
      $input['modulename'] = "Collect";
      $input['is_active']  = 1;
      $input['exceptions'] = exportArrayToDB(array());
      $pfAgentmodule->add($input);


   /*
    * Add cron task
    */
      $migration->displayMessage("Initialize cron task");
      CronTask::Register('PluginFusioninventoryTaskjob', 'taskscheduler', '60',
                         array('mode' => 2, 'allowmode' => 3, 'logs_lifetime'=> 30));
      Crontask::Register('PluginFusioninventoryTaskjobstate', 'cleantaskjob', (3600 * 24),
                         array('mode' => 2, 'allowmode' => 3, 'logs_lifetime' => 30));
      Crontask::Register('PluginFusioninventoryNetworkPortLog', 'cleannetworkportlogs', (3600 * 24),
                         array('mode'=>2, 'allowmode'=>3, 'logs_lifetime'=>30));
      CronTask::Register('PluginFusioninventoryTaskjob', 'updatedynamictasks', '60',
                         array('mode' => 2, 'allowmode' => 3, 'logs_lifetime'=> 30, 'state' => 0));

   /*
    * Create rules
    */
      $migration->displayMessage("Create rules");
      $pfSetup = new PluginFusioninventorySetup();
      $pfSetup->initRules();



   /*
    *  Import OCS locks
    */
      $migration->displayMessage("Import OCS locks if exists");
      $pfLock = new PluginFusioninventoryLock();
      $pfLock->importFromOcs();


   CronTask::Register('PluginFusioninventoryTaskjob', 'taskscheduler', '60',
                      array('mode' => 2, 'allowmode' => 3, 'logs_lifetime'=> 30));
   Crontask::Register('PluginFusioninventoryTaskjobstate', 'cleantaskjob', (3600 * 24),
                      array('mode' => 2, 'allowmode' => 3, 'logs_lifetime' => 30));


   $pfNetworkporttype = new PluginFusioninventoryNetworkporttype();
   $pfNetworkporttype->init();

   // Import models
   PluginFusioninventorySnmpmodel::importAllModels();

}

?>
