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
   it under the termas of the GNU Affero General Public License as published by
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

function pluginFusioninventoryGetCurrentVersion() {
   global $DB;

   require_once(GLPI_ROOT . "/plugins/fusioninventory/inc/module.class.php");

   if ((!TableExists("glpi_plugin_tracker_config")) &&
      (!TableExists("glpi_plugin_fusioninventory_config")) &&
      (!TableExists("glpi_plugin_fusioninventory_configs"))) {
      return '0';
   } else if ((TableExists("glpi_plugin_tracker_config")) ||
         (TableExists("glpi_plugin_fusioninventory_config"))) {

      if (TableExists("glpi_plugin_fusioninventory_configs")) {
         $query = "SELECT `value` FROM `glpi_plugin_fusioninventory_configs`
            WHERE `type`='version'
            LIMIT 1";

         $data = array();
         if ($result=$DB->query($query)) {
            if ($DB->numrows($result) == "1") {
               $data = $DB->fetch_assoc($result);
               return $data['value'];
            }
         }
      }

      if ((!TableExists("glpi_plugin_tracker_agents")) &&
         (!TableExists("glpi_plugin_fusioninventory_agents"))) {
         return "1.1.0";
      }
      if ((!TableExists("glpi_plugin_tracker_config_discovery")) &&
         (!TableExists("glpi_plugin_fusioninventory_config"))) {
         return "2.0.0";
      }
      if (((TableExists("glpi_plugin_tracker_agents")) &&
           (!FieldExists("glpi_plugin_tracker_config", "version"))) &&
         (!TableExists("glpi_plugin_fusioninventory_config"))) {
         return "2.0.1";
      }
      if (((TableExists("glpi_plugin_tracker_agents")) &&
           (FieldExists("glpi_plugin_tracker_config", "version"))) ||
         (TableExists("glpi_plugin_fusioninventory_config"))) {

         $query = "";
         if (TableExists("glpi_plugin_tracker_agents")) {
            $query = "SELECT version FROM glpi_plugin_tracker_config LIMIT 1";
         } else if (TableExists("glpi_plugin_fusioninventory_config")) {
            $query = "SELECT version FROM glpi_plugin_fusioninventory_config LIMIT 1";
         }

         $data = array();
         if ($result=$DB->query($query)) {
            if ($DB->numrows($result) == "1") {
               $data = $DB->fetch_assoc($result);
            }
         }

         if  ($data['version'] == "0") {
            return "2.0.2";
         } else {
            return $data['version'];
         }
      }
   } else if (TableExists("glpi_plugin_fusioninventory_configs")) {
      $query = "SELECT `value` FROM `glpi_plugin_fusioninventory_configs`
         WHERE `type`='version'
         LIMIT 1";

      $data = array();
      if ($result=$DB->query($query)) {
         if ($DB->numrows($result) == "1") {
            $data = $DB->fetch_assoc($result);
            return $data['value'];
         }
      }
      $query = "SELECT `plugins_id` FROM `glpi_plugin_fusioninventory_agentmodules`
         WHERE `modulename`='WAKEONLAN'
         LIMIT 1";
      if ($result=$DB->query($query)) {
         if ($DB->numrows($result) == "1") {
            $ex_pluginid = $DB->fetch_assoc($result);

            $query = "UPDATE `glpi_plugin_fusioninventory_taskjobs`
               SET `plugins_id`='".PluginFusioninventoryModule::getModuleId('fusioninventory')."'
                  WHERE `plugins_id`='".$ex_pluginid['plugins_id']."'";
            $DB->query($query);
            $query = "UPDATE `glpi_plugin_fusioninventory_profiles`
               SET `plugins_id`='".PluginFusioninventoryModule::getModuleId('fusioninventory')."'
                  WHERE `plugins_id`='".$ex_pluginid['plugins_id']."'";
            $DB->query($query);
            $query = "UPDATE `glpi_plugin_fusioninventory_agentmodules`
               SET `plugins_id`='".PluginFusioninventoryModule::getModuleId('fusioninventory')."'
                  WHERE `plugins_id`='".$ex_pluginid['plugins_id']."'";
            $DB->query($query);

            $query = "SELECT `value` FROM `glpi_plugin_fusioninventory_configs`
               WHERE `type`='version'
               LIMIT 1";

            $data = array();
            if ($result=$DB->query($query)) {
               if ($DB->numrows($result) == "1") {
                  $data = $DB->fetch_assoc($result);
                  return $data['value'];
               }
            }
         }
      }
   }

}



/*
 * find files recursively filtered with pattern
 * (grabbed from http://rosettacode.org/wiki/Walk_a_directory/Recursively#PHP)
 */
function pluginFusioninventoryFindFiles($dir = '.', $pattern = '/./') {
   $files = array();
   $prefix = $dir . '/';
   $dir = dir($dir);
   while (FALSE !== ($file = $dir->read())){
      if ($file === '.' || $file === '..') {
         continue;
      }
      $file = $prefix . $file;
      if (is_dir($file)) {
         $files[] = pluginFusioninventoryFindFiles($file, $pattern);
         continue;
      }
      if (preg_match($pattern, $file)){
          $files[] = $file;
      }
   }

   return pluginFusioninventoryFlatArray($files);
}



function pluginFusioninventoryFlatArray($array) {
   $tmp = array();
   foreach($array as $a) {
      if ( is_array($a) ) {
         $tmp = array_merge($tmp, pluginFusioninventoryFlatArray($a));
      } else {
         $tmp[] = $a;
      }
   }
   return $tmp;
}



function pluginFusioninventoryUpdate($current_version, $migrationname='Migration') {
   global $DB;

   ini_set("max_execution_time", "0");
   ini_set("memory_limit", "-1");


   foreach (glob(GLPI_ROOT.'/plugins/fusioninventory/inc/*.php') as $file) {
      require_once($file);
   }

   $migration = new $migrationname($current_version);
   $prepare_task = array();
   $prepare_rangeip = array();
   $prepare_Config = array();

   $a_plugin = plugin_version_fusioninventory();
   $plugins_id = PluginFusioninventoryModule::getModuleId($a_plugin['shortname']);

   $migration->displayMessage("Update of plugin FusionInventory");

   /*
    * Check if folders are correctly created
    */
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
   if (!is_dir(GLPI_PLUGIN_DOC_DIR.'/fusioninventory/walks')) {
      mkdir(GLPI_PLUGIN_DOC_DIR.'/fusioninventory/walks');
   }
   if (!is_dir(GLPI_PLUGIN_DOC_DIR.'/fusioninventory/tmpmodels')) {
      mkdir(GLPI_PLUGIN_DOC_DIR.'/fusioninventory/tmpmodels');
   }
   /*
    * Deploy folders
    */

   if (is_dir(GLPI_PLUGIN_DOC_DIR.'/fusinvdeploy/files')) {
      rename(
         GLPI_PLUGIN_DOC_DIR.'/fusinvdeploy/files',
         GLPI_PLUGIN_DOC_DIR.'/fusioninventory/files'
      );
   }
   if (!is_dir(GLPI_PLUGIN_DOC_DIR.'/fusioninventory/files')) {
      mkdir(GLPI_PLUGIN_DOC_DIR.'/fusioninventory/files');
   }


   if (is_dir(GLPI_PLUGIN_DOC_DIR.'/fusinvdeploy/repository')) {
      rename(
         GLPI_PLUGIN_DOC_DIR.'/fusinvdeploy/repository',
         GLPI_PLUGIN_DOC_DIR.'/fusioninventory/repository'
      );
   }
   if (!is_dir(GLPI_PLUGIN_DOC_DIR.'/fusioninventory/files/repository')) {
      mkdir(GLPI_PLUGIN_DOC_DIR.'/fusioninventory/files/repository');
   }

   if (!is_dir(GLPI_PLUGIN_DOC_DIR.'/fusioninventory/files/manifests')) {
      mkdir(GLPI_PLUGIN_DOC_DIR.'/fusioninventory/files/manifests');
   }

   if (is_dir(GLPI_PLUGIN_DOC_DIR.'/fusinvdeploy/upload')) {
      rename(
         GLPI_PLUGIN_DOC_DIR.'/fusinvdeploy/upload',
         GLPI_PLUGIN_DOC_DIR.'/fusioninventory/upload'
      );
   }
   if (!is_dir(GLPI_PLUGIN_DOC_DIR.'/fusioninventory/upload')) {
      mkdir(GLPI_PLUGIN_DOC_DIR.'/fusioninventory/upload');
   }



   /*
    * Rename fileparts without .gz extension (cf #1999)
    */
   if ( is_dir(GLPI_PLUGIN_DOC_DIR.'/fusioninventory/files') ) {
      $gzfiles = pluginFusioninventoryFindFiles(GLPI_PLUGIN_DOC_DIR.'/fusioninventory/files', '/\.gz$/');
      foreach($gzfiles as $file) {
         $fileWithoutExt =
            pathinfo($file, PATHINFO_DIRNAME) .
            '/' . pathinfo($file, PATHINFO_FILENAME);

         rename($file, $fileWithoutExt);
      }
   }
   unset($gzfiles);

   /*
    *  Rename tables from old version of FuionInventory (2.2.1 for example)
    */
   $migration->renameTable("glpi_plugin_fusioninventory_rangeip",
                           "glpi_plugin_fusioninventory_ipranges");
   $migration->renameTable("glpi_plugin_fusioninventory_lock",
                           "glpi_plugin_fusioninventory_locks");
   $migration->renameTable("glpi_plugin_fusioninventory_unknown_device",
                           "glpi_plugin_fusioninventory_unknowndevices");
   $migration->renameTable("glpi_plugin_fusioninventory_config",
                           "glpi_plugin_fusioninventory_configs");

   $migration->renameTable("glpi_plugin_fusioninventory_networking_ports",
                           "glpi_plugin_fusinvsnmp_networkports");
   $migration->renameTable("glpi_plugin_fusioninventory_construct_device",
                           "glpi_plugin_fusinvsnmp_constructdevices");
   $migration->renameTable("glpi_plugin_fusioninventory_construct_mibs",
                           "glpi_plugin_fusioninventory_snmpmodelconstructdevice_miboids");
   $migration->renameTable("glpi_plugin_fusioninventory_networking",
                           "glpi_plugin_fusioninventory_networkequipments");
   $migration->renameTable("glpi_plugin_fusioninventory_networking_ifaddr",
                           "glpi_plugin_fusinvsnmp_networkequipmentips");
   $migration->renameTable("glpi_plugin_fusioninventory_printers",
                           "glpi_plugin_fusinvsnmp_printers");
   $migration->renameTable("glpi_plugin_fusioninventory_printers_cartridges",
                           "glpi_plugin_fusinvsnmp_printercartridges");
   $migration->renameTable("glpi_plugin_fusioninventory_printers_history",
                           "glpi_plugin_fusinvsnmp_printerlogs");
   $migration->renameTable("glpi_plugin_fusioninventory_model_infos",
                           "glpi_plugin_fusioninventory_snmpmodels");
   $migration->renameTable("glpi_plugin_fusioninventory_mib_networking",
                           "glpi_plugin_fusinvsnmp_modelmibs");
   $migration->renameTable("glpi_plugin_fusioninventory_snmp_connection",
                           "glpi_plugin_fusinvsnmp_configsecurities");
   $migration->renameTable("glpi_plugin_fusioninventory_snmp_history",
                           "glpi_plugin_fusinvsnmp_networkportlogs");
   $migration->renameTable("glpi_plugin_fusioninventory_snmp_history_connections",
                           "glpi_plugin_fusinvsnmp_networkportconnectionlogs");

   $a_droptable = array('glpi_plugin_fusioninventory_agents_inventory_state',
                        'glpi_plugin_fusioninventory_config_modules',
                        'glpi_plugin_fusioninventory_connection_stats',
                        'glpi_plugin_fusioninventory_discovery',
                        'glpi_plugin_fusioninventory_errors',
                        'glpi_plugin_fusioninventory_lockable',
                        'glpi_plugin_fusioninventory_connection_history',
                        'glpi_plugin_fusioninventory_walks',
                        'glpi_plugin_fusioninventory_config_snmp_history',
                        'glpi_plugin_fusioninventory_config_snmp_networking',
                        'glpi_plugin_fusioninventory_task',
                        'glpi_plugin_fusinvinventory_pcidevices',
                        'glpi_plugin_fusinvinventory_pcivendors',
                        'glpi_plugin_fusinvinventory_usbdevices',
                        'glpi_plugin_fusinvinventory_usbvendors',
                        'glpi_plugin_fusinvsnmp_constructdevicewalks');

   foreach ($a_droptable as $newTable) {
      $migration->dropTable($newTable);
   }

    /*
      $a_table = array();

      //table name
      $a_table['name'] = '';
      $a_table['oldname'] = array(
      );

      // fields : fields that are new, have changed type or just stay the same
      //    array(
      //        <fieldname> = array(
      //            'type' => <type>, 'value' => <value>)
      //    );
      $a_table['fields'] = array(

      );

      // oldfields = fields that need to be removed
      //    array( 'field0', 'field1', ...);
      $a_table['oldfields'] = array(
      );

      // renamefields = fields that need to be renamed
      //    array('oldname' = 'newname', ...)
      $a_table['renamefields'] = array(
      );

      // keys : new, changed or not
      //    array( 'field' => <fields>, 'name' => <keyname> , 'type' => <keytype>)
      // <fields> : fieldnames needed by the key
      //            ex : array('field0' , 'field1' ...)
      //            ex : 'fieldname'
      // <keyname> : the name of the key (if blank, the fieldname is used)
      // <type> : the type of key (ex: INDEX, ...)
      $a_table['keys'] = array(
      );

      // oldkeys : keys that need to be removed
      //    array( 'key0', 'key1', ... )
      $a_table['oldkeys'] = array(
      );
   */


   /*
    *  Table glpi_plugin_fusioninventory_agents
    */
      $newTable = "glpi_plugin_fusioninventory_agents";
      $prepare_agentConfig = array();
      if (TableExists("glpi_plugin_tracker_agents")
              AND FieldExists("glpi_plugin_tracker_agents",
                              "ifaddr_start")) {
         $query = "SELECT * FROM `glpi_plugin_tracker_agents`";
         $result=$DB->query($query);
         while ($data=$DB->fetch_array($result)) {
            $prepare_rangeip[] = array("ip_start"=> $data['ifaddr_start'],
                                       "ip_end"  => $data['ifaddr_end'],
                                       "name"    => $data['name']);
            $prepare_agentConfig[] = array(
                                  "name" => $data["name"],
                                  "lock" => $data['lock'],
                                  "threads_networkinventory" => $data['nb_process_query'],
                                  "threads_networkdiscovery" => $data['nb_process_discovery']);
         }
      } else if (TableExists("glpi_plugin_tracker_agents")
                  AND FieldExists("glpi_plugin_tracker_agents",
                              "core_discovery")) {
         $query = "SELECT * FROM `glpi_plugin_tracker_agents`";
         $result=$DB->query($query);
         while ($data=$DB->fetch_array($result)) {
            $prepare_agentConfig[] = array(
                                   "name" => $data["name"],
                                   "lock" => $data['lock'],
                                   "threads_networkinventory" => $data['threads_query'],
                                   "threads_networkdiscovery" => $data['threads_discovery']);
         }
      } else if (TableExists("glpi_plugin_fusioninventory_agents")) {
         if (FieldExists($newTable, "module_snmpquery")) {
            $query = "SELECT * FROM `glpi_plugin_fusioninventory_agents`";
            $result=$DB->query($query);
            while ($data=$DB->fetch_array($result)) {
               $prepare_agentConfig[] = array(
                                 "id" => $data["ID"],
                                 "threads_networkinventory" => $data['threads_query'],
                                 "threads_networkdiscovery" => $data['threads_discovery'],
                                 "NETORKINVENTORY" => $data['module_snmpquery'],
                                 "NETWORKDISCOVERY" => $data['module_netdiscovery'],
                                 "INVENTORY" => $data['module_inventory'],
                                 "WAKEONLAN" => $data['module_wakeonlan']);
            }
         }
      }
      $a_table = array();
      $a_table['name'] = 'glpi_plugin_fusioninventory_agents';
      $a_table['oldname'] = array('glpi_plugin_tracker_agents');

      $a_table['fields']  = array();
      $a_table['fields']['id']            = array('type'    => 'autoincrement',
                                                  'value'   => '');
      $a_table['fields']['entities_id']   = array('type'    => 'integer',
                                                  'value'   => NULL);
      $a_table['fields']['is_recursive']  = array('type'    => 'bool',
                                                  'value'   => '1');
      $a_table['fields']['name']          = array('type'    => 'string',
                                                  'value'   => NULL);
      $a_table['fields']['last_contact']  = array('type'    => 'datetime',
                                                  'value'   => NULL);
      $a_table['fields']['version']       = array('type'    => 'string',
                                                  'value'   => NULL);
      $a_table['fields']['lock']          = array('type'    => 'bool',
                                                  'value'   => NULL);
      $a_table['fields']['device_id']     = array('type'    => 'string',
                                                  'value'   => NULL);
      $a_table['fields']['computers_id']  = array('type'    => 'integer',
                                                  'value'   => NULL);
      $a_table['fields']['token']         = array('type'    => 'string',
                                                  'value'   => NULL);
      $a_table['fields']['useragent']     = array('type'    => 'string',
                                                  'value'   => NULL);
      $a_table['fields']['tag']           = array('type'    => 'string',
                                                  'value'   => NULL);
      $a_table['fields']['threads_networkdiscovery'] = array(
                        'type' => "int(4) NOT NULL DEFAULT '1' COMMENT 'array(xmltag=>value)'",
                        'value'   => NULL);
      $a_table['fields']['threads_networkinventory'] = array(
                        'type' => "int(4) NOT NULL DEFAULT '1' COMMENT 'array(xmltag=>value)'",
                        'value'   => NULL);
      $a_table['fields']['senddico']      = array('type'    => 'bool',
                                                  'value'   => NULL);
      $a_table['fields']['agent_port']    = array('type'    => 'varchar(6)',
                                                  'value'   => NULL);

      $a_table['oldfields']  = array(
         'module_snmpquery',
         'module_netdiscovery',
         'module_inventory',
         'module_wakeonlan',
         'core_discovery',
         'threads_discovery',
         'core_query',
         'threads_query',
         'tracker_agent_version',
         'logs',
         'fragment',
         'itemtype',
         'device_type');


      $a_table['renamefields'] = array();
      $a_table['renamefields']['ID'] = 'id';
      $a_table['renamefields']['last_agent_update'] = 'last_contact';
      $a_table['renamefields']['fusioninventory_agent_version'] = 'version';
      $a_table['renamefields']['key'] = 'device_id';
      $a_table['renamefields']['on_device'] = 'computers_id';
      $a_table['renamefields']['items_id'] = 'computers_id';

      $a_table['keys']   = array();
      $a_table['keys'][] = array('field' => 'name', 'name' => '', 'type' => 'INDEX');
      $a_table['keys'][] = array('field' => 'device_id', 'name' => '', 'type' => 'INDEX');
      $a_table['keys'][] = array('field' => 'computers_id', 'name' => '', 'type' => 'INDEX');

      $a_table['oldkeys'] = array('key');

      migrateTablesFusionInventory($migration, $a_table);



   /*
    * Table glpi_plugin_fusioninventory_agentmodules
    */
      $a_table = array();
      $a_table['name'] = 'glpi_plugin_fusioninventory_agentmodules';
      $a_table['oldname'] = array();

      $a_table['fields']  = array();
      $a_table['fields']['id']         = array('type'    => 'autoincrement',
                                               'value'   => '');
      $a_table['fields']['modulename'] = array('type'    => 'string',
                                               'value'   => NULL);
      $a_table['fields']['is_active']  = array('type'    => 'bool',
                                               'value'   => NULL);
      $a_table['fields']['exceptions'] = array('type'    => 'text',
                                               'value'   => NULL);

      $a_table['oldfields']  = array();
      $a_table['oldfields'][] = 'plugins_id';
      $a_table['oldfields'][] = 'entities_id';
      $a_table['oldfields'][] = 'url';

      $a_table['renamefields'] = array();

      $a_table['keys']   = array();
      $a_table['keys'][] = array('field' => 'modulename', 'name' => '', 'type' => 'UNIQUE');

      $a_table['oldkeys'] = array('unicity', 'entities_id');

      migrateTablesFusionInventory($migration, $a_table);


   /*
    * Add Deploy module
    */
   $query = "SELECT `id` FROM `glpi_plugin_fusioninventory_agentmodules`
      WHERE `modulename`='DEPLOY'";
   $result = $DB->query($query);
   if (!$DB->numrows($result)) {
      $query_ins= "INSERT INTO `glpi_plugin_fusioninventory_agentmodules`
            (`modulename`, `is_active`, `exceptions`)
         VALUES ('DEPLOY', '0', '".exportArrayToDB(array())."')";
      $DB->query($query_ins);
   }

   /*
    * Add WakeOnLan module appear in version 2.3.0
    */
   $query = "SELECT `id` FROM `glpi_plugin_fusioninventory_agentmodules`
      WHERE `modulename`='WAKEONLAN'";
   $result = $DB->query($query);
   if (!$DB->numrows($result)) {
      $query_ins= "INSERT INTO `glpi_plugin_fusioninventory_agentmodules`
            (`modulename`, `is_active`, `exceptions`)
         VALUES ('WAKEONLAN', '0', '".exportArrayToDB(array())."')";
      $DB->query($query_ins);
   }

   /*
    * Add SNMPQUERY module if not present
    */
   $query = "UPDATE `glpi_plugin_fusioninventory_agentmodules`
      SET `modulename`='NETWORKINVENTORY'
      WHERE `modulename`='SNMPQUERY'";
   $DB->query($query);

   $query = "SELECT `id` FROM `glpi_plugin_fusioninventory_agentmodules`
      WHERE `modulename`='NETWORKINVENTORY'";
   $result = $DB->query($query);
   if (!$DB->numrows($result)) {
      $agentmodule = new PluginFusioninventoryAgentmodule;
      $input = array();
      $input['modulename'] = "NETWORKINVENTORY";
      $input['is_active']  = 0;
      $input['exceptions'] = exportArrayToDB(array());
      $agentmodule->add($input);
   }

   /*
    * Add NETDISCOVERY module if not present
    */
   $query = "UPDATE `glpi_plugin_fusioninventory_agentmodules`
      SET `modulename`='NETWORKDISCOVERY'
      WHERE `modulename`='NETDISCOVERY'";
   $DB->query($query);

   $query = "SELECT `id` FROM `glpi_plugin_fusioninventory_agentmodules`
      WHERE `modulename`='NETWORKDISCOVERY'";
   $result = $DB->query($query);
   if (!$DB->numrows($result)) {
      $agentmodule = new PluginFusioninventoryAgentmodule;
      $input = array();
      $input['modulename'] = "NETWORKDISCOVERY";
      $input['is_active']  = 0;
      $input['exceptions'] = exportArrayToDB(array());
      $agentmodule->add($input);
   }



   /*
    * Add INVENTORY module if not present
    */
   $query = "SELECT `id` FROM `glpi_plugin_fusioninventory_agentmodules`
      WHERE `modulename`='INVENTORY'";
   $result = $DB->query($query);
   if (!$DB->numrows($result)) {
      $agentmodule = new PluginFusioninventoryAgentmodule;
      $input = array();
      $input['modulename'] = "INVENTORY";
      $input['is_active']  = 1;
      $input['exceptions'] = exportArrayToDB(array());
      $agentmodule->add($input);
   }





   /*
    * Table glpi_plugin_fusioninventory_configs
    */
      $newTable = "glpi_plugin_fusioninventory_configs";
      if (TableExists('glpi_plugin_tracker_config')) {
         if (FieldExists('glpi_plugin_tracker_config', 'ssl_only')) {
            $query = "SELECT * FROM `glpi_plugin_tracker_config`
               LIMIT 1";
            $result = $DB->query($query);
            if ($DB->numrows($result) > 0) {
               $data = $DB->fetch_assoc($result);
               $prepare_Config['ssl_only'] = $data['ssl_only'];
            }
         }
//         $query = "SELECT *  FROM `glpi_plugin_tracker_config`
//            WHERE `type`='version'
//            LIMIT 1, 10";
//         $result=$DB->query($query);
//         while ($data=$DB->fetch_array($result)) {
//            $DB->query("DELETE FROM `glpi_plugin_tracker_config`
//               WHERE `ID`='".$data['ID']."'");
//         }
      }
      if (TableExists('glpi_plugin_fusioninventory_configs')) {
         $id = 'id';
         if (FieldExists('glpi_plugin_fusioninventory_configs', 'ID')) {
            $id = 'ID';
         }

         $query = "SELECT *  FROM `glpi_plugin_fusioninventory_configs`
            WHERE `type`='version'
            LIMIT 1, 10";
         $result=$DB->query($query);
         while ($data=$DB->fetch_array($result)) {
            $DB->query("DELETE FROM `glpi_plugin_fusioninventory_configs`
               WHERE `".$id."`='".$data[$id]."'");
         }

      }


      $a_table = array();
      $a_table['name'] = 'glpi_plugin_fusioninventory_configs';
      $a_table['oldname'] = array('glpi_plugin_tracker_config');

      $a_table['fields']  = array();
      $a_table['fields']['id']         = array('type'    => 'autoincrement',
                                               'value'   => '');
      $a_table['fields']['type']       = array('type'    => 'string',
                                               'value'   => NULL);
      $a_table['fields']['value']      = array('type'    => 'string',
                                               'value'   => NULL);

      $a_table['oldfields']  = array();
      $a_table['oldfields'][] = 'version';
      $a_table['oldfields'][] = 'URL_agent_conf';
      $a_table['oldfields'][] = 'ssl_only';
      $a_table['oldfields'][] = 'authsnmp';
      $a_table['oldfields'][] = 'inventory_frequence';
      $a_table['oldfields'][] = 'criteria1_ip';
      $a_table['oldfields'][] = 'criteria1_name';
      $a_table['oldfields'][] = 'criteria1_serial';
      $a_table['oldfields'][] = 'criteria1_macaddr';
      $a_table['oldfields'][] = 'criteria2_ip';
      $a_table['oldfields'][] = 'criteria2_name';
      $a_table['oldfields'][] = 'criteria2_serial';
      $a_table['oldfields'][] = 'criteria2_macaddr';
      $a_table['oldfields'][] = 'delete_agent_process';
      $a_table['oldfields'][] = 'activation_history';
      $a_table['oldfields'][] = 'activation_connection';
      $a_table['oldfields'][] = 'activation_snmp_computer';
      $a_table['oldfields'][] = 'activation_snmp_networking';
      $a_table['oldfields'][] = 'activation_snmp_peripheral';
      $a_table['oldfields'][] = 'activation_snmp_phone';
      $a_table['oldfields'][] = 'activation_snmp_printer';
      $a_table['oldfields'][] = 'plugins_id';
      $a_table['oldfields'][] = 'module';

      $a_table['renamefields'] = array();
      $a_table['renamefields']['ID'] = 'id';

      $a_table['keys']   = array();
      $a_table['keys'][] = array('field' => array("type"),
                                 'name' => 'unicity',
                                 'type' => 'UNIQUE');

      $a_table['oldkeys'] = array();

      migrateTablesFusionInventory($migration, $a_table);



   /*
    * Table glpi_plugin_fusioninventory_entities
    */
      $a_table = array();
      $a_table['name'] = 'glpi_plugin_fusioninventory_entities';
      $a_table['oldname'] = array();

      $a_table['fields']  = array();
      $a_table['fields']['id']         = array('type'    => 'autoincrement',
                                               'value'   => '');
      $a_table['fields']['entities_id']= array('type'    => 'integer',
                                               'value'   => NULL);
      $a_table['fields']['transfers_id_auto']= array('type'    => 'integer',
                                                 'value'   => NULL);
      $a_table['fields']['agent_base_url']= array('type'    => 'string',
                                                 'value'   => '');

      $a_table['oldfields']  = array();

      $a_table['renamefields'] = array();

      $a_table['keys']   = array();
      $a_table['keys'][] = array('field' => array('entities_id', 'transfers_id_auto'),
                                 'name' => 'entities_id',
                                 'type' => 'INDEX');

      $a_table['oldkeys'] = array();

      migrateTablesFusionInventory($migration, $a_table);

      if (countElementsInTable($a_table['name']) == 0) {
         $a_configs = getAllDatasFromTable('glpi_plugin_fusioninventory_configs',
                                           "`type`='transfers_id_auto'");
         $transfers_id_auto = 0;
         if (count($a_configs) > 0) {
            $a_config = current($a_configs);
            $transfers_id_auto = $a_config['value'];
         }

         $a_configs = getAllDatasFromTable('glpi_plugin_fusioninventory_configs',
                                           "`type`='agent_base_url'");
         $agent_base_url = '';
         if (count($a_configs) > 0) {
            $a_config = current($a_configs);
            $agent_base_url = $a_config['value'];
         }

         $DB->query("INSERT INTO `glpi_plugin_fusioninventory_entities`
               (`entities_id`, `transfers_id_auto`, `agent_base_url`)
            VALUES ('0', '".$transfers_id_auto."', '".$agent_base_url."');");
      } else if (countElementsInTable($a_table['name']) > 0) {
         $a_configs = getAllDatasFromTable('glpi_plugin_fusioninventory_configs',
                                           "`type`='agent_base_url'");
         $agent_base_url = '';
         if (count($a_configs) > 0) {
            $a_config = current($a_configs);
            $agent_base_url = $a_config['value'];
         }

         $DB->query("UPDATE `glpi_plugin_fusioninventory_entities`
               SET `agent_base_url` = '".$agent_base_url."'
               ;");
      }



   /*
    * Table glpi_plugin_fusioninventory_credentials
    */
      $a_table = array();
      $a_table['name'] = 'glpi_plugin_fusioninventory_credentials';
      $a_table['oldname'] = array();

      $a_table['fields']  = array();
      $a_table['fields']['id']         = array('type'    => 'autoincrement',
                                               'value'   => '');
      $a_table['fields']['entities_id']= array('type'    => 'integer',
                                               'value'   => NULL);
      $a_table['fields']['is_recursive']= array('type'    => 'bool',
                                               'value'   => NULL);
      $a_table['fields']['name']       = array('type'    => 'string',
                                               'value'   => "");
      $a_table['fields']['username']   = array('type'    => 'string',
                                               'value'   => "");
      $a_table['fields']['password']   = array('type'    => 'string',
                                               'value'   => "");
      $a_table['fields']['comment']    = array('type'    => 'text',
                                               'value'   => NULL);
      $a_table['fields']['date_mod']   = array('type'    => 'datetime',
                                               'value'   => NULL);
      $a_table['fields']['itemtype']   = array('type'    => 'string',
                                               'value'   => "");

      $a_table['oldfields']  = array();

      $a_table['renamefields'] = array();

      $a_table['keys']   = array();

      $a_table['oldkeys'] = array();

      migrateTablesFusionInventory($migration, $a_table);

      // Fix itemtype changed in 0.84
      $DB->query("UPDATE `glpi_plugin_fusioninventory_credentials`
         SET `itemtype`='PluginFusioninventoryInventoryComputerESX'
         WHERE `itemtype`='PluginFusinvinventoryVmwareESX'");


   /*
    * Table glpi_plugin_fusioninventory_credentialips
    */
      $a_table = array();
      $a_table['name'] = 'glpi_plugin_fusioninventory_credentialips';
      $a_table['oldname'] = array();

      $a_table['fields']  = array();
      $a_table['fields']['id']         = array('type'    => 'autoincrement',
                                               'value'   => '');
      $a_table['fields']['entities_id']= array('type'    => 'integer',
                                               'value'   => NULL);
      $a_table['fields']['plugin_fusioninventory_credentials_id'] = array('type'    => 'integer',
                                               'value'   => NULL);
      $a_table['fields']['name']       = array('type'    => 'string',
                                               'value'   => "");
      $a_table['fields']['comment']    = array('type'    => 'text',
                                               'value'   => NULL);
      $a_table['fields']['ip']         = array('type'    => 'string',
                                               'value'   => "");
      $a_table['fields']['date_mod']   = array('type'    => 'datetime',
                                               'value'   => NULL);

      $a_table['oldfields']  = array();

      $a_table['renamefields'] = array();

      $a_table['keys']   = array();

      $a_table['oldkeys'] = array();

      migrateTablesFusionInventory($migration, $a_table);



   /*
    * Table glpi_plugin_fusioninventory_ipranges
    */
      $newTable = "glpi_plugin_fusioninventory_ipranges";
      if (TableExists("glpi_plugin_tracker_rangeip")) {
         // Get all data to create task
         $query = "SELECT * FROM `glpi_plugin_tracker_rangeip`";
         $result=$DB->query($query);
         while ($data=$DB->fetch_array($result)) {
            if ($data['discover'] == '1') {
               $prepare_task[] = array("agents_id" => $data['FK_tracker_agents'],
                                       "ipranges_id" => $data['ID'],
                                       "netdiscovery" => "1");
            }
            if ($data['query'] == '1') {
               $prepare_task[] = array("agents_id" => $data['FK_tracker_agents'],
                                       "ipranges_id" => $data['ID'],
                                       "snmpquery" => "1");
            }
         }
      }
      if (TableExists("glpi_plugin_fusioninventory_rangeip")
              AND FieldExists("glpi_plugin_fusioninventory_rangeip",
                              "FK_fusioninventory_agents_discover")) {

         // Get all data to create task
         $query = "SELECT * FROM `glpi_plugin_fusioninventory_rangeip`";
         $result=$DB->query($query);
         while ($data=$DB->fetch_array($result)) {
            if ($data['discover'] == '1') {
               $prepare_task[] = array("agents_id" => $data['FK_fusioninventory_agents_discover'],
                                       "ipranges_id" => $data['ID'],
                                       "netdiscovery" => "1");
            }
            if ($data['query'] == '1') {
               $prepare_task[] = array("agents_id" => $data['FK_fusioninventory_agents_query'],
                                       "ipranges_id" => $data['ID'],
                                       "snmpquery" => "1");
            }
         }
      }
      $a_table = array();
      $a_table['name'] = 'glpi_plugin_fusioninventory_ipranges';
      $a_table['oldname'] = array('glpi_plugin_tracker_rangeip', 'glpi_plugin_fusinvsnmp_ipranges');

      $a_table['fields']  = array(
         'id'         => array('type'    => 'autoincrement',    'value'   => ''),
         'name'       => array('type'    => 'string',           'value'   => NULL),
         'entities_id'=> array('type'    => 'integer',          'value'   => NULL),
         'ip_start'   => array('type'    => 'string',           'value'   => NULL),
         'ip_end'     => array('type'    => 'string',           'value'   => NULL)
      );

      $a_table['oldfields']  = array(
         'FK_tracker_agents',
         'discover',
         'query',
         'FK_fusioninventory_agents_discover',
         'FK_fusioninventory_agents_query',
         'construct_device_id',
         'log',
         'comment'
      );

      $a_table['renamefields'] = array(
         'ID' => 'id',
         'ifaddr_start' => 'ip_start',
         'ifaddr_end' => 'ip_end',
         'FK_entities' => 'entities_id'
      );

      $a_table['keys']   = array(
         array('field' => 'entities_id', 'name' => '', 'type' => 'INDEX')
      );

      $a_table['oldkeys'] = array(
         'FK_tracker_agents',
         'FK_tracker_agents_2'
      );

      migrateTablesFusionInventory($migration, $a_table);



   /*
    * Table glpi_plugin_fusioninventory_snmpmodelconstructdevicewalks
    */
      $a_table = array();
      $a_table['name'] = 'glpi_plugin_fusioninventory_snmpmodelconstructdevicewalks';
      $a_table['oldname'] = array('glpi_plugin_fusioninventory_construct_walks');

      $a_table['fields']  = array();
      $a_table['fields']['id']         = array('type'    => 'autoincrement',
                                               'value'   => '');
      $a_table['fields']['plugin_fusioninventory_snmpmodelconstructdevices_id']
                                       = array('type'    => 'integer',
                                               'value'   => NULL);
      $a_table['fields']['log']        = array('type'    => 'text',
                                               'value'   => NULL);

      $a_table['oldfields']  = array();

      $a_table['renamefields'] = array(
          'ID' => 'id',
          'construct_device_id' => 'plugin_fusioninventory_snmpmodelconstructdevices_id'
      );

      $a_table['keys']   = array();
      $a_table['keys'][] = array('field' => 'plugin_fusioninventory_snmpmodelconstructdevices_id',
                                 'name' => '', 'type' => 'INDEX');

      $a_table['oldkeys'] = array();

      migrateTablesFusionInventory($migration, $a_table);



   /*
    * Table glpi_plugin_fusioninventory_locks
    */
      $a_table = array();
      $a_table['name'] = 'glpi_plugin_fusioninventory_locks';
      $a_table['oldname'] = array();

      $a_table['fields']  = array();
      $a_table['fields']['id']         = array('type'    => 'autoincrement',
                                               'value'   => '');
      $a_table['fields']['tablename']  = array(
                        'type'    => "varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT ''",
                        'value'   => NULL);
      $a_table['fields']['items_id']   = array('type'    => 'integer',
                                               'value'   => NULL);
      $a_table['fields']['tablefields']= array('type'    => 'text',
                                               'value'   => NULL);

      $a_table['oldfields']  = array('itemtype');

      $a_table['renamefields'] = array();
      $a_table['renamefields']['fields'] = 'tablefields';

      $a_table['keys']   = array();
      $a_table['keys'][] = array('field' => 'tablename', 'name' => '', 'type' => 'INDEX');
      $a_table['keys'][] = array('field' => 'items_id' , 'name' => '', 'type' => 'INDEX');

      $a_table['oldkeys'] = array();

      migrateTablesFusionInventory($migration, $a_table);



   /*
    * Table glpi_plugin_fusioninventory_mappings
    */
      $a_table = array();
      $a_table['name'] = 'glpi_plugin_fusioninventory_mappings';
      $a_table['oldname'] = array();

      $a_table['fields']  = array();
      $a_table['fields']['id']         = array('type'    => 'autoincrement',
                                               'value'   => '');
      $a_table['fields']['itemtype']   = array(
                        'type'    => "varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL",
                        'value'   => NULL);
      $a_table['fields']['name']       = array('type'    => 'string',
                                               'value'   => NULL);
      $a_table['fields']['table']      = array('type'    => 'string',
                                               'value'   => NULL);
      $a_table['fields']['tablefield'] = array('type'    => 'string',
                                               'value'   => NULL);
      $a_table['fields']['locale']     = array('type'    => "int(4) NOT NULL DEFAULT '0'",
                                               'value'   => NULL);
      $a_table['fields']['shortlocale']= array('type'    => 'int(4) DEFAULT NULL',
                                               'value'   => NULL);

      $a_table['oldfields']  = array();

      $a_table['renamefields'] = array();

      $a_table['keys']   = array();
      $a_table['keys'][] = array('field' => 'name', 'name' => '', 'type' => 'INDEX');
      $a_table['keys'][] = array('field' => 'itemtype' , 'name' => '', 'type' => 'INDEX');
      $a_table['keys'][] = array('field' => 'table', 'name' => '', 'type' => 'INDEX');
      $a_table['keys'][] = array('field' => 'tablefield' , 'name' => '', 'type' => 'INDEX');

      $a_table['oldkeys'] = array();

      migrateTablesFusionInventory($migration, $a_table);
      pluginFusioninventoryUpdatemapping();



   /*
    * Table glpi_plugin_fusioninventory_profiles
    */
      $a_table = array();
      $a_table['name'] = 'glpi_plugin_fusioninventory_profiles';
      $a_table['oldname'] = array();

      $a_table['fields']  = array();
      $a_table['fields']['id']         = array('type'    => 'autoincrement',
                                               'value'   => '');
      $a_table['fields']['type']       = array('type'    => 'string',
                                               'value'   => '');
      $a_table['fields']['right']      = array('type'    => 'char',
                                               'value'   => NULL);
      $a_table['fields']['plugins_id'] = array('type'    => 'integer',
                                               'value'   => NULL);
      $a_table['fields']['profiles_id']= array('type'    => 'integer',
                                               'value'   => NULL);

      $a_table['oldfields']  = array(
          'name',
          'interface',
          'is_default',
          'snmp_networking',
          'snmp_printers',
          'snmp_models',
          'snmp_authentification',
          'rangeip',
          'agents',
          'remotecontrol',
          'agentsprocesses',
          'unknowndevices',
          'reports',
          'deviceinventory',
          'netdiscovery',
          'snmp_query',
          'wol',
          'configuration');

      $a_table['renamefields'] = array();
      $a_table['renamefields']['ID'] = 'id';

      $a_table['keys']   = array();

      $a_table['oldkeys'] = array();

      migrateTablesFusionInventory($migration, $a_table);

         // Remove multiple lines can have problem with unicity
         $query = "SELECT * , count(`id`) AS cnt
            FROM `glpi_plugin_fusioninventory_profiles`
            GROUP BY `type`,`plugins_id`,`profiles_id`
            HAVING cnt >1
            ORDER BY cnt";
         $result=$DB->query($query);
         while ($data=$DB->fetch_array($result)) {
            $queryd = "DELETE FROM `glpi_plugin_fusioninventory_profiles`
               WHERE `type`='".$data['type']."'
                  AND `plugins_id`='".$data['plugins_id']."'
                  AND `profiles_id`='".$data['profiles_id']."'
               ORDER BY `id` DESC
               LIMIT ".($data['cnt'] - 1)." ";
            $DB->query($queryd);
         }

      $a_table = array();
      $a_table['name'] = 'glpi_plugin_fusioninventory_profiles';
      $a_table['oldname'] = array();

      $a_table['fields']  = array();

      $a_table['oldfields']  = array();

      $a_table['renamefields'] = array();

      $a_table['keys']   = array();
      $a_table['keys'][] = array('field' => array("type", "plugins_id", "profiles_id"),
                                 'name' => 'unicity', 'type' => 'UNIQUE');

      $a_table['oldkeys'] = array();

      migrateTablesFusionInventory($migration, $a_table);



   /*
    * Table glpi_plugin_fusioninventory_tasks
    */
      $a_table = array();
      $a_table['name'] = 'glpi_plugin_fusioninventory_tasks';
      $a_table['oldname'] = array();

      $a_table['fields']  = array();
      $a_table['fields']['id']         = array('type'    => 'autoincrement',
                                               'value'   => '');
      $a_table['fields']['entities_id']= array('type'    => 'integer',
                                               'value'   => NULL);
      $a_table['fields']['name']       = array('type'    => 'string',
                                               'value'   => NULL);
      $a_table['fields']['date_creation']= array('type'    => 'datetime',
                                                 'value'   => NULL);
      $a_table['fields']['comment']    = array('type'    => 'text',
                                               'value'   => NULL);
      $a_table['fields']['is_active']  = array('type'    => 'bool',
                                               'value'   => NULL);
      $a_table['fields']['communication']= array('type'    => 'string',
                                                 'value'   => 'push');
      $a_table['fields']['permanent']  = array('type'    => 'string',
                                               'value'   => NULL);
      $a_table['fields']['date_scheduled'] = array('type'    => 'datetime',
                                                   'value'   => NULL);
      $a_table['fields']['periodicity_count'] = array('type'    => "int(6) NOT NULL DEFAULT '0'",
                                                      'value'   => NULL);
      $a_table['fields']['periodicity_type'] = array('type'    => 'string',
                                                     'value'   => NULL);
      $a_table['fields']['execution_id'] = array('type'    => "bigint(20) NOT NULL DEFAULT '0'",
                                                 'value'   => NULL);
      $a_table['fields']['is_advancedmode'] = array('type'    => 'bool',
                                                    'value'   => NULL);

      $a_table['oldfields']  = array();

      $a_table['renamefields'] = array();

      $a_table['keys']   = array();
      $a_table['keys'][] = array('field' => 'entities_id', 'name' => '', 'type' => 'INDEX');
      $a_table['keys'][] = array('field' => 'is_active', 'name' => '', 'type' => 'INDEX');

      $a_table['oldkeys'] = array();

      migrateTablesFusionInventory($migration, $a_table);



   /*
    * Table glpi_plugin_fusioninventory_taskjobs
    */
      $a_table = array();
      $a_table['name'] = 'glpi_plugin_fusioninventory_taskjobs';
      $a_table['oldname'] = array();

      $a_table['fields']  = array();
      $a_table['fields']['id']         = array('type'    => 'autoincrement',
                                               'value'   => '');
      $a_table['fields']['plugin_fusioninventory_tasks_id']= array('type'    => 'integer',
                                               'value'   => NULL);
      $a_table['fields']['entities_id']= array('type'    => 'integer',
                                               'value'   => NULL);
      $a_table['fields']['name']       = array('type'    => 'string',
                                               'value'   => NULL);
      $a_table['fields']['date_creation'] = array('type'    => 'datetime',
                                                  'value'   => NULL);
      $a_table['fields']['retry_nb'] = array('type'    => "tinyint(2) NOT NULL DEFAULT '0'",
                                               'value'   => NULL);
      $a_table['fields']['retry_time'] = array('type'    => 'integer',
                                               'value'   => NULL);
      $a_table['fields']['plugins_id'] = array('type'    => 'integer',
                                               'value'   => NULL);
      $a_table['fields']['method']     = array('type'    => 'string',
                                               'value'   => NULL);
      $a_table['fields']['definition'] = array('type'    => 'text',
                                               'value'   => NULL);
      $a_table['fields']['action']     = array('type'    => 'text',
                                               'value'   => NULL);
      $a_table['fields']['comment']    = array('type'    => 'text',
                                               'value'   => NULL);
      $a_table['fields']['users_id']   = array('type'    => 'integer',
                                               'value'   => NULL);
      $a_table['fields']['status']     = array('type'    => 'integer',
                                               'value'   => NULL);
      $a_table['fields']['rescheduled_taskjob_id'] = array('type'    => 'integer',
                                                           'value'   => NULL);
      $a_table['fields']['statuscomments'] = array('type'    => 'text',
                                                   'value'   => NULL);
      $a_table['fields']['periodicity_count'] = array('type'    => "int(6) NOT NULL DEFAULT '0'",
                                                      'value'   => NULL);
      $a_table['fields']['periodicity_type']  = array('type'    => 'string',
                                                      'value'   => NULL);
      $a_table['fields']['execution_id'] = array('type'    => "bigint(20) NOT NULL DEFAULT '0'",
                                                 'value'   => NULL);

      $a_table['oldfields']  = array();

      $a_table['renamefields'] = array();

      $a_table['keys']   = array();
      $a_table['keys'][] = array('field' => 'plugin_fusioninventory_tasks_id',
                                 'name' => '', 'type' => 'INDEX');
      $a_table['keys'][] = array('field' => 'entities_id', 'name' => '', 'type' => 'INDEX');
      $a_table['keys'][] = array('field' => 'plugins_id', 'name' => '', 'type' => 'INDEX');
      $a_table['keys'][] = array('field' => 'users_id', 'name' => '', 'type' => 'INDEX');
      $a_table['keys'][] = array('field' => 'rescheduled_taskjob_id',
                                 'name' => '', 'type' => 'INDEX');
      $a_table['keys'][] = array('field' => 'method', 'name' => '', 'type' => 'INDEX');

      $a_table['oldkeys'] = array();

      migrateTablesFusionInventory($migration, $a_table);

      // * Update method name changed
      $DB->query("UPDATE `glpi_plugin_fusioninventory_taskjobs`
         SET `method`='InventoryComputerESX'
         WHERE `method`='ESX'");
      $DB->query("UPDATE `glpi_plugin_fusioninventory_taskjobs`
         SET `method`='networkinventory'
         WHERE `method`='snmpinventory'");
      $DB->query("UPDATE `glpi_plugin_fusioninventory_taskjobs`
         SET `method`='networkdiscovery'
         WHERE `method`='netdiscovery'");
      // * Update plugins_id
      $DB->query("UPDATE `glpi_plugin_fusioninventory_taskjobs`
         SET `plugins_id`='".$plugins_id."'");



   /*
    * Table glpi_plugin_fusioninventory_taskjoblogs
    */
      $newTable = "glpi_plugin_fusioninventory_taskjoblogs";
      if (!TableExists($newTable)) {
         $query = "CREATE TABLE `".$newTable."` (
                     `id` bigint(20) NOT NULL AUTO_INCREMENT,
                      PRIMARY KEY (`id`)
                  ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1";
         $DB->query($query);
      }
         $migration->changeField($newTable,
                                 "id",
                                 "id",
                                 "bigint(20) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 "plugin_fusioninventory_taskjobstatus_id",
                                 "plugin_fusioninventory_taskjobstates_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "date",
                                 "date",
                                 "datetime DEFAULT NULL");
         $migration->changeField($newTable,
                                 "items_id",
                                 "items_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "itemtype",
                                 "itemtype",
                                 "varchar(100) DEFAULT NULL");
         $migration->changeField($newTable,
                                 "state",
                                 "state",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "comment",
                                 "comment",
                                 "text DEFAULT NULL");
         $migration->dropKey($newTable,
                             "plugin_fusioninventory_taskjobstatus_id");
      $migration->migrationOneTable($newTable);
         $migration->addField($newTable,
                              "id",
                              "bigint(20) NOT NULL AUTO_INCREMENT");
         $migration->addField($newTable,
                              "plugin_fusioninventory_taskjobstates_id",
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              "date",
                              "datetime DEFAULT NULL");
         $migration->addField($newTable,
                              "items_id",
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              "itemtype",
                              "varchar(100) DEFAULT NULL");
         $migration->addField($newTable,
                              "state",
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              "comment",
                              "text DEFAULT NULL");
         $migration->addKey($newTable,
                            array("plugin_fusioninventory_taskjobstates_id", "state", "date"),
                            "plugin_fusioninventory_taskjobstates_id");
      $migration->migrationOneTable($newTable);
      $DB->list_fields($newTable, FALSE);

      // rename comments for new lang system (gettext in 0.84)
         $a_text = array(
               'fusinvsnmp::1' => 'devicesqueried',
               'fusinvsnmp::2' => 'devicesfound',
               'fusinvsnmp::3' => 'diconotuptodate',
               'fusinvsnmp::4' => 'addtheitem',
               'fusinvsnmp::5' => 'updatetheitem',
               'fusinvsnmp::6' => 'inventorystarted',
               'fusinvsnmp::7' => 'detail',
               'fusioninventory::1' => 'badtoken',
               'fusioninventory::2' => 'agentcrashed',
               'fusioninventory::3' => 'importdenied'
            );
         $query = "SELECT * FROM `".$newTable."`
            WHERE `comment` LIKE '%==%'";
         $result=$DB->query($query);
         while ($data=$DB->fetch_array($result)) {
            $comment = $data['comment'];
            foreach ($a_text as $key=>$value) {
               $comment = str_replace("==".$key."==", "==".$value."==", $comment);
            }
            $DB->query("UPDATE `".$newTable."`
               SET `comment`='".$DB->escape($comment)."'
               WHERE `id`='".$data['id']."'");
         }

   /*
    * Table glpi_plugin_fusioninventory_taskjobstates
    */
      $newTable = "glpi_plugin_fusioninventory_taskjobstates";
      if (TableExists("glpi_plugin_fusioninventory_taskjobstatus")) {
         $migration->renameTable("glpi_plugin_fusioninventory_taskjobstatus", $newTable);
      }
      if (!TableExists($newTable)) {
         $query = "CREATE TABLE `".$newTable."` (
                     `id` bigint(20) NOT NULL AUTO_INCREMENT,
                      PRIMARY KEY (`id`)
                  ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1";
         $DB->query($query);
      }
         $migration->changeField($newTable,
                                 "id",
                                 "id",
                                 "bigint(20) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 "plugin_fusioninventory_taskjobs_id",
                                 "plugin_fusioninventory_taskjobs_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "items_id",
                                 "items_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "itemtype",
                                 "itemtype",
                                 "varchar(100) DEFAULT NULL");
         $migration->changeField($newTable,
                                 "state",
                                 "state",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "plugin_fusioninventory_agents_id",
                                 "plugin_fusioninventory_agents_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "specificity",
                                 "specificity",
                                 "text DEFAULT NULL");
         $migration->changeField($newTable,
                                 "uniqid",
                                 "uniqid",
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
      $migration->migrationOneTable($newTable);
         $migration->addField($newTable,
                              "id",
                              "bigint(20) NOT NULL AUTO_INCREMENT");
         $migration->addField($newTable,
                              "plugin_fusioninventory_taskjobs_id",
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              "items_id",
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              "itemtype",
                              "varchar(100) DEFAULT NULL");
         $migration->addField($newTable,
                              "state",
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              "plugin_fusioninventory_agents_id",
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              "specificity",
                              "text DEFAULT NULL");
         $migration->addField($newTable,
                              "uniqid",
                              "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addKey($newTable,
                            "plugin_fusioninventory_taskjobs_id");
         $migration->addKey($newTable,
                            array("plugin_fusioninventory_agents_id", "state"),
                            "plugin_fusioninventory_agents_id");
         $migration->addKey($newTable,
                            array("uniqid", "state"),
                            "uniqid");
      $migration->migrationOneTable($newTable);
      $DB->list_fields($newTable, FALSE);



   /*
    * Table glpi_plugin_fusioninventory_unknowndevices
    */
      $newTable = "glpi_plugin_fusioninventory_unknowndevices";
      if (TableExists('glpi_plugin_tracker_unknown_device')) {
         $migration->renameTable("glpi_plugin_tracker_unknown_device", $newTable);
      } else if (!TableExists($newTable)) {
         $query = "CREATE TABLE `".$newTable."` (
                     `id` int(11) NOT NULL AUTO_INCREMENT,
                      PRIMARY KEY (`id`)
                  ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1";
         $DB->query($query);
      }
         $migration->changeField($newTable,
                                 'id',
                                 'id',
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 'name',
                                 'name',
                                 'varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL');
         $migration->changeField($newTable,
                                 'date_mod',
                                 'date_mod',
                                 'datetime DEFAULT NULL');
         $migration->changeField($newTable,
                                 'entities_id',
                                 'entities_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'locations_id',
                                 'locations_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'is_deleted',
                                 'is_deleted',
                                 "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'serial',
                                 'serial',
                                 "varchar(255) DEFAULT NULL");
         $migration->changeField($newTable,
                                 'otherserial',
                                 'otherserial',
                                 "varchar(255) DEFAULT NULL");
         $migration->changeField($newTable,
                                 'contact',
                                 'contact',
                                 "varchar(255) DEFAULT NULL");
         $migration->changeField($newTable,
                                 'domain',
                                 'domain',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'comments',
                                 'comment',
                                 "text DEFAULT NULL");
         $migration->changeField($newTable,
                                 'comment',
                                 'comment',
                                 "text DEFAULT NULL");
         $migration->changeField($newTable,
                                 'type',
                                 'item_type',
                                 "varchar(255) DEFAULT NULL");
         $migration->changeField($newTable,
                                 'item_type',
                                 'item_type',
                                 "varchar(255) DEFAULT NULL");
         $migration->changeField($newTable,
                                 'accepted',
                                 'accepted',
                                 "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'plugin_fusioninventory_agents_id',
                                 'plugin_fusioninventory_agents_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'ifaddr',
                                 'ip',
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->changeField($newTable,
                                 'ip',
                                 'ip',
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->changeField($newTable,
                                 'hub',
                                 'hub',
                                 "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'states_id',
                                 'states_id',
                                 "int(11) NOT NULL DEFAULT '0'");
      $migration->migrationOneTable($newTable);
         $migration->changeField($newTable,
                                 'ID',
                                 'id',
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 'FK_entities',
                                 'entities_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'location',
                                 'locations_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'deleted',
                                 'is_deleted',
                                 "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "sysdescr",
                                 "sysdescr",
                                 "text DEFAULT NULL");
         $migration->changeField($newTable,
                                 "plugin_fusinvsnmp_models_id",
                                 "plugin_fusioninventory_snmpmodels_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "plugin_fusioninventory_snmpmodels_id",
                                 "plugin_fusioninventory_snmpmodels_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "plugin_fusioninventory_configsecurities_id",
                                 "plugin_fusioninventory_configsecurities_id",
                                 "int(11) NOT NULL DEFAULT '0'");
      $migration->migrationOneTable($newTable);
         $migration->changeField($newTable,
                                 "plugin_fusinvsnmp_configsecurities_id",
                                 "plugin_fusioninventory_configsecurities_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->dropField($newTable, "dnsname");
         $migration->dropField($newTable, "snmp");
         $migration->dropField($newTable, "FK_model_infos");
         $migration->dropField($newTable, "FK_snmp_connection");
         $migration->dropField($newTable, "FK_agent");
         $migration->dropField($newTable, "mac");
         $migration->dropField($newTable, "ifmac");
      $migration->migrationOneTable($newTable);
         $migration->addField($newTable,
                              'id',
                              "int(11) NOT NULL AUTO_INCREMENT");
         $migration->addField($newTable,
                              'name',
                              'varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL');
         $migration->addField($newTable,
                              'date_mod',
                              'datetime DEFAULT NULL');
         $migration->addField($newTable,
                              'entities_id',
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'locations_id',
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'is_deleted',
                              "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'is_template',
                              "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'users_id',
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'serial',
                              "varchar(255) DEFAULT NULL");
         $migration->addField($newTable,
                              'otherserial',
                              "varchar(255) DEFAULT NULL");
         $migration->addField($newTable,
                              'contact',
                              "varchar(255) DEFAULT NULL");
         $migration->addField($newTable,
                              'domain',
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'comment',
                              "text DEFAULT NULL");
         $migration->addField($newTable,
                              'item_type',
                              "varchar(255) DEFAULT NULL");
         $migration->addField($newTable,
                              'accepted',
                              "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'plugin_fusioninventory_agents_id',
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'ip',
                              "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addField($newTable,
                              'hub',
                              "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'states_id',
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 "sysdescr",
                                 "text DEFAULT NULL");
         $migration->addField($newTable,
                                 "plugin_fusioninventory_snmpmodels_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 "plugin_fusioninventory_configsecurities_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'is_dynamic',
                              "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              "serialized_inventory",
                              "longblob");
         $migration->addKey($newTable,
                            "entities_id");
         $migration->addKey($newTable,
                            "plugin_fusioninventory_agents_id");
         $migration->addKey($newTable,
                            "is_deleted");
         $migration->addKey($newTable,
                            "date_mod");
      $migration->migrationOneTable($newTable);
      $DB->list_fields($newTable, FALSE);

      if (TableExists('glpi_plugin_fusinvsnmp_unknowndevices')) {
         $query = "SELECT * FROM `glpi_plugin_fusinvsnmp_unknowndevices`";
         $result=$DB->query($query);
         while ($data=$DB->fetch_array($result)) {
            $DB->query("UPDATE `glpi_plugin_fusioninventory_unknowndevices`
               SET `sysdescr`='".$data['sysdescr']."',
                   `plugin_fusioninventory_snmpmodels_id`='".$data['plugin_fusinvsnmp_models_id']."',
                   `plugin_fusioninventory_configsecurities_id`='".$data['plugin_fusinvsnmp_configsecurities_id']."'
               WHERE `id`='".$data['plugin_fusioninventory_unknowndevices_id']."'");
         }
         $migration->dropTable('glpi_plugin_fusinvsnmp_unknowndevices');
      }



   /*
    * Table glpi_plugin_fusioninventory_ignoredimportdevices
    */
      $a_table = array();
      $a_table['name'] = 'glpi_plugin_fusioninventory_ignoredimportdevices';
      $a_table['oldname'] = array();

      $a_table['fields']  = array();
      $a_table['fields']['id']         = array('type'    => 'autoincrement',
                                               'value'   => '');
      $a_table['fields']['name']       = array('type'    => 'string',
                                               'value'   => NULL);
      $a_table['fields']['date']       = array('type'    => 'datetime',
                                               'value'   => NULL);
      $a_table['fields']['itemtype']   = array(
                        'type'    => "varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL",
                        'value'   => NULL);
      $a_table['fields']['entities_id']= array('type'    => 'integer',
                                               'value'   => NULL);
      $a_table['fields']['ip']         = array('type'    => 'string',
                                               'value'   => NULL);
      $a_table['fields']['mac']        = array('type'    => 'string',
                                               'value'   => NULL);
      $a_table['fields']['rules_id']   = array('type'    => 'integer',
                                               'value'   => NULL);
      $a_table['fields']['method']     = array('type'    => 'string',
                                               'value'   => NULL);
      $a_table['fields']['serial']     = array('type'    => 'string',
                                               'value'   => NULL);
      $a_table['fields']['uuid']       = array('type'    => 'string',
                                               'value'   => NULL);

      $a_table['oldfields']  = array();

      $a_table['renamefields'] = array();

      $a_table['keys']   = array();

      $a_table['oldkeys'] = array();

      migrateTablesFusionInventory($migration, $a_table);




   /*
    * Table glpi_plugin_fusioninventory_inventorycomputercriterias
    */
      $a_table = array();
      $a_table['name'] = 'glpi_plugin_fusioninventory_inventorycomputercriterias';
      $a_table['oldname'] = array('glpi_plugin_fusinvinventory_criterias');

      $a_table['fields']  = array();
      $a_table['fields']['id']         = array('type'    => 'autoincrement',
                                               'value'   => '');
      $a_table['fields']['name']       = array('type'    => 'string',
                                               'value'   => NULL);
      $a_table['fields']['comment']    = array('type'    => 'text',
                                               'value'   => NULL);

      $a_table['oldfields']  = array();

      $a_table['renamefields'] = array();

      $a_table['keys']   = array();
      $a_table['keys'][] = array('field' => 'name', 'name' => '', 'type' => 'INDEX');

      $a_table['oldkeys'] = array();

      migrateTablesFusionInventory($migration, $a_table);



   /*
    * Table glpi_plugin_fusioninventory_rulematchedlogs
    */
      $newTable = "glpi_plugin_fusioninventory_rulematchedlogs";
      if (!TableExists($newTable)) {
         $query = "CREATE TABLE `".$newTable."` (
                     `id` int(11) NOT NULL AUTO_INCREMENT,
                      PRIMARY KEY (`id`)
                  ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1";
         $DB->query($query);
      }
         $migration->changeField($newTable,
                                 "id",
                                 "id",
                                 "int(11) NOT NULL AUTO_INCREMENT");

      $migration->migrationOneTable($newTable);

         $migration->addField($newTable,
                                 "date",
                                 "datetime DEFAULT NULL");
      $migration->addField($newTable,
                                 "items_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 "itemtype",
                                 "varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addField($newTable,
                                 "rules_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 "plugin_fusioninventory_agents_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 "method",
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
      $migration->migrationOneTable($newTable);
      $DB->list_fields($newTable, FALSE);



   /*
    * Table glpi_plugin_fusioninventory_inventorycomputerblacklists
    */
      $a_table = array();
      $a_table['name'] = 'glpi_plugin_fusioninventory_inventorycomputerblacklists';
      $a_table['oldname'] = array('glpi_plugin_fusinvinventory_blacklists');

      $a_table['fields']  = array();
      $a_table['fields']['id']         = array('type'    => 'autoincrement',
                                               'value'   => '');
      $a_table['fields']['plugin_fusioninventory_criterium_id'] = array('type'    => 'integer',
                                                                        'value'   => NULL);
      $a_table['fields']['value']  = array('type'    => 'string',
                                           'value'   => NULL);

      $a_table['oldfields']  = array();

      $a_table['renamefields'] = array();

      $a_table['keys']   = array();
      $a_table['keys'][] = array('field' => 'plugin_fusioninventory_criterium_id',
                                 'name' => '',
                                 'type' => 'KEY');

      $a_table['oldkeys'] = array();

      migrateTablesFusionInventory($migration, $a_table);
      $DB->list_fields($newTable, FALSE);


   pluginFusioninventorychangeDisplayPreference("5153", "PluginFusioninventoryUnknownDevice");
   pluginFusioninventorychangeDisplayPreference("5158", "PluginFusioninventoryAgent");


   /*
    *  Udpate criteria for blacklist
    */
      $a_criteria = array();
      $a_criteria['Serial number'] = 'ssn';
      $a_criteria['uuid'] = 'uuid';
      $a_criteria['Mac address'] = 'macAddress';
      $a_criteria['Windows product key'] = 'winProdKey';
      $a_criteria['Model'] = 'smodel';
      $a_criteria['storage serial'] = 'storagesSerial';
      $a_criteria['drives serial'] = 'drivesSerial';
      $a_criteria['Asset Tag'] = 'assetTag';
      $a_criteria['Computer name'] = 'name';
      $a_criteria['Manufacturer'] = 'manufacturer';

      foreach ($a_criteria as $name=>$comment) {
         $query = "SELECT * FROM `glpi_plugin_fusioninventory_inventorycomputercriterias`
            WHERE `name`='".$name."'";
         $result = $DB->query($query);
         if ($DB->numrows($result) == '0') {
            $query_ins = "INSERT INTO `glpi_plugin_fusioninventory_inventorycomputercriterias`
               (`name`, `comment`)
               VALUES ('".$name."', '".$comment."')";
            $DB->query($query_ins);
         }
      }
      $a_criteria = array();
      $query = "SELECT * FROM `glpi_plugin_fusioninventory_inventorycomputercriterias`";
      $result = $DB->query($query);
      while ($data=$DB->fetch_array($result)) {
         $a_criteria[$data['comment']] = $data['id'];
      }



    /*
    * Update blacklist
    */
      $newTable = "glpi_plugin_fusioninventory_inventorycomputerblacklists";
      // * ssn
      $a_input = array(
         'N/A',
         '(null string)',
         'INVALID',
         'SYS-1234567890',
         'SYS-9876543210',
         'SN-12345',
         'SN-1234567890',
         '1111111111',
         '1111111',
         '1',
         '0123456789',
         '12345',
         '123456',
         '1234567',
         '12345678',
         '123456789',
         '1234567890',
         '123456789000',
         '12345678901234567',
         '0000000000',
         '000000000',
         '00000000',
         '0000000',
         '0000000',
         'NNNNNNN',
         'xxxxxxxxxxx',
         'EVAL',
         'IATPASS',
         'none',
         'To Be Filled By O.E.M.',
         'Tulip Computers',
         'Serial Number xxxxxx',
         'SN-123456fvgv3i0b8o5n6n7k',
         'Unknow',
         'System Serial Number',
         'MB-1234567890',
         '0');
         foreach ($a_input as $value) {
            $query = "SELECT * FROM `".$newTable."`
               WHERE `plugin_fusioninventory_criterium_id`='".$a_criteria['ssn']."'
                AND `value`='".$value."'";
            $result=$DB->query($query);
            if ($DB->numrows($result) == '0') {
               $query = "INSERT INTO `".$newTable."`
                     (`plugin_fusioninventory_criterium_id`, `value`)
                  VALUES ( '".$a_criteria['ssn']."', '".$value."')";
               $DB->query($query);
            }
         }

         // * uuid
         $a_input = array(
            'FFFFFFFF-FFFF-FFFF-FFFF-FFFFFFFFFFFF',
            '03000200-0400-0500-0006-000700080009',
            '6AB5B300-538D-1014-9FB5-B0684D007B53',
            '01010101-0101-0101-0101-010101010101');
         foreach ($a_input as $value) {
            $query = "SELECT * FROM `".$newTable."`
               WHERE `plugin_fusioninventory_criterium_id`='".$a_criteria['uuid']."'
                AND `value`='".$value."'";
            $result=$DB->query($query);
            if ($DB->numrows($result) == '0') {
               $query = "INSERT INTO `".$newTable."`
                     (`plugin_fusioninventory_criterium_id`, `value`)
                  VALUES ( '".$a_criteria['uuid']."', '".$value."')";
               $DB->query($query);
            }
         }

         // * macAddress
         $a_input = array(
            '20:41:53:59:4e:ff',
            '02:00:4e:43:50:49',
            'e2:e6:16:20:0a:35',
            'd2:0a:2d:a0:04:be',
            '00:a0:c6:00:00:00',
            'd2:6b:25:2f:2c:e7',
            '33:50:6f:45:30:30',
            '0a:00:27:00:00:00',
            '00:50:56:C0:00:01',
            '00:50:56:C0:00:08',
            '02:80:37:EC:02:00',
            '50:50:54:50:30:30',
            '24:b6:20:52:41:53');
         foreach ($a_input as $value) {
            $query = "SELECT * FROM `".$newTable."`
               WHERE `plugin_fusioninventory_criterium_id`='".$a_criteria['macAddress']."'
                AND `value`='".$value."'";
            $result=$DB->query($query);
            if ($DB->numrows($result) == '0') {
               $query = "INSERT INTO `".$newTable."`
                     (`plugin_fusioninventory_criterium_id`, `value`)
                  VALUES ( '".$a_criteria['macAddress']."', '".$value."')";
               $DB->query($query);
            }
         }

         // * smodel
         $a_input = array(
            'Unknow',
            'To Be Filled By O.E.M.',
            '*',
            'System Product Name',
            'Product Name',
            'System Name');
         foreach ($a_input as $value) {
            $query = "SELECT * FROM `".$newTable."`
               WHERE `plugin_fusioninventory_criterium_id`='".$a_criteria['smodel']."'
                AND `value`='".$value."'";
            $result=$DB->query($query);
            if ($DB->numrows($result) == '0') {
               $query = "INSERT INTO `".$newTable."`
                     (`plugin_fusioninventory_criterium_id`, `value`)
                  VALUES ( '".$a_criteria['smodel']."', '".$value."')";
               $DB->query($query);
            }
         }

         // * manufacturer
         $a_input = array(
            'System manufacturer');
         foreach ($a_input as $value) {
            $query = "SELECT * FROM `".$newTable."`
               WHERE `plugin_fusioninventory_criterium_id`='".$a_criteria['manufacturer']."'
                AND `value`='".$value."'";
            $result=$DB->query($query);
            if ($DB->numrows($result) == '0') {
               $query = "INSERT INTO `".$newTable."`
                     (`plugin_fusioninventory_criterium_id`, `value`)
                  VALUES ( '".$a_criteria['manufacturer']."', '".$value."')";
               $DB->query($query);
            }
         }



   /*
    * Table glpi_plugin_fusioninventory_inventorycomputerantiviruses
    */
      $newTable = "glpi_plugin_fusioninventory_inventorycomputerantiviruses";
      $migration->renameTable("glpi_plugin_fusinvinventory_antivirus", $newTable);
      if (!TableExists($newTable)) {
         $DB->query("CREATE TABLE `".$newTable."` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        PRIMARY KEY (`id`)
                   ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1");
      }
      $migration->addField($newTable,
                           "id",
                           "int(11) NOT NULL AUTO_INCREMENT");
      $migration->addField($newTable,
                           "computers_id",
                           "int(11) NOT NULL DEFAULT '0'");
      $migration->addField($newTable,
                           "name",
                           "varchar(255) DEFAULT NULL");
      $migration->addField($newTable,
                           "manufacturers_id",
                           "int(11) NOT NULL DEFAULT '0'");
      $migration->addField($newTable,
                           "version",
                           "varchar(255) DEFAULT NULL");
      $migration->addField($newTable,
                           "is_active",
                           "tinyint(1) NOT NULL DEFAULT '0'");
      $migration->addField($newTable,
                           "uptodate",
                           "tinyint(1) NOT NULL DEFAULT '0'");
      $migration->addKey($newTable,
                          "name");
      $migration->addKey($newTable,
                          "version");
      $migration->addKey($newTable,
                          "is_active");
      $migration->addKey($newTable,
                          "uptodate");
      $migration->addKey($newTable,
                          "computers_id");
      $migration->migrationOneTable($newTable);
      $DB->list_fields($newTable, FALSE);



   /*
    * Table glpi_plugin_fusioninventory_inventorycomputerbatteries
    */
      $newTable = "glpi_plugin_fusioninventory_inventorycomputerbatteries";
      if (!TableExists($newTable)) {
         $query = "CREATE TABLE `".$newTable."` (
                     `id` int(11) NOT NULL AUTO_INCREMENT,
                      PRIMARY KEY (`id`)
                  ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1";
         $DB->query($query);
      }
         $migration->changeField($newTable,
                                 "id",
                                 "id",
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 "computers_id",
                                 "computers_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "name",
                                 "name",
                                 "varchar(255) DEFAULT NULL");
         $migration->changeField($newTable,
                                 "manufacturers_id",
                                 "manufacturers_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "serial",
                                 "serial",
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->changeField($newTable,
                                 "capacity",
                                 "capacity",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "date",
                                 "date",
                                 "datetime DEFAULT NULL");
         $migration->changeField($newTable,
                                 "plugin_fusioninventory_inventorycomputerchemistries_id",
                                 "plugin_fusioninventory_inventorycomputerchemistries_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "voltage",
                                 "voltage",
                                 "int(11) NOT NULL DEFAULT '0'");
      $migration->migrationOneTable($newTable);
         $migration->addField($newTable,
                              "id",
                              "int(11) NOT NULL AUTO_INCREMENT");
         $migration->addField($newTable,
                              "computers_id",
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              "name",
                              "varchar(255) DEFAULT NULL");
         $migration->addField($newTable,
                              "manufacturers_id",
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              "serial",
                              "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addField($newTable,
                              "capacity",
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              "date",
                              "datetime DEFAULT NULL");
         $migration->addField($newTable,
                              "plugin_fusioninventory_inventorycomputerchemistries_id",
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              "voltage",
                              "int(11) NOT NULL DEFAULT '0'");
      $migration->migrationOneTable($newTable);
         $migration->addKey($newTable,
                            "computers_id");
      $migration->migrationOneTable($newTable);
      $DB->list_fields($newTable, FALSE);




   /*
    * Table glpi_plugin_fusioninventory_inventorycomputerchemistries
    */
      $newTable = "glpi_plugin_fusioninventory_inventorycomputerchemistries";
      if (!TableExists($newTable)) {
         $query = "CREATE TABLE `".$newTable."` (
                     `id` int(11) NOT NULL AUTO_INCREMENT,
                      PRIMARY KEY (`id`)
                  ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1";
         $DB->query($query);
      }
         $migration->changeField($newTable,
                                 "id",
                                 "id",
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 "name",
                                 "name",
                                 "varchar(255) DEFAULT NULL");
      $migration->migrationOneTable($newTable);
         $migration->addField($newTable,
                              "id",
                              "int(11) NOT NULL AUTO_INCREMENT");
         $migration->addField($newTable,
                              "name",
                              "varchar(255) DEFAULT NULL");
      $migration->migrationOneTable($newTable);
         $migration->addKey($newTable,
                            "name");
      $migration->migrationOneTable($newTable);
      $DB->list_fields($newTable, FALSE);



   /*
    * Table glpi_plugin_fusioninventory_inventorycomputercomputers
    */
      if (TableExists("glpi_plugin_fusinvinventory_computers")
              AND FieldExists("glpi_plugin_fusinvinventory_computers", "uuid")) {
         $Computer = new Computer();
         $sql = "SELECT * FROM `glpi_plugin_fusinvinventory_computers`";
         $result=$DB->query($sql);
         while ($data = $DB->fetch_array($result)) {
            if ($Computer->getFromDB($data['items_id'])) {
               $input = array();
               $input['id'] = $data['items_id'];
               $input['uuid'] = $data['uuid'];
               $Computer->update($input);
            }
         }
         $sql = "DROP TABLE `glpi_plugin_fusinvinventory_computers`";
         $DB->query($sql);
      }
      if (TableExists("glpi_plugin_fusinvinventory_tmp_agents")) {
         $sql = "DROP TABLE `glpi_plugin_fusinvinventory_tmp_agents`";
         $DB->query($sql);
      }
      $a_table = array();
      $a_table['name'] = 'glpi_plugin_fusioninventory_inventorycomputercomputers';
      $a_table['oldname'] = array('glpi_plugin_fusinvinventory_computers');

      $a_table['fields']  = array();
      $a_table['fields']['id']                     = array('type'    => 'autoincrement',
                                                           'value'   => '');
      $a_table['fields']['computers_id']           = array('type'    => 'integer',
                                                           'value'   => NULL);
      $a_table['fields']['bios_date']              = array('type'    => 'datetime',
                                                           'value'   => NULL);
      $a_table['fields']['bios_version']           = array('type'    => 'string',
                                                           'value'   => NULL);
      $a_table['fields']['bios_assettag']          = array('type'    => 'string',
                                                           'value'   => NULL);
      $a_table['fields']['bios_manufacturers_id']  = array('type'    => 'integer',
                                                           'value'   => NULL);
      $a_table['fields']['operatingsystem_installationdate'] = array('type'    => 'datetime',
                                                                     'value'   => NULL);
      $a_table['fields']['winowner']               = array('type'    => 'string',
                                                           'value'   => NULL);
      $a_table['fields']['wincompany']             = array('type'    => 'string',
                                                           'value'   => NULL);
      $a_table['fields']['last_fusioninventory_update']     = array('type'    => 'datetime',
                                                                    'value'   => NULL);
      $a_table['fields']['remote_addr']            = array('type'    => 'string',
                                                           'value'   => NULL);
      $a_table['fields']['plugin_fusioninventory_computerarchs_id'] = array('type'    => 'integer',
                                                                            'value'   => NULL);
      $a_table['fields']['serialized_inventory']   = array('type'    => 'longblob',
                                                           'value'   => "");
      $a_table['fields']['is_entitylocked']        = array('type'    => 'bool',
                                                           'value'   => "0");
      $a_table['fields']['oscomment']              = array('type'    => 'text',
                                                           'value'   => NULL);

      $a_table['oldfields']  = array();

      $a_table['renamefields'] = array();

      $a_table['keys']   = array();
      $a_table['keys'][] = array('field' => 'computers_id', 'name' => '', 'type' => 'INDEX');
      $a_table['keys'][] = array('field' => 'last_fusioninventory_update', 'name' => '', 'type' => 'INDEX');

      $a_table['oldkeys'] = array();

      migrateTablesFusionInventory($migration, $a_table);

      // Migrate libserialization
      require_once(GLPI_ROOT . "/plugins/fusioninventory/inc/inventorycomputercomputer.class.php");
      $pfInventoryComputerComputer = new PluginFusioninventoryInventoryComputerComputer();
      if (TableExists('glpi_plugin_fusinvinventory_libserialization')) {
         $query = "SELECT * FROM `glpi_plugin_fusinvinventory_libserialization`";
         $result=$DB->query($query);
         while ($data = $DB->fetch_array($result)) {
            $a_pfcomputer = array();
            $a_pfcomputer = current($pfInventoryComputerComputer->find(
                                                   "`computers_id`='".$data['computers_id']."'",
                                                   "", 1));
            if (empty($a_pfcomputer)) {
               // Add
               if (countElementsInTable("glpi_computers", "`id`='".$data['computers_id']."'") > 0) {
                  $input = array();
                  $input['computers_id'] = $data['computers_id'];
                  $input['last_fusioninventory_update'] = $data['last_fusioninventory_update'];
                  $pfInventoryComputerComputer->add($input);
               }
            } else {
               // Update
               $a_pfcomputer['last_fusioninventory_update'] = $data['last_fusioninventory_update'];
               $pfInventoryComputerComputer->update($a_pfcomputer);
            }
         }
      }


      $migration->dropTable('glpi_plugin_fusinvinventory_libserialization');



   /*
    * Table glpi_plugin_fusioninventory_inventorycomputerstorages
    */
      $newTable = "glpi_plugin_fusioninventory_inventorycomputerstorages";
      if (!TableExists($newTable)) {
         $query = "CREATE TABLE `".$newTable."` (
                     `id` int(11) NOT NULL AUTO_INCREMENT,
                      PRIMARY KEY (`id`)
                  ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1";
         $DB->query($query);
      }
         $migration->changeField($newTable,
                                 "id",
                                 "id",
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 "name",
                                 "name",
                                 "varchar(255) DEFAULT NULL");
         $migration->changeField($newTable,
                                 "uuid",
                                 "uuid",
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->changeField($newTable,
                                 "totalsize",
                                 "totalsize",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "freesize",
                                 "freesize",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "plugin_fusioninventory_inventorycomputerstoragetypes_id",
                                 "plugin_fusioninventory_inventorycomputerstoragetypes_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "computers_id",
                                 "computers_id",
                                 "int(11) NOT NULL DEFAULT '0'");
      $migration->migrationOneTable($newTable);
         $migration->addField($newTable,
                              "id",
                              "int(11) NOT NULL AUTO_INCREMENT");
         $migration->addField($newTable,
                              "name",
                              "varchar(255) DEFAULT NULL");
         $migration->addField($newTable,
                              "uuid",
                              "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addField($newTable,
                              "totalsize",
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              "freesize",
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              "plugin_fusioninventory_inventorycomputerstoragetypes_id",
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              "computers_id",
                              "int(11) NOT NULL DEFAULT '0'");
      $migration->migrationOneTable($newTable);
         $migration->addKey($newTable,
                            "uuid");
         $migration->addKey($newTable,
                            "computers_id");
      $migration->migrationOneTable($newTable);
      $DB->list_fields($newTable, FALSE);



   /*
    * Table glpi_plugin_fusioninventory_inventorycomputerstoragetypes
    */
      $newTable = "glpi_plugin_fusioninventory_inventorycomputerstoragetypes";
      if (!TableExists($newTable)) {
         $query = "CREATE TABLE `".$newTable."` (
                     `id` int(11) NOT NULL AUTO_INCREMENT,
                      PRIMARY KEY (`id`)
                  ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1";
         $DB->query($query);
      }
         $migration->changeField($newTable,
                                 "id",
                                 "id",
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 "name",
                                 "name",
                                 "varchar(255) DEFAULT NULL");
         $migration->changeField($newTable,
                                 "level",
                                 "level",
                                 "tinyint(2) NOT NULL DEFAULT '0'");
      $migration->migrationOneTable($newTable);
         $migration->addField($newTable,
                              "id",
                              "int(11) NOT NULL AUTO_INCREMENT");
         $migration->addField($newTable,
                              "name",
                              "varchar(255) DEFAULT NULL");
         $migration->addField($newTable,
                              "level",
                              "tinyint(2) NOT NULL DEFAULT '0'");
      $migration->migrationOneTable($newTable);
         $migration->addKey($newTable,
                            "level");
      $migration->migrationOneTable($newTable);
      $DB->list_fields($newTable, FALSE);



   /*
    * Table glpi_plugin_fusioninventory_inventorycomputerstorages_storages
    */
      $newTable = "glpi_plugin_fusioninventory_inventorycomputerstorages_storages";
      if (!TableExists($newTable)) {
         $query = "CREATE TABLE `".$newTable."` (
                     `id` int(11) NOT NULL AUTO_INCREMENT,
                      PRIMARY KEY (`id`)
                  ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1";
         $DB->query($query);
      }
         $migration->changeField($newTable,
                                 "id",
                                 "id",
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 "plugin_fusioninventory_inventorycomputerstorages_id_1",
                                 "plugin_fusioninventory_inventorycomputerstorages_id_1",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "plugin_fusioninventory_inventorycomputerstorages_id_2",
                                 "plugin_fusioninventory_inventorycomputerstorages_id_2",
                                 "int(11) NOT NULL DEFAULT '0'");
      $migration->migrationOneTable($newTable);
         $migration->addField($newTable,
                              "id",
                              "int(11) NOT NULL AUTO_INCREMENT");
         $migration->addField($newTable,
                              "plugin_fusioninventory_inventorycomputerstorages_id_1",
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              "plugin_fusioninventory_inventorycomputerstorages_id_2",
                              "int(11) NOT NULL DEFAULT '0'");
      $migration->migrationOneTable($newTable);
         $migration->addKey($newTable,
                            "plugin_fusioninventory_inventorycomputerstorages_id_1");
         $migration->addKey($newTable,
                            "plugin_fusioninventory_inventorycomputerstorages_id_2");
      $migration->migrationOneTable($newTable);
      $DB->list_fields($newTable, FALSE);



   /*
    * Table glpi_plugin_fusioninventory_agentmodules
    */
      $a_table = array();
      $a_table['name'] = 'glpi_plugin_fusioninventory_snmpmodeldevices';
      $a_table['oldname'] = array('glpi_plugin_fusinvsnmp_modeldevices');

      $a_table['fields']  = array();
      $a_table['fields']['id']         = array('type'    => 'autoincrement',
                                               'value'   => '');
      $a_table['fields']['plugin_fusioninventory_snmpmodels_id'] = array('type'    => 'integer',
                                                                         'value'   => NULL);
      $a_table['fields']['sysdescr']   = array('type'    => 'text',
                                               'value'   => NULL);

      $a_table['oldfields']  = array();

      $a_table['renamefields'] = array();
      $a_table['renamefields']['plugin_fusinvsnmp_models_id'] = 'plugin_fusioninventory_snmpmodels_id';

      $a_table['keys']   = array();
      $a_table['keys'][] = array('field' => 'plugin_fusioninventory_snmpmodels_id', 'name' => '', 'type' => 'INDEX');

      $a_table['oldkeys'] = array('plugin_fusinvsnmp_models_id');

      migrateTablesFusionInventory($migration, $a_table);



   /*
    * Table glpi_plugin_fusioninventory_snmpmodelmiblabels
    */
      $a_table = array();
      $a_table['name'] = 'glpi_plugin_fusioninventory_snmpmodelmiblabels';
      $a_table['oldname'] = array('glpi_dropdown_plugin_tracker_mib_label',
                                  'glpi_plugin_fusinvsnmp_miblabels');

      $a_table['fields']  = array();
      $a_table['fields']['id']         = array('type'    => 'autoincrement',
                                               'value'   => '');
      $a_table['fields']['name']       = array('type'    => 'string',
                                               'value'   => NULL);
      $a_table['fields']['comment']    = array('type'    => 'text',
                                               'value'   => NULL);

      $a_table['oldfields']  = array();

      $a_table['renamefields'] = array();
      $a_table['renamefields']['ID'] = 'id';
      $a_table['renamefields']['comments'] = 'comment';

      $a_table['keys']   = array();

      $a_table['oldkeys'] = array();

      migrateTablesFusionInventory($migration, $a_table);



   /*
    * Table glpi_plugin_fusioninventory_snmpmodelmibobjects
    */
      $a_table = array();
      $a_table['name'] = 'glpi_plugin_fusioninventory_snmpmodelmibobjects';
      $a_table['oldname'] = array('glpi_dropdown_plugin_tracker_mib_object',
                                  'glpi_plugin_fusinvsnmp_mibobjects');

      $a_table['fields']  = array();
      $a_table['fields']['id']         = array('type'    => 'autoincrement',
                                               'value'   => '');
      $a_table['fields']['name']       = array('type'    => 'string',
                                               'value'   => NULL);
      $a_table['fields']['comment']    = array('type'    => 'text',
                                               'value'   => NULL);

      $a_table['oldfields']  = array();

      $a_table['renamefields'] = array();
      $a_table['renamefields']['ID'] = 'id';
      $a_table['renamefields']['comments'] = 'comment';

      $a_table['keys']   = array();

      $a_table['oldkeys'] = array();

      migrateTablesFusionInventory($migration, $a_table);



   /*
    * Table glpi_plugin_fusioninventory_snmpmodelmiboids
    */
      $newTable = "glpi_plugin_fusioninventory_snmpmodelmiboids";
      $migration->renameTable("glpi_dropdown_plugin_tracker_mib_oid",
                              $newTable);
      $migration->renameTable("glpi_plugin_fusinvsnmp_miboids",
                              $newTable);
      if (!TableExists($newTable)) {
         $query = "CREATE TABLE `".$newTable."` (
                     `id` int(11) NOT NULL AUTO_INCREMENT,
                      PRIMARY KEY (`id`)
                  ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1";
         $DB->query($query);
      }
         $migration->changeField($newTable,
                                 "ID",
                                 "id",
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 "id",
                                 "id",
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 "name",
                                 "name",
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->changeField($newTable,
                                 "comment",
                                 "comment",
                                 "text COLLATE utf8_unicode_ci DEFAULT NULL");
      $migration->migrationOneTable($newTable);
         $migration->changeField($newTable,
                                 "ID",
                                 "id",
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 "comments",
                                 "comment",
                                 "text COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->dropField($newTable,
                               "plugin_fusinvsnmp_constructdevices_id");
         $migration->dropField($newTable,
                               "oid_port_counter");
         $migration->dropField($newTable,
                               "oid_port_dyn");
         $migration->dropField($newTable,
                               "itemtype");
         $migration->dropField($newTable,
                               "vlan");
      $migration->migrationOneTable($newTable);
         $migration->addField($newTable,
                                 "id",
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->addField($newTable,
                                 "name",
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addField($newTable,
                                 "comment",
                                 "text COLLATE utf8_unicode_ci DEFAULT NULL");
      $migration->migrationOneTable($newTable);
      $DB->list_fields($newTable, FALSE);



   /*
    * glpi_plugin_fusioninventory_configlogfields
    */
      $newTable = "glpi_plugin_fusioninventory_configlogfields";
      $migration->renameTable("glpi_plugin_fusioninventory_config_snmp_history",
                              $newTable);
      $migration->renameTable("glpi_plugin_fusinvsnmp_configlogfields",
                              $newTable);
      if (TableExists($newTable)) {
         if (FieldExists($newTable, "field")) {
            $query = "SELECT * FROM `".$newTable."`";
            $result=$DB->query($query);
            while ($data=$DB->fetch_array($result)) {
               $pfMapping = new PluginFusioninventoryMapping();
               $mapping = 0;
               if ($mapping = $pfMapping->get("NetworkEquipment", $data['field'])) {
                  $queryu = "UPDATE `".$newTable."`
                     SET `field`='".$mapping['id']."'
                     WHERE `field`='".$data['field']."'";
                  $DB->query($queryu);
               }
            }
         }
      }
      if (!TableExists($newTable)) {
         $query = "CREATE TABLE `".$newTable."` (
                     `id` int(8) NOT NULL AUTO_INCREMENT,
                      PRIMARY KEY (`id`)
                  ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1";
         $DB->query($query);
      }
         $migration->changeField($newTable,
                                 "ID",
                                 "id",
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 "id",
                                 "id",
                                 "int(8) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 "plugin_fusioninventory_mappings_id",
                                 "plugin_fusioninventory_mappings_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "days",
                                 "days",
                                 "int(255) NOT NULL DEFAULT '-1'");
      $migration->migrationOneTable($newTable);
         $migration->changeField($newTable,
                                 "ID",
                                 "id",
                                 "int(8) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 "field",
                                 "plugin_fusioninventory_mappings_id",
                                 "int(11) NOT NULL DEFAULT '0'");
      $migration->migrationOneTable($newTable);
         $migration->addField($newTable,
                                 "id",
                                 "int(8) NOT NULL AUTO_INCREMENT");
         $migration->addField($newTable,
                                 "plugin_fusioninventory_mappings_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 "days",
                                 "int(255) NOT NULL DEFAULT '-1'");
         $migration->addKey($newTable,
                            "plugin_fusioninventory_mappings_id");
      $migration->migrationOneTable($newTable);
      $DB->list_fields($newTable, FALSE);

         $configLogField = new PluginFusioninventoryConfigLogField();
         $configLogField->initConfig();



   /*
    * glpi_plugin_fusioninventory_snmpmodelconstructdevices
    */
      $newTable = "glpi_plugin_fusioninventory_snmpmodelconstructdevices";
      $migration->renameTable("glpi_plugin_fusinvsnmp_constructdevices",
                        $newTable);
      if (!TableExists($newTable)) {
         $query = "CREATE TABLE `".$newTable."` (
                     `id` int(11) NOT NULL AUTO_INCREMENT,
                      PRIMARY KEY (`id`)
                  ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1";
         $DB->query($query);
      }
         $migration->changeField($newTable,
                                 "ID",
                                 "id",
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 "have_someinformations",
                                 "have_someinformations",
                                 "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "have_importantinformations",
                                 "have_importantinformations",
                                 "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "have_ports",
                                 "have_ports",
                                 "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "have_portsconnections",
                                 "have_portsconnections",
                                 "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "have_vlan",
                                 "have_vlan",
                                 "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "have_trunk",
                                 "have_trunk",
                                 "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "released",
                                 "released",
                                 "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "snmpmodel_id",
                                 "plugin_fusioninventory_snmpmodels_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "plugin_fusinvsnmp_models_id",
                                 "plugin_fusioninventory_snmpmodels_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "plugin_fusioninventory_snmpmodels_id",
                                 "plugin_fusioninventory_snmpmodels_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "FK_glpi_enterprise",
                                 "manufacturers_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "type",
                                 "itemtype",
                                 "varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->dropField($newTable, "device");
         $migration->dropField($newTable, "firmware");
   $migration->migrationOneTable($newTable);
      $migration->addField($newTable,
                           "manufacturers_id",
                           "int(11) NOT NULL DEFAULT '0'");
      $migration->addField($newTable,
                           "sysdescr",
                           "text DEFAULT NULL");
      $migration->addField($newTable,
                           "itemtype",
                           "varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL");
      $migration->addField($newTable,
                           "plugin_fusioninventory_snmpmodels_id",
                           "int(11) NOT NULL DEFAULT '0'");
      $migration->addField($newTable,
                           "networkmodel_id",
                           "int(11) NOT NULL DEFAULT '0'");
      $migration->addField($newTable,
                           "printermodel_id",
                           "int(11) NOT NULL DEFAULT '0'");
      $migration->addField($newTable,
                           "have_someinformations",
                           "tinyint(1) NOT NULL DEFAULT '0'");
      $migration->addField($newTable,
                           "have_importantinformations",
                           "tinyint(1) NOT NULL DEFAULT '0'");
      $migration->addField($newTable,
                           "have_ports",
                           "tinyint(1) NOT NULL DEFAULT '0'");
      $migration->addField($newTable,
                           "have_portsconnections",
                           "tinyint(1) NOT NULL DEFAULT '0'");
      $migration->addField($newTable,
                           "have_vlan",
                           "tinyint(1) NOT NULL DEFAULT '0'");
      $migration->addField($newTable,
                           "have_trunk",
                           "tinyint(1) NOT NULL DEFAULT '0'");
      $migration->addField($newTable,
                           "released",
                           "tinyint(1) NOT NULL DEFAULT '0'");
      $migration->addField($newTable,
                           "releasedsnmpmodel_id",
                           "int(11) NOT NULL DEFAULT '0'");
   $migration->migrationOneTable($newTable);
   $DB->list_fields($newTable, FALSE);




   /*
    * Table glpi_plugin_fusioninventory_snmpmodelconstructdevices_users
    */
      $a_table = array();
      $a_table['name'] = 'glpi_plugin_fusioninventory_snmpmodelconstructdevices_users';
      $a_table['oldname'] = array('glpi_plugin_fusinvsnmp_constructdevices_users');

      $a_table['fields']  = array();
      $a_table['fields']['id']       = array('type'    => 'autoincrement',
                                             'value'   => '');
      $a_table['fields']['users_id'] = array('type'    => 'integer',
                                             'value'   => NULL);
      $a_table['fields']['login']    = array('type'    => 'string',
                                             'value'   => NULL);
      $a_table['fields']['password'] = array('type'    => 'string',
                                             'value'   => NULL);
      $a_table['fields']['key']      = array('type'    => 'string',
                                             'value'   => NULL);

      $a_table['oldfields']  = array();

      $a_table['renamefields'] = array();

      $a_table['keys']   = array();
      $a_table['keys'][] = array('field' => 'users_id', 'name' => '', 'type' => 'INDEX');

      $a_table['oldkeys'] = array();

      migrateTablesFusionInventory($migration, $a_table);



   /*
    * Table glpi_plugin_fusioninventory_snmpmodelconstructdevice_miboids
    */
      $newTable = "glpi_plugin_fusioninventory_snmpmodelconstructdevice_miboids";
      $migration->renameTable("glpi_plugin_fusinvsnmp_constructdevice_miboids",
                              $newTable);
      // Update with mapping
      if (TableExists($newTable)) {
         if (FieldExists($newTable, "mapping_name")
                 AND FieldExists($newTable, "itemtype")) {
            $query = "SELECT * FROM `".$newTable."`
               GROUP BY `itemtype`, `mapping_type`";
            $result=$DB->query($query);
            while ($data=$DB->fetch_array($result)) {
               if (!is_numeric($data['mapping_name'])) {
                  $pfMapping = new PluginFusioninventoryMapping();
                  $mapping = 0;
                  $mapping_type = '';
                  if ($data['itemtype'] == 'glpi_networkequipments') {
                     $mapping_type = 'NetworkEquipment';
                  } else if ($data['itemtype'] == 'glpi_printers') {
                     $mapping_type = 'Printer';
                  }
                  if ($mapping = $pfMapping->get($mapping_type, $data['mapping_name'])) {
                     $data['mapping_name'] = $mapping['id'];
                     $queryu = "UPDATE `".$newTable."`
                        SET `mapping_name`='".$mapping['id']."',
                           `mapping_type`='".$mapping_type."'
                        WHERE `itemtype`='".$data['itemtype']."'
                           AND `mapping_name`='".$data['mapping_name']."'";
                     $DB->query($queryu);
                  }
               }
            }
         }
         $migration->changeField($newTable,
                                 "mapping_name",
                                 "plugin_fusioninventory_mappings_id",
                                 "int(11) NOT NULL DEFAULT '0'");
      }
   $migration->migrationOneTable($newTable);
      if (!TableExists($newTable)) {
         $query = "CREATE TABLE `".$newTable."` (
                     `id` int(11) NOT NULL AUTO_INCREMENT,
                      PRIMARY KEY (`id`)
                  ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1";
         $DB->query($query);
      }
         $migration->changeField($newTable,
                                 "ID",
                                 "id",
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 "id",
                                 "id",
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 "mib_oid_id",
                                 "plugin_fusioninventory_snmpmodelmiboids_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "plugin_fusioninventory_snmpmodelmiboids_id",
                                 "plugin_fusioninventory_snmpmodelmiboids_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "plugin_fusinvsnmp_miboids_id",
                                 "plugin_fusioninventory_snmpmodelmiboids_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "construct_device_id",
                                 "plugin_fusioninventory_snmpmodelconstructdevices_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "plugin_fusinvsnmp_constructdevices_id",
                                 "plugin_fusioninventory_snmpmodelconstructdevices_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "plugin_fusioninventory_snmpmodelconstructdevices_id",
                                 "plugin_fusioninventory_snmpmodelconstructdevices_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "plugin_fusioninventory_mappings_id",
                                 "plugin_fusioninventory_mappings_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "oid_port_counter",
                                 "oid_port_counter",
                                 "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "oid_port_dyn",
                                 "oid_port_dyn",
                                 "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "itemtype",
                                 "itemtype",
                                 "varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT ''");
         $migration->changeField($newTable,
                                 "vlan",
                                 "vlan",
                                 "tinyint(1) NOT NULL DEFAULT '0'");
      $migration->migrationOneTable($newTable);
         $migration->dropField($newTable, "mapping_type");
      $migration->migrationOneTable($newTable);
         $migration->addField($newTable,
                                 "id",
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->addField($newTable,
                                 "plugin_fusioninventory_snmpmodelmiboids_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 "plugin_fusioninventory_snmpmodelconstructdevices_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 "plugin_fusioninventory_mappings_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 "oid_port_counter",
                                 "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 "oid_port_dyn",
                                 "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 "itemtype",
                                 "varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT ''");
         $migration->addField($newTable,
                                 "vlan",
                                 "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->addKey($newTable,
                            array("plugin_fusioninventory_snmpmodelmiboids_id",
                                  "plugin_fusioninventory_snmpmodelconstructdevices_id",
                                  "plugin_fusioninventory_mappings_id"),
                            "unicity",
                            "UNIQUE");
      $migration->migrationOneTable($newTable);
      $DB->list_fields($newTable, FALSE);



   /*
    * Table glpi_plugin_fusioninventory_networkportconnectionlogs
    */
      $newTable = "glpi_plugin_fusioninventory_networkportconnectionlogs";
      $migration->renameTable("glpi_plugin_fusinvsnmp_networkportconnectionlogs",
                              $newTable);

      if (!TableExists($newTable)) {
         $DB->query('CREATE TABLE `'.$newTable.'` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        PRIMARY KEY (`id`)
                   ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1');
      }
         $migration->changeField($newTable,
                                 "ID",
                                 "id",
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                              "id",
                              "id",
                              "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                              "date",
                              "date_mod",
                              "datetime NOT NULL DEFAULT '0000-00-00 00:00:00'");
         $migration->changeField($newTable,
                              "date_mod",
                              "date_mod",
                              "datetime NOT NULL DEFAULT '0000-00-00 00:00:00'");
         $migration->changeField($newTable,
                              "creation",
                              "creation",
                              "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                              "FK_port_source",
                              "networkports_id_source",
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                              "networkports_id_source",
                              "networkports_id_source",
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                              "FK_port_destination",
                              "networkports_id_destination",
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                              "networkports_id_destination",
                              "networkports_id_destination",
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                              "plugin_fusioninventory_agentprocesses_id",
                              "plugin_fusioninventory_agentprocesses_id",
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->dropField($newTable, "process_number");
      $migration->migrationOneTable($newTable);
         $migration->addField($newTable,
                              "id",
                              "int(11) NOT NULL AUTO_INCREMENT");
         $migration->addField($newTable,
                              "date_mod",
                              "datetime NOT NULL DEFAULT '0000-00-00 00:00:00'");
         $migration->addField($newTable,
                              "creation",
                              "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              "networkports_id_source",
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              "networkports_id_destination",
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              "plugin_fusioninventory_agentprocesses_id",
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->addKey($newTable,
                            array("networkports_id_source",
                                  "networkports_id_destination",
                                  "plugin_fusioninventory_agentprocesses_id"),
                            "networkports_id_source");
         $migration->addKey($newTable,
                            "date_mod");
      $migration->migrationOneTable($newTable);
      $DB->list_fields($newTable, FALSE);



   /*
    * Table glpi_plugin_fusioninventory_snmpmodelmibs
    */
      $newTable = "glpi_plugin_fusioninventory_snmpmodelmibs";
      $migration->renameTable("glpi_plugin_fusinvsnmp_modelmibs",
                              $newTable);
      $migration->renameTable("glpi_plugin_tracker_mib_networking",
                              $newTable);
      if (FieldExists($newTable, "FK_mib_label")) {
         $query = "UPDATE `".$newTable."`
            SET `FK_mib_label`='0'
            WHERE `FK_mib_label` IS NULL";
         $DB->query($query);
      }
      if (FieldExists($newTable, "plugin_fusinvsnmp_miblabels_id")) {
         $query = "UPDATE `".$newTable."`
            SET `plugin_fusinvsnmp_miblabels_id`='0'
            WHERE `plugin_fusinvsnmp_miblabels_id` IS NULL";
         $DB->query($query);
      }
      if (FieldExists($newTable, "plugin_fusinvsnmp_mibobjects_id")) {
         $query = "UPDATE `".$newTable."`
            SET `plugin_fusinvsnmp_mibobjects_id`='0'
            WHERE `plugin_fusinvsnmp_mibobjects_id` IS NULL";
         $DB->query($query);
      }
      if (!TableExists($newTable)) {
         $DB->query('CREATE TABLE `'.$newTable.'` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        PRIMARY KEY (`id`)
                   ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1');
      }
         $migration->changeField($newTable,
                                 "ID",
                                 "id",
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 "plugin_fusinvsnmp_models_id",
                                 "plugin_fusioninventory_snmpmodels_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "plugin_fusinvsnmp_miblabels_id",
                                 "plugin_fusioninventory_snmpmodelmiblabels_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "plugin_fusinvsnmp_miboids_id",
                                 "plugin_fusioninventory_snmpmodelmiboids_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "plugin_fusinvsnmp_mibobjects_id",
                                 "plugin_fusioninventory_snmpmodelmibobjects_id",
                                 "int(11) NOT NULL DEFAULT '0'");
      $migration->migrationOneTable($newTable);
         $migration->changeField($newTable,
                                 "id",
                                 "id",
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 "plugin_fusioninventory_snmpmodels_id",
                                 "plugin_fusioninventory_snmpmodels_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "plugin_fusioninventory_snmpmodelmiblabels_id",
                                 "plugin_fusioninventory_snmpmodelmiblabels_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "plugin_fusioninventory_snmpmodelmiboids_id",
                                 "plugin_fusioninventory_snmpmodelmiboids_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "plugin_fusioninventory_snmpmodelmibobjects_id",
                                 "plugin_fusioninventory_snmpmodelmibobjects_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "oid_port_counter",
                                 "oid_port_counter",
                                 "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "oid_port_dyn",
                                 "oid_port_dyn",
                                 "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "is_active",
                                 "is_active",
                                 "tinyint(1) NOT NULL DEFAULT '1'");
      $migration->migrationOneTable($newTable);
         $migration->changeField($newTable,
                                 "ID",
                                 "id",
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 "FK_model_infos",
                                 "plugin_fusioninventory_snmpmodels_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "FK_mib_label",
                                 "plugin_fusioninventory_snmpmodelmiblabels_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "plugin_fusinvsnmp_miblabels_id",
                                 "plugin_fusioninventory_snmpmodelmiblabels_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "FK_mib_oid",
                                 "plugin_fusioninventory_snmpmodelmiboids_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "FK_mib_object",
                                 "plugin_fusioninventory_snmpmodelmibobjects_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "plugin_fusinvsnmp_mibobjects_id",
                                 "plugin_fusioninventory_snmpmodelmibobjects_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "oid_port_counter",
                                 "oid_port_counter",
                                 "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "oid_port_dyn",
                                 "oid_port_dyn",
                                 "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              "plugin_fusioninventory_mappings_id",
                              "int(11) NOT NULL DEFAULT '0'");
      $migration->migrationOneTable($newTable);

         // Update with mapping
         if (FieldExists($newTable, "mapping_type")) {
            $query = "SELECT * FROM `".$newTable."`
               GROUP BY `mapping_type`, `mapping_name`";
            $result=$DB->query($query);
            while ($data=$DB->fetch_array($result)) {
               $pfMapping = new PluginFusioninventoryMapping();
               $mapping = 0;
               $mapping_type = '';
               if ($data['mapping_type'] == '2') {
                  $mapping_type == 'NetworkEquipment';
               } else if ($data['mapping_type'] == '3') {
                  $mapping_type == 'Printer';
               }
               if ($mapping = $pfMapping->get($mapping_type, $data['mapping_name'])) {
                  $data['mapping_name'] = $mapping['id'];
                  $queryu = "UPDATE `".$newTable."`
                     SET `plugin_fusioninventory_mappings_id`='".$mapping['id']."',
                        `mapping_type`='".$mapping_type."'
                     WHERE `mapping_type`='".$data['mapping_type']."'
                        AND `mapping_name`='".$data['mapping_name']."'";
                  $DB->query($queryu);
               }
            }
         }
         $migration->dropField($newTable,
                               "mapping_type");
         $migration->dropField($newTable,
                               "mapping_name");
         $migration->dropField($newTable,
                               "name");
         $migration->dropField($newTable,
                               "itemtype");
         $migration->dropField($newTable,
                               "discovery_key");
         $migration->dropField($newTable,
                               "comment");
         $migration->changeField($newTable,
                                 "activation",
                                 "is_active",
                                 "tinyint(1) NOT NULL DEFAULT '1'");
         $migration->changeField($newTable,
                                 "vlan",
                                 "vlan",
                                 "tinyint(1) NOT NULL DEFAULT '0'");

         $migration->dropKey($newTable,
                             "FK_model_infos");
         $migration->dropKey($newTable,
                             "FK_model_infos_2");
         $migration->dropKey($newTable,
                             "FK_model_infos_3");
         $migration->dropKey($newTable,
                             "FK_model_infos_4");
         $migration->dropKey($newTable,
                             "oid_port_dyn");
         $migration->dropKey($newTable,
                             "activation");
      $migration->migrationOneTable($newTable);
         $migration->addField($newTable,
                                 "id",
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->addField($newTable,
                                 "plugin_fusioninventory_snmpmodels_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 "plugin_fusioninventory_snmpmodelmiblabels_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 "plugin_fusioninventory_snmpmodelmiboids_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 "plugin_fusioninventory_snmpmodelmibobjects_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 "oid_port_counter",
                                 "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 "oid_port_dyn",
                                 "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 "plugin_fusioninventory_mappings_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 "is_active",
                                 "tinyint(1) NOT NULL DEFAULT '1'");
         $migration->addField($newTable,
                                 "vlan",
                                 "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->addKey($newTable,
                            "plugin_fusioninventory_snmpmodels_id");
         $migration->addKey($newTable,
                            array("plugin_fusioninventory_snmpmodels_id", "oid_port_dyn"),
                            "plugin_fusioninventory_snmpmodels_id_2");
         $migration->addKey($newTable,
                            array("plugin_fusioninventory_snmpmodels_id",
                                  "oid_port_counter",
                                  "plugin_fusioninventory_mappings_id"),
                            "plugin_fusioninventory_snmpmodels_id_3");
         $migration->addKey($newTable,
                            array("plugin_fusioninventory_snmpmodels_id",
                                  "plugin_fusioninventory_mappings_id"),
                            "plugin_fusioninventory_snmpmodels_id_4");
         $migration->addKey($newTable,
                            "oid_port_dyn");
         $migration->addKey($newTable,
                            "is_active");
         $migration->addKey($newTable,
                            "plugin_fusioninventory_mappings_id");
      $migration->migrationOneTable($newTable);
      $DB->list_fields($newTable, FALSE);



   /*
    * Table glpi_plugin_fusioninventory_snmpmodels
    */
      $newTable = "glpi_plugin_fusioninventory_snmpmodels";
      $migration->renameTable("glpi_plugin_fusinvsnmp_models",
                              $newTable);
      if (!TableExists($newTable)) {
         $DB->query('CREATE TABLE `'.$newTable.'` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        PRIMARY KEY (`id`)
                   ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1');
      }
         $migration->changeField($newTable,
                                 "ID",
                                 "id",
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 "id",
                                 "id",
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 "name",
                                 "name",
                                 "varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT ''");
         $migration->changeField($newTable,
                                 "device_type",
                                 "itemtype",
                                 "varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT ''");
         $migration->changeField($newTable,
                                 "itemtype",
                                 "itemtype",
                                 "varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT ''");
         $migration->changeField($newTable,
                                 "discovery_key",
                                 "discovery_key",
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->changeField($newTable,
                                 "comments",
                                 "comment",
                                 "text COLLATE utf8_unicode_ci");
         $migration->changeField($newTable,
                                 "comment",
                                 "comment",
                                 "text COLLATE utf8_unicode_ci");
      $migration->migrationOneTable($newTable);
         $migration->dropField($newTable, "deleted");
         $migration->dropField($newTable, "FK_entities");
         $migration->dropField($newTable, "activation");
      $migration->migrationOneTable($newTable);
         $migration->addField($newTable,
                              "id",
                              "int(11) NOT NULL AUTO_INCREMENT");
         $migration->addField($newTable,
                              "name",
                              "varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT ''");
         $migration->addField($newTable,
                              "itemtype",
                              "varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT ''");
         $migration->addField($newTable,
                              "discovery_key",
                              "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addField($newTable,
                              "comment",
                              "text COLLATE utf8_unicode_ci");
         $migration->addKey($newTable,
                            "name");
         $migration->addKey($newTable,
                            "itemtype");
      $migration->migrationOneTable($newTable);
      $DB->list_fields($newTable, FALSE);



   /*
    * Table glpi_plugin_fusioninventory_networkporttypes
    */
      $newTable = "glpi_plugin_fusioninventory_networkporttypes";
      $migration->renameTable("glpi_plugin_fusinvsnmp_networkporttypes",
                              $newTable);
      if (!TableExists($newTable)) {
         $query = "CREATE TABLE `".$newTable."` (
                     `id` int(11) NOT NULL AUTO_INCREMENT,
                      PRIMARY KEY (`id`)
                  ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1";
         $DB->query($query);
      }
         $migration->changeField($newTable,
                                 "id",
                                 "id",
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 "name",
                                 "name",
                                 "varchar(255) DEFAULT NULL");
         $migration->changeField($newTable,
                                 "number",
                                 "number",
                                 "int(4) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "othername",
                                 "othername",
                                 "varchar(255) DEFAULT NULL");
         $migration->changeField($newTable,
                                 "import",
                                 "import",
                                 "tinyint(1) NOT NULL DEFAULT '0'");
      $migration->migrationOneTable($newTable);
         $migration->addField($newTable,
                              "name",
                              "varchar(255) DEFAULT NULL");
         $migration->addField($newTable,
                              "number",
                              "int(4) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              "othername",
                              "varchar(255) DEFAULT NULL");
         $migration->addField($newTable,
                              "import",
                              "tinyint(1) NOT NULL DEFAULT '0'");
      $migration->migrationOneTable($newTable);
      $DB->list_fields($newTable, FALSE);



   /*
    * Table glpi_plugin_fusioninventory_printers
    */
      $newTable = "glpi_plugin_fusioninventory_printers";
      $migration->renameTable("glpi_plugin_fusinvsnmp_printers",
                              $newTable);

      $migration->renameTable("glpi_plugin_tracker_printers",
                              $newTable);
      if (!TableExists($newTable)) {
         $DB->query('CREATE TABLE `'.$newTable.'` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        PRIMARY KEY (`id`)
                   ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1');
      }
         $migration->changeField($newTable,
                                 "id",
                                 "id",
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 "printers_id",
                                 "printers_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "sysdescr",
                                 "sysdescr",
                                 "text COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->changeField($newTable,
                                 "plugin_fusinvsnmp_models_id",
                                 "plugin_fusioninventory_snmpmodels_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "plugin_fusioninventory_snmpmodels_id",
                                 "plugin_fusioninventory_snmpmodels_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "plugin_fusinvsnmp_configsecurities_id",
                                 "plugin_fusioninventory_configsecurities_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "plugin_fusioninventory_configsecurities_id",
                                 "plugin_fusioninventory_configsecurities_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "frequence_days",
                                 "frequence_days",
                                 "int(5) NOT NULL DEFAULT '1'");
         $migration->changeField($newTable,
                                 "last_fusioninventory_update",
                                 "last_fusioninventory_update",
                                 "datetime DEFAULT NULL");
      $migration->migrationOneTable($newTable);
         $migration->changeField($newTable,
                                 "ID",
                                 "id",
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 "FK_printers",
                                 "printers_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "FK_model_infos",
                                 "plugin_fusioninventory_snmpmodels_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "FK_snmp_connection",
                                 "plugin_fusioninventory_configsecurities_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "last_tracker_update",
                                 "last_fusioninventory_update",
                                 "datetime DEFAULT NULL");
         $migration->dropKey($newTable,
                             "FK_printers");
         $migration->dropKey($newTable,
                             "FK_snmp_connection");
      $migration->migrationOneTable($newTable);
         $migration->addField($newTable,
                                 "id",
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->addField($newTable,
                                 "printers_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 "sysdescr",
                                 "text COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addField($newTable,
                                 "plugin_fusioninventory_snmpmodels_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 "plugin_fusioninventory_configsecurities_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 "frequence_days",
                                 "int(5) NOT NULL DEFAULT '1'");
         $migration->addField($newTable,
                                 "last_fusioninventory_update",
                                 "datetime DEFAULT NULL");
         $migration->addField($newTable,
                              "serialized_inventory",
                              "longblob");
         $migration->addKey($newTable,
                            "plugin_fusioninventory_configsecurities_id");
         $migration->addKey($newTable,
                            "printers_id");
         $migration->addKey($newTable,
                            "plugin_fusioninventory_snmpmodels_id");
      $migration->migrationOneTable($newTable);
      $DB->list_fields($newTable, FALSE);



   /*
    * Table glpi_plugin_fusioninventory_printerlogs
    */
      $newTable = "glpi_plugin_fusioninventory_printerlogs";
      $migration->renameTable("glpi_plugin_fusinvsnmp_printerlogs",
                              $newTable);
      $migration->renameTable("glpi_plugin_tracker_printers_history",
                              $newTable);
      if (!TableExists($newTable)) {
         $DB->query('CREATE TABLE `'.$newTable.'` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        PRIMARY KEY (`id`)
                   ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1');
      }
         $migration->changeField($newTable,
                                 "id",
                                 "id",
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 "printers_id",
                                 "printers_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "date",
                                 "date",
                                 "datetime NOT NULL DEFAULT '0000-00-00 00:00:00'");
         $migration->changeField($newTable,
                                 "pages_total",
                                 "pages_total",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "pages_n_b",
                                 "pages_n_b",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "pages_color",
                                 "pages_color",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "pages_recto_verso",
                                 "pages_recto_verso",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "scanned",
                                 "scanned",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "pages_total_print",
                                 "pages_total_print",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "pages_n_b_print",
                                 "pages_n_b_print",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "pages_color_print",
                                 "pages_color_print",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "pages_total_copy",
                                 "pages_total_copy",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "pages_n_b_copy",
                                 "pages_n_b_copy",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "pages_color_copy",
                                 "pages_color_copy",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "pages_total_fax",
                                 "pages_total_fax",
                                 "int(11) NOT NULL DEFAULT '0'");
      $migration->migrationOneTable($newTable);
         $migration->changeField($newTable,
                                 "ID",
                                 "id",
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 "FK_printers",
                                 "printers_id",
                                 "int(11) NOT NULL DEFAULT '0'");
      $migration->migrationOneTable($newTable);
         $migration->addField($newTable,
                                 "id",
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->addField($newTable,
                                 "printers_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 "date",
                                 "datetime NOT NULL DEFAULT '0000-00-00 00:00:00'");
         $migration->addField($newTable,
                                 "pages_total",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 "pages_n_b",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 "pages_color",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 "pages_recto_verso",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 "scanned",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 "pages_total_print",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 "pages_n_b_print",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 "pages_color_print",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 "pages_total_copy",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 "pages_n_b_copy",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 "pages_color_copy",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 "pages_total_fax",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addKey($newTable,
                            array("printers_id", "date"),
                            "printers_id");
      $migration->migrationOneTable($newTable);
      $DB->list_fields($newTable, FALSE);



   /*
    *  glpi_plugin_fusioninventory_printercartridges
    */
      $newTable = "glpi_plugin_fusioninventory_printercartridges";
      $migration->renameTable("glpi_plugin_fusinvsnmp_printercartridges",
                              $newTable);
      $migration->renameTable("glpi_plugin_tracker_printers_cartridges",
                              $newTable);
      if (!TableExists($newTable)) {
         $DB->query('CREATE TABLE `'.$newTable.'` (
                        `id` bigint(100) NOT NULL AUTO_INCREMENT,
                        PRIMARY KEY (`id`)
                   ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1');
      }
         $migration->changeField($newTable,
                                 "id",
                                 "id",
                                 "bigint(100) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 "printers_id",
                                 "printers_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "plugin_fusioninventory_mappings_id",
                                 "plugin_fusioninventory_mappings_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "cartridges_id",
                                 "cartridges_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "state",
                                 "state",
                                 "int(3) NOT NULL DEFAULT '100'");
      $migration->migrationOneTable($newTable);
         $migration->changeField($newTable,
                                 "ID",
                                 "id",
                                 "bigint(100) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 "FK_printers",
                                 "printers_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "FK_cartridges",
                                 "cartridges_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              "plugin_fusioninventory_mappings_id",
                              "int(11) NOT NULL DEFAULT '0'");
      $migration->migrationOneTable($newTable);

         // Update with mapping
         if (FieldExists($newTable, "object_name")) {
            $query = "SELECT * FROM `".$newTable."`
               GROUP BY `object_name`";
            $result=$DB->query($query);
            while ($data=$DB->fetch_array($result)) {
               $pfMapping = new PluginFusioninventoryMapping();
               $mapping = 0;
               if (($mapping = $pfMapping->get("Printer", $data['object_name']))) {
                  $DB->query("UPDATE `".$newTable."`
                     SET `plugin_fusioninventory_mappings_id`='".$mapping['id']."'
                        WHERE `object_name`='".$data['object_name']."'");
               }
            }
         }
         $migration->dropField($newTable,
                               "object_name");
      $migration->migrationOneTable($newTable);
         $migration->addField($newTable,
                                 "id",
                                 "bigint(100) NOT NULL AUTO_INCREMENT");
         $migration->addField($newTable,
                                 "printers_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 "plugin_fusioninventory_mappings_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 "cartridges_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 "state",
                                 "int(3) NOT NULL DEFAULT '100'");
         $migration->addKey($newTable,
                            "printers_id");
         $migration->addKey($newTable,
                            "plugin_fusioninventory_mappings_id");
         $migration->addKey($newTable,
                            "cartridges_id");
      $migration->migrationOneTable($newTable);
      $DB->list_fields($newTable, FALSE);



   /*
    * glpi_plugin_fusioninventory_networkports
    */
      $newTable = "glpi_plugin_fusioninventory_networkports";
      $migration->renameTable("glpi_plugin_fusinvsnmp_networkports",
                              $newTable);
      $migration->renameTable("glpi_plugin_tracker_networking_ports",
                              $newTable);
      if (!TableExists($newTable)) {
         $DB->query('CREATE TABLE `'.$newTable.'` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        PRIMARY KEY (`id`)
                   ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1');
      }
         $migration->changeField($newTable,
                                 "id",
                                 "id",
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 "networkports_id",
                                 "networkports_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "ifmtu",
                                 "ifmtu",
                                 "int(8) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "ifspeed",
                                 "ifspeed",
                                 "bigint(50) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "ifinternalstatus",
                                 "ifinternalstatus",
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->changeField($newTable,
                                 "ifconnectionstatus",
                                 "ifconnectionstatus",
                                 "int(8) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "iflastchange",
                                 "iflastchange",
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->changeField($newTable,
                                 "ifinoctets",
                                 "ifinoctets",
                                 "bigint(50) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "ifinerrors",
                                 "ifinerrors",
                                 "bigint(50) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "ifoutoctets",
                                 "ifoutoctets",
                                 "bigint(50) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "ifouterrors",
                                 "ifouterrors",
                                 "bigint(50) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "ifstatus",
                                 "ifstatus",
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->changeField($newTable,
                                 "mac",
                                 "mac",
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->changeField($newTable,
                                 "ifdescr",
                                 "ifdescr",
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->changeField($newTable,
                                 "portduplex",
                                 "portduplex",
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->changeField($newTable,
                                 "trunk",
                                 "trunk",
                                 "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "lastup",
                                 "lastup",
                                 "datetime NOT NULL DEFAULT '0000-00-00 00:00:00'");
      $migration->migrationOneTable($newTable);
         $migration->changeField($newTable,
                                 "ID",
                                 "id",
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 "FK_networking_ports",
                                 "networkports_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "ifmac",
                                 "mac",
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->dropKey($newTable,
                             "FK_networking_ports");
      $migration->migrationOneTable($newTable);
         $migration->addField($newTable,
                                 "id",
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->addField($newTable,
                                 "networkports_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 "ifmtu",
                                 "int(8) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 "ifspeed",
                                 "bigint(50) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 "ifinternalstatus",
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addField($newTable,
                                 "ifconnectionstatus",
                                 "int(8) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 "iflastchange",
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addField($newTable,
                                 "ifinoctets",
                                 "bigint(50) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 "ifinerrors",
                                 "bigint(50) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 "ifoutoctets",
                                 "bigint(50) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 "ifouterrors",
                                 "bigint(50) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 "ifstatus",
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addField($newTable,
                                 "mac",
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addField($newTable,
                                 "ifdescr",
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addField($newTable,
                                 "ifalias",
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addField($newTable,
                                 "portduplex",
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addField($newTable,
                                 "trunk",
                                 "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 "lastup",
                                 "datetime NOT NULL DEFAULT '0000-00-00 00:00:00'");
         $migration->addKey($newTable,
                            "networkports_id");
      $migration->migrationOneTable($newTable);
      $DB->list_fields($newTable, FALSE);



   /*
    * Table glpi_plugin_fusioninventory_networkequipments
    */
      $newTable = "glpi_plugin_fusioninventory_networkequipments";
      $migration->renameTable("glpi_plugin_fusinvsnmp_networkequipments",
                              $newTable);
      $migration->renameTable("glpi_plugin_tracker_networking",
                              $newTable);
      if (!TableExists($newTable)) {
         $DB->query('CREATE TABLE `'.$newTable.'` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        PRIMARY KEY (`id`)
                   ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1');
      }
         $migration->changeField($newTable,
                                 "id",
                                 "id",
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 "networkequipments_id",
                                 "networkequipments_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "sysdescr",
                                 "sysdescr",
                                 "text COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->changeField($newTable,
                                 "plugin_fusioninventory_snmpmodels_id",
                                 "plugin_fusioninventory_snmpmodels_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "plugin_fusioninventory_configsecurities_id",
                                 "plugin_fusioninventory_configsecurities_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "uptime",
                                 "uptime",
                                 "varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "cpu",
                                 "cpu",
                                 "int(3) NOT NULL DEFAULT '0' COMMENT '%'");
         $migration->changeField($newTable,
                                 "memory",
                                 "memory",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "last_fusioninventory_update",
                                 "last_fusioninventory_update",
                                 "datetime DEFAULT NULL");
         $migration->changeField($newTable,
                                 "last_PID_update",
                                 "last_PID_update",
                                 "int(11) NOT NULL DEFAULT '0'");
      $migration->migrationOneTable($newTable);
         $migration->changeField($newTable,
                                 "ID",
                                 "id",
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 "FK_networking",
                                 "networkequipments_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "FK_model_infos",
                                 "plugin_fusioninventory_snmpmodels_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "FK_snmp_connection",
                                 "plugin_fusioninventory_configsecurities_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "last_tracker_update",
                                 "last_fusioninventory_update",
                                 "datetime DEFAULT NULL");
         $migration->changeField($newTable,
                                 "plugin_fusinvsnmp_models_id",
                                 "plugin_fusioninventory_snmpmodels_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "plugin_fusinvsnmp_configsecurities_id",
                                 "plugin_fusioninventory_configsecurities_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->dropKey($newTable,
                             "FK_networking");
         $migration->dropKey($newTable,
                             "FK_model_infos");
      $migration->migrationOneTable($newTable);
         $migration->addField($newTable,
                                 "id",
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->addField($newTable,
                                 "networkequipments_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 "sysdescr",
                                 "text COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addField($newTable,
                                 "plugin_fusioninventory_snmpmodels_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 "plugin_fusioninventory_configsecurities_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 "uptime",
                                 "varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 "cpu",
                                 "int(3) NOT NULL DEFAULT '0' COMMENT '%'");
         $migration->addField($newTable,
                                 "memory",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 "last_fusioninventory_update",
                                 "datetime DEFAULT NULL");
         $migration->addField($newTable,
                                 "last_PID_update",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              "serialized_inventory",
                              "longblob");
         $migration->addKey($newTable,
                            "networkequipments_id");
         $migration->addKey($newTable,
                            array("plugin_fusioninventory_snmpmodels_id",
                                  "plugin_fusioninventory_configsecurities_id"),
                            "plugin_fusioninventory_snmpmodels_id");
      $migration->migrationOneTable($newTable);
      $DB->list_fields($newTable, FALSE);



   /*
    * glpi_plugin_fusioninventory_networkequipmentips
    * Removed in 0.84, but required here for update, we drop in edn of this function
    */
   if (TableExists("glpi_plugin_fusioninventory_networkequipmentips")
           || TableExists("glpi_plugin_fusinvsnmp_networkequipmentips")
           || TableExists("glpi_plugin_tracker_networking_ifaddr")) {
      $newTable = "glpi_plugin_fusioninventory_networkequipmentips";
      $migration->renameTable("glpi_plugin_fusinvsnmp_networkequipmentips",
                              $newTable);
      $migration->renameTable("glpi_plugin_tracker_networking_ifaddr",
                              $newTable);
      if (!TableExists($newTable)) {
         $DB->query('CREATE TABLE `'.$newTable.'` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        PRIMARY KEY (`id`)
                   ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1');
      }
         $migration->changeField($newTable,
                                 "id",
                                 "id",
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 "networkequipments_id",
                                 "networkequipments_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "ip",
                                 "ip",
                                 "varchar(255) DEFAULT NULL");
      $migration->migrationOneTable($newTable);
         $migration->changeField($newTable,
                                 "ID",
                                 "id",
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 "FK_networking",
                                 "networkequipments_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "ifaddr",
                                 "ip",
                                 "varchar(255) DEFAULT NULL");
         $migration->dropKey($newTable,
                             "ifaddr");
      $migration->migrationOneTable($newTable);
         $migration->addField($newTable,
                                 "id",
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->addField($newTable,
                                 "networkequipments_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 "ip",
                                 "varchar(255) DEFAULT NULL");
         $migration->addKey($newTable,
                            "ip");
         $migration->addKey($newTable,
                            "networkequipments_id");
      $migration->migrationOneTable($newTable);
      $DB->list_fields($newTable, FALSE);
   }


   /*
    * Table glpi_plugin_fusioninventory_networkportlogs
    */
      $newTable = "glpi_plugin_fusioninventory_networkportlogs";
         if (TableExists("glpi_plugin_tracker_snmp_history")) {
            // **** Update history
            update213to220_ConvertField($migration);

            // **** Migration network history connections
            $query = "SELECT count(ID) FROM `glpi_plugin_tracker_snmp_history`
                              WHERE `Field`='0'";
            $result = $DB->query($query);
            $datas = $DB->fetch_assoc($result);
            $nb = $datas['count(ID)'];

            echo "Move Connections history to another table...";

            for ($i=0; $i < $nb; $i = $i + 500) {
               $migration->displayMessage("$i / $nb");
               $sql_connection = "SELECT * FROM `glpi_plugin_tracker_snmp_history`
                                 WHERE `Field`='0'
                                 ORDER BY `FK_process` DESC, `date_mod` DESC
                                 LIMIT 500";
               $result_connection = $DB->query($sql_connection);
               while ($thread_connection = $DB->fetch_array($result_connection)) {
                  $input = array();
                  $input['process_number'] = $thread_connection['FK_process'];
                  $input['date'] = $thread_connection['date_mod'];
                  if (($thread_connection["old_device_ID"] != "0")
                          OR ($thread_connection["new_device_ID"] != "0")) {

                     if ($thread_connection["old_device_ID"] != "0") {
                        // disconnection
                        $input['creation'] = '0';
                     } else if ($thread_connection["new_device_ID"] != "0") {
                        // connection
                        $input['creation'] = '1';
                     }
                     $input['FK_port_source'] = $thread_connection["FK_ports"];
                     $dataPort = array();
                     if ($thread_connection["old_device_ID"] != "0") {
                        $queryPort = "SELECT *
                                      FROM `glpi_networkports`
                                      WHERE `mac`='".$thread_connection['old_value']."'
                                      LIMIT 1";
                        $resultPort = $DB->query($queryPort);
                        $dataPort = $DB->fetch_assoc($resultPort);
                     } else if ($thread_connection["new_device_ID"] != "0") {
                        $queryPort = "SELECT *
                                      FROM `glpi_networkports`
                                      WHERE `mac`='".$thread_connection['new_value']."'
                                      LIMIT 1";
                        $resultPort = $DB->query($queryPort);
                        $dataPort = $DB->fetch_assoc($resultPort);
                     }
                     if (isset($dataPort['id'])) {
                        $input['FK_port_destination'] = $dataPort['id'];
                     } else {
                        $input['FK_port_destination'] = 0;
                     }

                     $query_ins = "INSERT INTO `glpi_plugin_fusinvsnmp_networkportconnectionlogs`
                        (`date_mod`, `creation`, `networkports_id_source`,
                         `networkports_id_destination`)
                        VALUES ('".$input['date']."',
                                '".$input['creation']."',
                                '".$input['FK_port_source']."',
                                '".$input['FK_port_destination']."')";
                     $DB->query($query_ins);
                  }
               }
            }
            $query_del = "DELETE FROM `glpi_plugin_tracker_snmp_history`
               WHERE `Field`='0'
               AND (`old_device_ID`!='0' OR `new_device_ID`!='0')";
            $DB->query($query_del);
            $migration->displayMessage("$nb / $nb");
         }

      $migration->renameTable("glpi_plugin_fusinvsnmp_networkportlogs",
                              $newTable);
      $migration->renameTable("glpi_plugin_tracker_snmp_history",
                              $newTable);
      if (!TableExists($newTable)) {
         $query = "CREATE TABLE `".$newTable."` (
                     `id` int(11) NOT NULL AUTO_INCREMENT,
                      PRIMARY KEY (`id`)
                  ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1";
         $DB->query($query);
      }
         $migration->changeField($newTable,
                                 "id",
                                 "id",
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 "networkports_id",
                                 "networkports_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "plugin_fusioninventory_mappings_id",
                                 "plugin_fusioninventory_mappings_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "date_mod",
                                 "date_mod",
                                 "datetime DEFAULT NULL");
         $migration->changeField($newTable,
                                 "value_old",
                                 "value_old",
                                 "varchar(255) DEFAULT NULL");
         $migration->changeField($newTable,
                                 "value_new",
                                 "value_new",
                                 "varchar(255) DEFAULT NULL");
         $migration->changeField($newTable,
                                 "plugin_fusioninventory_agentprocesses_id",
                                 "plugin_fusioninventory_agentprocesses_id",
                                 "int(11) NOT NULL DEFAULT '0'");
      $migration->migrationOneTable($newTable);
         $migration->changeField($newTable,
                                 "ID",
                                 "id",
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 "FK_ports",
                                 "networkports_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              "plugin_fusioninventory_mappings_id",
                              "int(11) NOT NULL DEFAULT '0'");
      $migration->migrationOneTable($newTable);

         // Update with mapping
         if (FieldExists($newTable, "Field")) {
//            $pfNetworkPortLog = new PluginFusioninventoryNetworkPortLog();
            $pfMapping = new PluginFusioninventoryMapping();
            $query = "SELECT * FROM `".$newTable."`
               GROUP BY `Field`";
            $result=$DB->query($query);
            while ($data=$DB->fetch_array($result)) {
               $mapping = 0;
               if ($mapping = $pfMapping->get("NetworkEquipment", $data['Field'])) {
                  $DB->query("UPDATE `".$newTable."`
                     SET `plugin_fusioninventory_mappings_id`='".$mapping['id']."'
                     WHERE `Field`='".$data['Field']."'
                        AND `plugin_fusioninventory_mappings_id`!='".$mapping['id']."'");
               }
            }
         }
         $migration->dropField($newTable,
                            "Field");
         $migration->changeField($newTable,
                                 "old_value",
                                 "value_old",
                                 "varchar(255) DEFAULT NULL");
         $migration->dropField($newTable,
                               "old_device_type");
         $migration->dropField($newTable,
                               "old_device_ID");
         $migration->changeField($newTable,
                                 "new_value",
                                 "value_new",
                                 "varchar(255) DEFAULT NULL");
         $migration->dropField($newTable,
                               "new_device_type");
         $migration->dropField($newTable,
                               "new_device_ID");
         $migration->dropField($newTable, "FK_process");
         $migration->dropKey($newTable, "FK_process");
         $migration->dropKey($newTable,
                             "FK_ports");
      $migration->migrationOneTable($newTable);
         $migration->addField($newTable,
                                 "id",
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->addField($newTable,
                                 "networkports_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 "plugin_fusioninventory_mappings_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 "date_mod",
                                 "datetime DEFAULT NULL");
         $migration->addField($newTable,
                                 "value_old",
                                 "varchar(255) DEFAULT NULL");
         $migration->addField($newTable,
                                 "value_new",
                                 "varchar(255) DEFAULT NULL");
         $migration->addField($newTable,
                                 "plugin_fusioninventory_agentprocesses_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addKey($newTable,
                            array("networkports_id", "date_mod"),
                            "networkports_id");
         $migration->addKey($newTable,
                            "plugin_fusioninventory_mappings_id");
         $migration->addKey($newTable,
                            "plugin_fusioninventory_agentprocesses_id");
         $migration->addKey($newTable,
                            "date_mod");
      $migration->migrationOneTable($newTable);
      $DB->list_fields($newTable, FALSE);



   /*
    * Table glpi_plugin_fusioninventory_configsecurities
    */
      // TODO get info to create SNMP authentification with old values of Tracker plugin
      $newTable = "glpi_plugin_fusioninventory_configsecurities";
      $migration->renameTable("glpi_plugin_fusinvsnmp_configsecurities",
                              $newTable);
      $migration->renameTable("glpi_plugin_tracker_snmp_connection",
                              $newTable);
      if (!TableExists($newTable)) {
         $DB->query('CREATE TABLE `'.$newTable.'` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        PRIMARY KEY (`id`)
                   ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1');
      }
         $migration->changeField($newTable,
                                 "id",
                                 "id",
                                 "int(11) NOT NULL AUTO_INCREMENT");
          $migration->changeField($newTable,
                                 "name",
                                 "name",
                                 "varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->changeField($newTable,
                                 "snmpversion",
                                 "snmpversion",
                                 "varchar(8) COLLATE utf8_unicode_ci NOT NULL DEFAULT '1'");
         $migration->changeField($newTable,
                                 "community",
                                 "community",
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->changeField($newTable,
                                 "username",
                                 "username",
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->changeField($newTable,
                                 "authentication",
                                 "authentication",
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->changeField($newTable,
                                 "auth_passphrase",
                                 "auth_passphrase",
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->changeField($newTable,
                                 "encryption",
                                 "encryption",
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->changeField($newTable,
                                 "priv_passphrase",
                                 "priv_passphrase",
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->changeField($newTable,
                                 "is_deleted",
                                 "is_deleted",
                                 "tinyint(1) NOT NULL DEFAULT '0'");
      $migration->migrationOneTable($newTable);
         $migration->changeField($newTable,
                                 "ID",
                                 "id",
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 "FK_snmp_version",
                                 "snmpversion",
                                 "varchar(8) COLLATE utf8_unicode_ci NOT NULL DEFAULT '1'");
         $migration->changeField($newTable,
                                 "sec_name",
                                 "username",
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->dropField($newTable,
                               "sec_level");
         $migration->dropField($newTable,
                               "auth_protocol");
         $migration->dropField($newTable,
                               "priv_protocol");
         $migration->dropField($newTable,
                               "deleted");
      $migration->migrationOneTable($newTable);
         $migration->addField($newTable,
                                 "id",
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->addField($newTable,
                                 "name",
                                 "varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addField($newTable,
                                 "snmpversion",
                                 "varchar(8) COLLATE utf8_unicode_ci NOT NULL DEFAULT '1'");
         $migration->addField($newTable,
                                 "community",
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addField($newTable,
                                 "username",
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addField($newTable,
                                 "authentication",
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addField($newTable,
                                 "auth_passphrase",
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addField($newTable,
                                 "encryption",
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addField($newTable,
                                 "priv_passphrase",
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addField($newTable,
                                 "is_deleted",
                                 "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->addKey($newTable,
                            "snmpversion");
         $migration->addKey($newTable,
                            "is_deleted");
      $migration->migrationOneTable($newTable);
      $DB->list_fields($newTable, FALSE);



   /*
    *  glpi_plugin_fusioninventory_statediscoveries
    */
      $newTable = "glpi_plugin_fusioninventory_statediscoveries";
      $migration->renameTable("glpi_plugin_fusinvsnmp_statediscoveries",
                              $newTable);
      if (!TableExists($newTable)) {
         $DB->query("CREATE TABLE `".$newTable."` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        PRIMARY KEY (`id`)
                   ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1");
      }
         $migration->changeField($newTable,
                                 "id",
                                 "id",
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 "plugin_fusioninventory_taskjob_id",
                                 "plugin_fusioninventory_taskjob_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "plugin_fusioninventory_agents_id",
                                 "plugin_fusioninventory_agents_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "start_time",
                                 "start_time",
                                 "datetime NOT NULL DEFAULT '0000-00-00 00:00:00'");
         $migration->changeField($newTable,
                                 "end_time",
                                 "end_time",
                                 "datetime NOT NULL DEFAULT '0000-00-00 00:00:00'");
         $migration->changeField($newTable,
                                 "date_mod",
                                 "date_mod",
                                 "datetime DEFAULT NULL");
         $migration->changeField($newTable,
                                 "threads",
                                 "threads",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "nb_ip",
                                 "nb_ip",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "nb_found",
                                 "nb_found",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "nb_error",
                                 "nb_error",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "nb_exists",
                                 "nb_exists",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 "nb_import",
                                 "nb_import",
                                 "int(11) NOT NULL DEFAULT '0'");
      $migration->migrationOneTable($newTable);
         $migration->addField($newTable,
                                 "id",
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->addField($newTable,
                                 "plugin_fusioninventory_taskjob_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 "plugin_fusioninventory_agents_id",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 "start_time",
                                 "datetime NOT NULL DEFAULT '0000-00-00 00:00:00'");
         $migration->addField($newTable,
                                 "end_time",
                                 "datetime NOT NULL DEFAULT '0000-00-00 00:00:00'");
         $migration->addField($newTable,
                                 "date_mod",
                                 "datetime DEFAULT NULL");
         $migration->addField($newTable,
                                 "threads",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 "nb_ip",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 "nb_found",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 "nb_error",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 "nb_exists",
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 "nb_import",
                                 "int(11) NOT NULL DEFAULT '0'");
      $migration->migrationOneTable($newTable);
      $DB->list_fields($newTable, FALSE);



   /*
    * Table glpi_plugin_fusioninventory_computerlicenseinfos
    */
      if (TableExists("glpi_plugin_fusinvinventory_licenseinfos")) {
         $DB->query("UPDATE `glpi_plugin_fusinvinventory_licenseinfos`"
                 ." SET `softwarelicenses_id`='0'"
                 ." WHERE `softwarelicenses_id` IS NULL");
      }
      $a_table = array();
      $a_table['name'] = 'glpi_plugin_fusioninventory_computerlicenseinfos';
      $a_table['oldname'] = array('glpi_plugin_fusinvinventory_licenseinfos');

      $a_table['fields']  = array();
      $a_table['fields']['id']                  = array('type'    => 'autoincrement',
                                                        'value'   => '');
      $a_table['fields']['computers_id']        = array('type'    => 'integer',
                                                        'value'   => NULL);
      $a_table['fields']['softwarelicenses_id'] = array('type'    => 'integer',
                                                        'value'   => NULL);
      $a_table['fields']['name']                = array('type'    => 'string',
                                                        'value'   => NULL);
      $a_table['fields']['fullname']            = array('type'    => 'string',
                                                        'value'   => NULL);
      $a_table['fields']['serial']              = array('type'    => 'string',
                                                        'value'   => NULL);
      $a_table['fields']['is_trial']            = array('type'    => 'bool',
                                                        'value'   => NULL);
      $a_table['fields']['is_update']           = array('type'    => 'bool',
                                                        'value'   => NULL);
      $a_table['fields']['is_oem']              = array('type'    => 'bool',
                                                        'value'   => NULL);
      $a_table['fields']['activation_date']     = array('type'    => 'datetime',
                                                        'value'   => NULL);

      $a_table['oldfields']  = array();

      $a_table['renamefields'] = array();

      $a_table['keys']   = array();
      $a_table['keys'][] = array('field' => 'name', 'name' => '', 'type' => 'INDEX');
      $a_table['keys'][] = array('field' => 'fullname', 'name' => '', 'type' => 'INDEX');

      $a_table['oldkeys'] = array();

      migrateTablesFusionInventory($migration, $a_table);



   /*
    * Table glpi_plugin_fusioninventory_computerarchs
    */
      $a_table = array();
      $a_table['name'] = 'glpi_plugin_fusioninventory_computerarchs';
      $a_table['oldname'] = array();

      $a_table['fields']  = array();
      $a_table['fields']['id']      = array('type'    => 'autoincrement',
                                            'value'   => '');
      $a_table['fields']['name']    = array('type'    => 'string',
                                            'value'   => NULL);
      $a_table['fields']['comment'] = array('type'    => 'text',
                                            'value'   => NULL);

      $a_table['oldfields']  = array();

      $a_table['renamefields'] = array();

      $a_table['keys']   = array();
      $a_table['keys'][] = array('field' => 'name', 'name' => '', 'type' => 'INDEX');

      $a_table['oldkeys'] = array();

      migrateTablesFusionInventory($migration, $a_table);


   /*
    * Deploy Update Begin
    */
      /*
       * glpi_plugin_fusioninventory_deployfiles
       */
      $a_table = array();

      $a_table['name'] = 'glpi_plugin_fusioninventory_deployfiles';

      $a_table['oldname'] = array(
      );

      $a_table['fields'] = array(
         'id' =>  array(
                  'type'   => 'autoincrement',
                  'value'  => NULL
         ),
         'name' => array(
                  'type'   => 'varchar(255) NOT NULL',
                  'value'  => NULL
         ),
         'mimetype' => array(
                  'type'   => 'varchar(255) NOT NULL',
                  'value'  => NULL
         ),
         'filesize' => array(
                  'type' => 'bigint(20) NOT NULL',
                  'value' => NULL
         ),
         'comment' => array(
                  'type'   => 'text DEFAULT NULL',
                  'value'  => NULL
         ),
         'sha512' => array(
                  'type'   => 'char(128) NOT NULL',
                  'value'  => NULL
         ),
         'shortsha512' => array(
                  'type'   => 'char(6) NOT NULL',
                  'value'  => NULL
         ),
         'entities_id' => array(
                  'type'   => 'int(11) NOT NULL',
                  'value'  => NULL
         ),
         'is_recursive' => array(
                  'type'   => 'tinyint(1) NOT NULL DEFAULT 0',
                  'value'  => 0
         ),
         'date_mod' => array(
                  'type'   => 'datetime DEFAULT NULL',
                  'value'  => NULL
         ),

      );

      $a_table['oldfields'] = array(
      );

      $a_table['renamefields'] = array(
      );

      $a_table['keys'] = array(
         array(
            'field' => 'id',
            'name' => '',
            'type' => 'KEY'
         ),
         array(
            'field' => 'shortsha512',
            'name' => '',
            'type' => 'KEY'
         ),
         array(
            'field' => 'entities_id',
            'name' => '',
            'type' => 'KEY'
         ),
         array(
            'field' => 'date_mod',
            'name' => '',
            'type' => 'KEY'
         ),
      );

      $a_table['oldkeys'] = array(
      );

      migrateTablesFusionInventory($migration, $a_table);


      /*
       * glpi_plugin_fusioninventory_deployorders
       */

      $a_table = array();

      //table name
      $a_table['name'] = 'glpi_plugin_fusioninventory_deployorders';
      $a_table['oldname'] = array(
         'glpi_plugin_fusinvdeploy_orders'
      );

      $a_table['fields'] = array(
         'id' =>  array(
                  'type' => 'autoincrement',
                  'value' => NULL
         ),
         'type' =>  array(
                  'type' => 'int(11) NOT NULL',
                  'value' => NULL
         ),
         'create_date' =>  array(
                  'type' => ' datetime NOT NULL',
                  'value' => NULL
         ),
         'plugin_fusioninventory_deploypackages_id' =>  array(
                  'type' => 'int(11) NOT NULL',
                  'value' => NULL
         ),
         'json' =>  array(
                  'type' => 'longtext DEFAULT NULL',
                  'value' => NULL
         ),
      );

      $a_table['oldfields'] = array(
      );

      $a_table['renamefields'] = array(
         'plugin_fusinvdeploy_packages_id' => 'plugin_fusioninventory_deploypackages_id'
      );

      $a_table['keys'] = array(
         array(
            'field' => 'type',
            'name' => '',
            'type' => 'KEY'
         ),
         array(
            'field' => 'create_date',
            'name' => '',
            'type' => 'KEY'
         ),
         array(
            'field' => 'plugin_fusioninventory_deploypackages_id',
            'name' => '',
            'type' => 'KEY'
         ),
      );

      $a_table['oldkeys'] = array(
         'plugin_fusinvdeploy_packages_id',
      );

      migrateTablesFusionInventory($migration, $a_table);

      /*
       * glpi_plugin_fusioninventory_deploypackages
       */

      $a_table = array();

      //table name
      $a_table['name'] = 'glpi_plugin_fusioninventory_deploypackages';
      $a_table['oldname'] = array(
         'glpi_plugin_fusinvdeploy_packages'
      );

      $a_table['fields'] = array(
         'id' =>  array(
                  'type' => 'autoincrement',
                  'value' => NULL
         ),
         'name' =>  array(
                  'type' => 'varchar(255) COLLATE utf8_unicode_ci NOT NULL',
                  'value' => NULL
         ),
         'comment' =>  array(
                  'type' => "text",
                  'value' => NULL
         ),
         'entities_id' =>  array(
                  'type' => 'int(11) NOT NULL',
                  'value' => NULL
         ),
         'is_recursive' =>  array(
                  'type' => 'tinyint(1) NOT NULL DEFAULT 0',
                  'value' => NULL
         ),
         'date_mod' =>  array(
                  'type' => 'datetime DEFAULT NULL',
                  'value' => NULL
         ),
      );

      $a_table['oldfields'] = array(
      );

      $a_table['renamefields'] = array(
      );

      $a_table['keys'] = array(
         array(
            'field' => 'entities_id',
            'name' => '',
            'type' => 'KEY'
         ),
         array(
            'field' => 'date_mod',
            'name' => '',
            'type' => 'KEY'
         ),
      );

      $a_table['oldkeys'] = array(
      );

      migrateTablesFusionInventory($migration, $a_table);

      /*
       * glpi_plugin_fusioninventory_deploymirrors
       */

      $a_table = array();

      //table name
      $a_table['name'] = 'glpi_plugin_fusioninventory_deploymirrors';
      $a_table['oldname'] = array(
         'glpi_plugin_fusinvdeploy_mirrors'
      );

      $a_table['fields'] = array(
         'id' =>  array(
            'type' => 'autoincrement',
            'value' => NULL
         ),
         'entities_id' =>  array(
            'type' => 'int(11) NOT NULL',
            'value' => NULL
         ),
         'is_recursive' =>  array(
            'type' => 'tinyint(1) NOT NULL DEFAULT 0',
            'value' => NULL
         ),
         'name' =>  array(
            'type' => 'varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL',
            'value' => NULL
         ),
         'url' =>  array(
            'type' => "varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci".
                      " NOT NULL DEFAULT ''",
            'value' => NULL
         ),
         'locations_id' => array(
            'type' => 'int(11) NOT NULL',
            'value' => 0
         ),
         'comment' =>  array(
            'type' => "text",
            'value' => NULL
         ),
         'date_mod' =>  array(
            'type' => 'datetime DEFAULT NULL',
            'value' => NULL
         ),
      );

      $a_table['oldfields'] = array(
      );

      $a_table['renamefields'] = array(
      );

      $a_table['keys'] = array(
         array(
            'field' => 'entities_id',
            'name' => '',
            'type' => 'KEY'
         ),
         array(
            'field' => 'date_mod',
            'name' => '',
            'type' => 'KEY'
         ),
      );

      $a_table['oldkeys'] = array(
      );

      migrateTablesFusionInventory($migration, $a_table);


      /*
       * glpi_plugin_fusioninventory_deploygroups
       */

      $a_table = array();

      //table name
      $a_table['name'] = 'glpi_plugin_fusioninventory_deploygroups';
      $a_table['oldname'] = array(
         'glpi_plugin_fusinvdeploy_groups'
      );

      $a_table['fields'] = array(
         'id' =>  array(
            'type' => 'autoincrement',
            'value' => NULL
         ),
         'name' =>  array(
            'type' => 'varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL',
            'value' => NULL
         ),
         'comment' =>  array(
            'type' => "text",
            'value' => NULL
         ),
         'type' =>  array(
            'type' => 'varchar(255) COLLATE utf8_unicode_ci NOT NULL',
            'value' => NULL
         ),
      );

      $a_table['oldfields'] = array(
      );

      $a_table['renamefields'] = array(
      );

      $a_table['keys'] = array(
      );

      $a_table['oldkeys'] = array(
      );

      migrateTablesFusionInventory($migration, $a_table);

      /*
       * glpi_plugin_fusioninventory_deploygroups_staticdatas
       */

      $a_table = array();

      //table name
      $a_table['name'] = 'glpi_plugin_fusioninventory_deploygroups_staticdatas';
      $a_table['oldname'] = array(
         'glpi_plugin_fusinvdeploy_groups_staticdatas'
      );

      $a_table['fields'] = array(
         'id' =>  array(
            'type' => 'autoincrement',
            'value' => NULL
         ),
         'groups_id' =>  array(
            'type' => 'int(11) NOT NULL',
            'value' => NULL
         ),
         'itemtype' =>  array(
            'type' => 'varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL',
            'value' => NULL
         ),
         'items_id' =>  array(
            'type' => 'int(11) NOT NULL',
            'value' => NULL
         ),
      );

      $a_table['oldfields'] = array(
      );

      $a_table['renamefields'] = array(
      );

      $a_table['keys'] = array(
         array(
            'field' => 'groups_id',
            'name' => '',
            'type' => 'KEY'
         ),
         array(
            'field' => 'items_id',
            'name' => '',
            'type' => 'KEY'
         ),
      );

      $a_table['oldkeys'] = array(
      );

      migrateTablesFusionInventory($migration, $a_table);

      /*
       * glpi_plugin_fusioninventory_deploygroups_dynamicdatas
       */

      $a_table = array();

      //table name
      $a_table['name'] = 'glpi_plugin_fusioninventory_deploygroups_dynamicdatas';
      $a_table['oldname'] = array(
         'glpi_plugin_fusinvdeploy_groups_dynamicdatas'
      );

      $a_table['fields'] = array(
         'id' =>  array(
            'type' => 'autoincrement',
            'value' => NULL
         ),
         'groups_id' =>  array(
            'type' => 'int(11) NOT NULL',
            'value' => NULL
         ),
         'fields_array' =>  array(
            'type' => 'text NOT NULL',
            'value' => NULL
         ),
      );

      $a_table['oldfields'] = array(
      );

      $a_table['renamefields'] = array(
      );

      $a_table['keys'] = array(
         array(
            'field' => 'groups_id',
            'name' => '',
            'type' => 'KEY'
         ),
      );

      $a_table['oldkeys'] = array(
      );

      migrateTablesFusionInventory($migration, $a_table);

      /*
      * import old datas as json in order table before migrate this table
      */

      migrateTablesFromFusinvDeploy($migration);


  /*
    * Deploy Update End
    */


   /*
    * Table glpi_plugin_fusioninventory_collects
    */
      $a_table = array();
      $a_table['name'] = 'glpi_plugin_fusioninventory_collects';
      $a_table['oldname'] = array();

      $a_table['fields']  = array();
      $a_table['fields']['id']         = array('type'    => "autoincrement",
                                               'value'   => '');
      $a_table['fields']['name']       = array('type'    => 'string',
                                               'value'   => NULL);
      $a_table['fields']['entities_id']   = array('type'    => 'integer',
                                                  'value'   => NULL);
      $a_table['fields']['is_recursive']  = array('type'    => 'bool',
                                                  'value'   => NULL);
      $a_table['fields']['type']       = array('type'    => 'string',
                                               'value'   => NULL);
      $a_table['fields']['is_active']  = array('type'    => 'bool',
                                               'value'   => NULL);
      $a_table['fields']['comment']    = array('type'    => 'text',
                                               'value'   => NULL);

      $a_table['oldfields']  = array();

      $a_table['renamefields'] = array();

      $a_table['keys']   = array();

      $a_table['oldkeys'] = array();

      migrateTablesFusionInventory($migration, $a_table);



   /*
    * Table glpi_plugin_fusioninventory_collects_registries
    */
      $a_table = array();
      $a_table['name'] = 'glpi_plugin_fusioninventory_collects_registries';
      $a_table['oldname'] = array();

      $a_table['fields']  = array();
      $a_table['fields']['id']         = array('type'    => "autoincrement",
                                               'value'   => '');
      $a_table['fields']['name']       = array('type'    => 'string',
                                               'value'   => NULL);
      $a_table['fields']['plugin_fusioninventory_collects_id']   = array('type'    => 'integer',
                                                  'value'   => NULL);
      $a_table['fields']['hive']       = array('type'    => 'string',
                                               'value'   => NULL);
      $a_table['fields']['path']       = array('type'    => 'text',
                                               'value'   => NULL);
      $a_table['fields']['key']        = array('type'    => 'string',
                                               'value'   => NULL);

      $a_table['oldfields']  = array();

      $a_table['renamefields'] = array();

      $a_table['keys']   = array();

      $a_table['oldkeys'] = array();

      migrateTablesFusionInventory($migration, $a_table);



   /*
    * Table glpi_plugin_fusioninventory_collects_registries_contents
    */
      $a_table = array();
      $a_table['name'] = 'glpi_plugin_fusioninventory_collects_registries_contents';
      $a_table['oldname'] = array();

      $a_table['fields']  = array();
      $a_table['fields']['id']         = array('type'    => "autoincrement",
                                               'value'   => '');
      $a_table['fields']['computers_id'] = array('type'    => 'integer',
                                               'value'   => NULL);
      $a_table['fields']['plugin_fusioninventory_collects_registries_id']   = array('type'    => 'integer',
                                                  'value'   => NULL);
      $a_table['fields']['key']       = array('type'    => 'string',
                                               'value'   => NULL);
      $a_table['fields']['value']     = array('type'    => 'string',
                                               'value'   => NULL);

      $a_table['oldfields']  = array();

      $a_table['renamefields'] = array();

      $a_table['keys']   = array();
      $a_table['keys'][] = array('field' => 'computers_id', 'name' => '', 'type' => 'INDEX');

      $a_table['oldkeys'] = array();

      migrateTablesFusionInventory($migration, $a_table);



   /*
    * Table glpi_plugin_fusioninventory_collects_wmis
    */
      $a_table = array();
      $a_table['name'] = 'glpi_plugin_fusioninventory_collects_wmis';
      $a_table['oldname'] = array();

      $a_table['fields']  = array();
      $a_table['fields']['id']         = array('type'    => "autoincrement",
                                               'value'   => '');
      $a_table['fields']['name']       = array('type'    => 'string',
                                               'value'   => NULL);
      $a_table['fields']['plugin_fusioninventory_collects_id']   = array('type'    => 'integer',
                                                  'value'   => NULL);
      $a_table['fields']['moniker']    = array('type'    => 'string',
                                               'value'   => NULL);
      $a_table['fields']['class']      = array('type'    => 'string',
                                               'value'   => NULL);
      $a_table['fields']['properties'] = array('type'    => 'string',
                                               'value'   => NULL);

      $a_table['oldfields']  = array();

      $a_table['renamefields'] = array();

      $a_table['keys']   = array();

      $a_table['oldkeys'] = array();

      migrateTablesFusionInventory($migration, $a_table);



   /*
    * Table glpi_plugin_fusioninventory_collects_wmis_contents
    */
      $a_table = array();
      $a_table['name'] = 'glpi_plugin_fusioninventory_collects_wmis_contents';
      $a_table['oldname'] = array();

      $a_table['fields']  = array();
      $a_table['fields']['id']         = array('type'    => "autoincrement",
                                               'value'   => '');
      $a_table['fields']['computers_id'] = array('type'    => 'integer',
                                               'value'   => NULL);
      $a_table['fields']['plugin_fusioninventory_collects_wmis_id']   = array('type'    => 'integer',
                                                  'value'   => NULL);
      $a_table['fields']['property']   = array('type'    => 'string',
                                               'value'   => NULL);
      $a_table['fields']['value']      = array('type'    => 'string',
                                               'value'   => NULL);

      $a_table['oldfields']  = array();

      $a_table['renamefields'] = array();

      $a_table['keys']   = array();

      $a_table['oldkeys'] = array();

      migrateTablesFusionInventory($migration, $a_table);



   /*
    * Table glpi_plugin_fusioninventory_collects_files
    */
      $a_table = array();
      $a_table['name'] = 'glpi_plugin_fusioninventory_collects_files';
      $a_table['oldname'] = array();

      $a_table['fields']  = array();
      $a_table['fields']['id']         = array('type'    => "autoincrement",
                                               'value'   => '');
      $a_table['fields']['name']       = array('type'    => 'string',
                                               'value'   => NULL);
      $a_table['fields']['plugin_fusioninventory_collects_id']   = array('type'    => 'integer',
                                                  'value'   => NULL);
      $a_table['fields']['dir']        = array('type'    => 'string',
                                               'value'   => NULL);
      $a_table['fields']['limit']      = array('type'    => "int(4) NOT NULL DEFAULT '50'",
                                               'value'   => NULL);
      $a_table['fields']['is_recursive'] = array('type'    => 'bool',
                                               'value'   => NULL);
      $a_table['fields']['filter_regex'] = array('type'    => 'string',
                                               'value'   => NULL);
      $a_table['fields']['filter_sizeequals'] = array('type'    => 'integer',
                                               'value'   => NULL);
      $a_table['fields']['filter_sizegreater'] = array('type'    => 'integer',
                                               'value'   => NULL);
      $a_table['fields']['filter_sizelower'] = array('type'    => 'integer',
                                               'value'   => NULL);
      $a_table['fields']['filter_checksumsha512'] = array('type'    => 'string',
                                               'value'   => NULL);
      $a_table['fields']['filter_checksumsha2'] = array('type'    => 'string',
                                               'value'   => NULL);
      $a_table['fields']['filter_name'] = array('type'    => 'string',
                                               'value'   => NULL);
      $a_table['fields']['filter_iname'] = array('type'    => 'string',
                                               'value'   => NULL);
      $a_table['fields']['filter_is_file'] = array('type'    => 'bool',
                                               'value'   => '1');
      $a_table['fields']['filter_is_dir'] = array('type'    => 'bool',
                                               'value'   => '0');

      $a_table['oldfields']  = array();

      $a_table['renamefields'] = array();

      $a_table['keys']   = array();

      $a_table['oldkeys'] = array();

      migrateTablesFusionInventory($migration, $a_table);



   /*
    * Table glpi_plugin_fusioninventory_collects_files_contents
    */
      $a_table = array();
      $a_table['name'] = 'glpi_plugin_fusioninventory_collects_files_contents';
      $a_table['oldname'] = array();

      $a_table['fields']  = array();
      $a_table['fields']['id']         = array('type'    => "autoincrement",
                                               'value'   => '');
      $a_table['fields']['computers_id'] = array('type'    => 'integer',
                                               'value'   => NULL);
      $a_table['fields']['plugin_fusioninventory_collects_files_id']   = array('type'    => 'integer',
                                                  'value'   => NULL);
      $a_table['fields']['pathfile']   = array('type'    => 'text',
                                               'value'   => NULL);
      $a_table['fields']['size']       = array('type'    => 'integer',
                                               'value'   => NULL);

      $a_table['oldfields']  = array();

      $a_table['renamefields'] = array();

      $a_table['keys']   = array();

      $a_table['oldkeys'] = array();

      migrateTablesFusionInventory($migration, $a_table);


   /*
    * Table glpi_plugin_fusioninventory_dblockinventorynames
    */
      $a_table = array();
      $a_table['name'] = 'glpi_plugin_fusioninventory_dblockinventorynames';
      $a_table['oldname'] = array();

      $a_table['fields']  = array();
      $a_table['fields']['value']      = array('type'    => "varchar(100) NOT NULL DEFAULT ''",
                                               'value'   => NULL);
      $a_table['fields']['date']       = array('type'    => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
                                               'value'   => NULL);

      $a_table['oldfields']  = array();

      $a_table['renamefields'] = array();

      $a_table['keys']   = array();
      $a_table['keys'][] = array('field' => 'value', 'name' => '', 'type' => 'UNIQUE');

      $a_table['oldkeys'] = array();

      migrateTablesFusionInventory($migration, $a_table);



   /*
    * Table glpi_plugin_fusioninventory_dblockinventories
    */
      $a_table = array();
      $a_table['name'] = 'glpi_plugin_fusioninventory_dblockinventories';
      $a_table['oldname'] = array();

      $a_table['fields']  = array();
      $a_table['fields']['value']      = array('type'    => 'integer',
                                               'value'   => NULL);
      $a_table['fields']['date']       = array('type'    => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
                                               'value'   => NULL);

      $a_table['oldfields']  = array();

      $a_table['renamefields'] = array();

      $a_table['keys']   = array();
      $a_table['keys'][] = array('field' => 'value', 'name' => '', 'type' => 'UNIQUE');

      $a_table['oldkeys'] = array();

      migrateTablesFusionInventory($migration, $a_table);



   /*
    * Table glpi_plugin_fusioninventory_dblocksoftwares
    */
      $a_table = array();
      $a_table['name'] = 'glpi_plugin_fusioninventory_dblocksoftwares';
      $a_table['oldname'] = array();

      $a_table['fields']  = array();
      $a_table['fields']['value']      = array('type'    => 'bool',
                                               'value'   => NULL);
      $a_table['fields']['date']       = array('type'    => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
                                               'value'   => NULL);

      $a_table['oldfields']  = array();

      $a_table['renamefields'] = array();

      $a_table['keys']   = array();
      $a_table['keys'][] = array('field' => 'value', 'name' => '', 'type' => 'UNIQUE');

      $a_table['oldkeys'] = array();

      migrateTablesFusionInventory($migration, $a_table);



   /*
    * Table glpi_plugin_fusioninventory_dblocksoftwareversions
    */
      $a_table = array();
      $a_table['name'] = 'glpi_plugin_fusioninventory_dblocksoftwareversions';
      $a_table['oldname'] = array();

      $a_table['fields']  = array();
      $a_table['fields']['value']      = array('type'    => 'bool',
                                               'value'   => NULL);
      $a_table['fields']['date']       = array('type'    => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
                                               'value'   => NULL);

      $a_table['oldfields']  = array();

      $a_table['renamefields'] = array();

      $a_table['keys']   = array();
      $a_table['keys'][] = array('field' => 'value', 'name' => '', 'type' => 'UNIQUE');

      $a_table['oldkeys'] = array();

      migrateTablesFusionInventory($migration, $a_table);





   /*
    * Add ESX module appear in version 2.4.0(0.80+1.0)
    */

      $DB->query("UPDATE `glpi_plugin_fusioninventory_agentmodules`
         SET `modulename`='InventoryComputerESX'
         WHERE `modulename`='ESX'");

      $agentmodule = new PluginFusioninventoryAgentmodule();
      $query = "SELECT `id` FROM `glpi_plugin_fusioninventory_agentmodules`
         WHERE `modulename`='InventoryComputerESX'
         LIMIT 1";
      $result = $DB->query($query);
      if ($DB->numrows($result) == '0') {
         $input = array();
         $input['modulename'] = "InventoryComputerESX";
         $input['is_active']  = 0;
         $input['exceptions'] = exportArrayToDB(array());
         $url= '';
         if (isset($_SERVER['HTTP_REFERER'])) {
            $url = $_SERVER['HTTP_REFERER'];
         }
         $agentmodule->add($input);
      }


   /*
    * Add Collect module appear in version 0.84+2.0
    */

      $agentmodule = new PluginFusioninventoryAgentmodule();
      $query = "SELECT `id` FROM `glpi_plugin_fusioninventory_agentmodules`
         WHERE `modulename`='Collect'
         LIMIT 1";
      $result = $DB->query($query);
      if ($DB->numrows($result) == '0') {
         $input = array();
         $input['modulename'] = "Collect";
         $input['is_active']  = 1;
         $input['exceptions'] = exportArrayToDB(array());
         $agentmodule->add($input);
      }


      /*
       * Update pci and usb ids and oui
       */
      foreach (array('usbid.sql', 'pciid.sql', 'oui.sql') as $sql) {
         $DB_file = GLPI_ROOT ."/plugins/fusioninventory/install/mysql/$sql";
         $DBf_handle = fopen($DB_file, "rt");
         $sql_query = fread($DBf_handle, filesize($DB_file));
         fclose($DBf_handle);
         foreach ( explode(";\n", "$sql_query") as $sql_line) {
            if (Toolbox::get_magic_quotes_runtime()) {
               $sql_line=Toolbox::stripslashes_deep($sql_line);
            }
            if (!empty($sql_line)) {
               $DB->query($sql_line)/* or die($DB->error())*/;
            }
         }
      }



   /*
    * Migrate data of table glpi_plugin_fusinvsnmp_agentconfigs into
    * glpi_plugin_fusioninventory_agents
    */
   if (TableExists("glpi_plugin_fusinvsnmp_agentconfigs")) {

      $query = "SELECT * FROM `glpi_plugin_fusinvsnmp_agentconfigs`";
      $result=$DB->query($query);
      while ($data=$DB->fetch_array($result)) {
         $queryu = "UPDATE `glpi_plugin_fusioninventory_agents`
            SET `threads_networkdiscovery`='".$data['threads_netdiscovery']."',
                `threads_networkinventory`='".$data['threads_snmpquery']."',
                `senddico`='".$data['senddico']."'
            WHERE `id`='".$data['plugin_fusioninventory_agents_id']."'";
         $DB->query($queryu);
      }
   }



   // Update profiles
   if (TableExists("glpi_plugin_tracker_profiles")) {
      $profile = new Profile();
      $pfProfile = new PluginFusioninventoryProfile();
      $query = "SELECT * FROM `glpi_plugin_tracker_profiles`";
      $result=$DB->query($query);
      while ($data=$DB->fetch_array($result)) {
         $profiledata = current($profile->find("`name`='".$data['name']."'", "", 1));
         if (!empty($profiledata)) {
            $newprofile = array();
            $newprofile['snmp_networking'] = "networkequipment";
            $newprofile['snmp_printers'] = "printer";
            $newprofile['snmp_models'] = "model";
            $newprofile['snmp_authentification'] = "configsecurity";
            $newprofile['general_config'] = "configuration";
            $newprofile['snmp_report'] = "reportprinter";

            foreach ($newprofile as $old=>$new) {
               if (isset($profiledata[$old])) {
                  $pfProfile->addProfile($new,
                                                       $profiledata[$old],
                                                       $profiledata['id']);
               }
            }
            if (isset($profiledata["snmp_report"])) {
               $pfProfile->addProfile("reportnetworkequipment",
                                                    $profiledata["snmp_report"],
                                                    $profiledata['id']);
            }
         }
      }
      $DB->query("DROP TABLE `glpi_plugin_tracker_profiles`");
   }

   update213to220_ConvertField($migration);


   /*
    * Move networkequipment IPs to net system
    */
   if (TableExists("glpi_plugin_fusioninventory_networkequipmentips")) {
      $networkPort = new NetworkPort();
      $networkName = new NetworkName();
      $ipAddress = new IPAddress();
      $networkEquipment = new NetworkEquipment();

      $query = "SELECT * FROM `glpi_plugin_fusioninventory_networkequipments`";
      $result=$DB->query($query);
      while ($data=$DB->fetch_array($result)) {
         if ($networkEquipment->getFromDB($data['networkequipments_id'])) {
            $oldtableip = array();
            $queryIP = "SELECT * FROM `glpi_plugin_fusioninventory_networkequipmentips`
               WHERE `networkequipments_id`='".$data['networkequipments_id']."'";
            $resultIP = $DB->query($queryIP);
            while ($dataIP = $DB->fetch_array($resultIP)) {
               $oldtableip[$dataIP['ip']] = $dataIP['ip'];
            }

            // Get actual IP defined
            $networknames_id = 0;
            $a_ports = $networkPort->find("`itemtype`='NetworkEquipment'
                  AND `items_id`='".$data['networkequipments_id']."'
                  AND `instantiation_type`='NetworkPortAggregate'
                  AND `name`='management'", "", 1);

            foreach ($a_ports as $a_port) {
               $a_networknames = $networkName->find("`itemtype`='NetworkPort'
                  AND `items_id`='".$a_port['id']."'");
               foreach ($a_networknames as $a_networkname) {
                  $networknames_id = $a_networkname['id'];
                  $a_ipaddresses = $ipAddress->find("`itemtype`='NetworkName'
                     AND `items_id`='".$a_networkname['id']."'");
                  foreach ($a_ipaddresses as $a_ipaddress) {
                     if (isset($oldtableip[$a_ipaddress['name']])) {
                        unset($oldtableip[$a_ipaddress['name']]);
                     } else {
                        $ipAddress->delete($a_ipaddress, 1);
                     }
                  }
               }
            }

            // Update
            foreach ($oldtableip as $ip) {
               $input = array();
               $input['itemtype']   = "NetworkName";
               $input['items_id']   = $networknames_id;
               $input['name']       = $ip;
               $input['is_dynamic'] = 1;
               $ipAddress->add($input);
            }
         }
      }
   }


   /*
    * Table Delete old table not used
    */
   $a_drop = array();
   $a_drop[] = 'glpi_plugin_tracker_computers';
   $a_drop[] = 'glpi_plugin_tracker_connection_history';
   $a_drop[] = 'glpi_plugin_tracker_agents_processes';
   $a_drop[] = 'glpi_plugin_tracker_config_snmp_history';
   $a_drop[] = 'glpi_plugin_tracker_config_snmp_networking';
   $a_drop[] = 'glpi_plugin_tracker_config_snmp_printer';
   $a_drop[] = 'glpi_plugin_tracker_config_snmp_script';
   $a_drop[] = 'glpi_plugin_tracker_connection_stats';
   $a_drop[] = 'glpi_plugin_tracker_discovery';
   $a_drop[] = 'glpi_plugin_tracker_errors';
   $a_drop[] = 'glpi_plugin_tracker_model_infos';
   $a_drop[] = 'glpi_plugin_tracker_processes';
   $a_drop[] = 'glpi_plugin_tracker_processes_values';
   $a_drop[] = 'glpi_plugin_fusioninventory_agents_errors';
   $a_drop[] = 'glpi_plugin_fusioninventory_agents_processes';
   $a_drop[] = 'glpi_plugin_fusioninventory_computers';
   $a_drop[] = 'glpi_dropdown_plugin_tracker_snmp_auth_auth_protocol';
   $a_drop[] = 'glpi_dropdown_plugin_tracker_snmp_auth_priv_protocol';
   $a_drop[] = 'glpi_dropdown_plugin_tracker_snmp_auth_sec_level';
   $a_drop[] = 'glpi_dropdown_plugin_tracker_snmp_version';
   $a_drop[] = 'glpi_plugin_fusioninventory_config_snmp_networking';
   $a_drop[] = 'glpi_plugin_fusioninventory_config_snmp_history';
   $a_drop[] = 'glpi_plugin_fusinvsnmp_agentconfigs';
   $a_drop[] = 'glpi_plugin_tracker_computers';
   $a_drop[] = 'glpi_plugin_tracker_config';
   $a_drop[] = 'glpi_plugin_tracker_config_discovery';
   $a_drop[] = 'glpi_dropdown_plugin_fusioninventory_mib_label';
   $a_drop[] = 'glpi_dropdown_plugin_fusioninventory_mib_object';
   $a_drop[] = 'glpi_dropdown_plugin_fusioninventory_mib_oid';
   $a_drop[] = 'glpi_dropdown_plugin_fusioninventory_snmp_auth_auth_protocol';
   $a_drop[] = 'glpi_dropdown_plugin_fusioninventory_snmp_auth_priv_protocol';
   $a_drop[] = 'glpi_dropdown_plugin_fusioninventory_snmp_version';
   $a_drop[] = 'glpi_plugin_fusinvsnmp_temp_profiles';
   $a_drop[] = 'glpi_plugin_fusinvsnmp_tmp_agents';
   $a_drop[] = 'glpi_plugin_fusinvsnmp_tmp_configs';
   $a_drop[] = 'glpi_plugin_fusinvsnmp_tmp_tasks';
   $a_drop[] = 'glpi_plugin_tracker_tmp_connections';
   $a_drop[] = 'glpi_plugin_tracker_tmp_netports';
   $a_drop[] = 'glpi_plugin_tracker_walks';
   $a_drop[] = 'glpi_plugin_fusioninventory_networkequipmentips';

   foreach ($a_drop as $droptable) {
      if (TableExists($droptable)) {
         $DB->query("DROP TABLE `".$droptable."`");
      }
   }

   $migration->executeMigration();



   /*
    * Add WakeOnLan module appear in version 2.3.0
    */
   $query = "SELECT `id` FROM `glpi_plugin_fusioninventory_agentmodules`
      WHERE `modulename`='WAKEONLAN'";
   $result = $DB->query($query);
   if (!$DB->numrows($result)) {
      $agentmodule = new PluginFusioninventoryAgentmodule;
      $input = array();
      $input['plugins_id'] = $plugins_id;
      $input['modulename'] = "WAKEONLAN";
      $input['is_active']  = 0;
      $input['exceptions'] = exportArrayToDB(array());
      $agentmodule->add($input);
   }



   /*
    * Add storage type if not present
    */
   $a_storage = array();
   $a_storage['partition']          = 5;
   $a_storage['volume groups']      = 10;
   $a_storage['logical volumes']    = 20;
   $a_storage['hard disk']          = 1;
   $a_storage['mount']              = 25;

   foreach ($a_storage as $name => $level) {
      $query = "SELECT `id` FROM `glpi_plugin_fusioninventory_inventorycomputerstoragetypes`
         WHERE `name`='".$name."'";
      $result = $DB->query($query);
      if (!$DB->numrows($result)) {
         $DB->query("INSERT INTO `glpi_plugin_fusioninventory_inventorycomputerstoragetypes`
            (`name`, `level`) VALUES
            ('".$name."', '".$level."')");
      }
   }




   /*
    * Clean for port orphelin
    */
   //networkports with item_type = 0
   $NetworkPort = new NetworkPort();
   $NetworkPort_Vlan = new NetworkPort_Vlan();
   $NetworkPort_NetworkPort = new NetworkPort_NetworkPort();
   $a_networkports = $NetworkPort->find("`itemtype`=''");
   foreach ($a_networkports as $data) {
      if ($NetworkPort_NetworkPort->getFromDBForNetworkPort($data['id'])) {
         $NetworkPort_NetworkPort->delete($NetworkPort_NetworkPort->fields);
      }
      $a_vlans = $NetworkPort_Vlan->find("`networkports_id`='".$data['id']."'");
      foreach ($a_vlans as $a_vlan) {
         $NetworkPort_Vlan->delete($a_vlan);
      }
      $NetworkPort->delete($data, 1);
   }


   /*
    *  Clean old ports deleted but have some informations in SNMP tables
    */
   echo "Clean ports purged\n";
   $query_select = "SELECT `glpi_plugin_fusioninventory_networkports`.`id`
                    FROM `glpi_plugin_fusioninventory_networkports`
                          LEFT JOIN `glpi_networkports`
                                    ON `glpi_networkports`.`id` = `networkports_id`
                          LEFT JOIN `glpi_networkequipments`
                              ON `glpi_networkequipments`.`id` = `glpi_networkports`.`items_id`
                    WHERE `glpi_networkequipments`.`id` IS NULL";
   $result=$DB->query($query_select);
   while ($data=$DB->fetch_array($result)) {
      $query_del = "DELETE FROM `glpi_plugin_fusioninventory_networkports`
         WHERE `id`='".$data["id"]."'";
      $DB->query($query_del);
   }



   /*
    * Clean for switch more informations again in DB when switch is purged
    */
   echo "Clean for switch more informations again in DB when switch is purged\n";
   $query_select = "SELECT `glpi_plugin_fusioninventory_networkequipments`.`id`
                    FROM `glpi_plugin_fusioninventory_networkequipments`
                    LEFT JOIN `glpi_networkequipments`
                        ON `glpi_networkequipments`.`id` = `networkequipments_id`
                    WHERE `glpi_networkequipments`.`id` IS NULL";
   $result=$DB->query($query_select);
   while ($data=$DB->fetch_array($result)) {
       $query_del = "DELETE FROM `glpi_plugin_fusioninventory_networkequipments`
         WHERE `id`='".$data["id"]."'";
      $DB->query($query_del);
   }



   /*
    * Clean for printer more informations again in DB when printer is purged
    */
   "Clean for printer more informations again in DB when printer is purged\n";
   $query_select = "SELECT `glpi_plugin_fusioninventory_printers`.`id`
                    FROM `glpi_plugin_fusioninventory_printers`
                          LEFT JOIN `glpi_printers` ON `glpi_printers`.`id` = `printers_id`
                    WHERE `glpi_printers`.`id` IS NULL";
   $result=$DB->query($query_select);
   while ($data=$DB->fetch_array($result)) {
      $query_del = "DELETE FROM `glpi_plugin_fusioninventory_printers`
         WHERE `id`='".$data["id"]."'";
      $DB->query($query_del);
   }



   /*
    *  Clean printer cartridge not deleted with the printer associated
    */
   echo "Clean printer cartridge not deleted with the printer associated\n";
   $query_select = "SELECT `glpi_plugin_fusioninventory_printercartridges`.`id`
                    FROM `glpi_plugin_fusioninventory_printercartridges`
                          LEFT JOIN `glpi_printers` ON `glpi_printers`.`id` = `printers_id`
                    WHERE `glpi_printers`.`id` IS NULL";
   $result=$DB->query($query_select);
   while ($data=$DB->fetch_array($result)) {
      $query_del = "DELETE FROM `glpi_plugin_fusioninventory_printercartridges`
         WHERE `id`='".$data["id"]."'";
      $DB->query($query_del);
   }



   /*
    *  Clean printer history not deleted with printer associated
    */
   echo "Clean printer history not deleted with printer associated\n";
   $query_select = "SELECT `glpi_plugin_fusioninventory_printerlogs`.`id`
                    FROM `glpi_plugin_fusioninventory_printerlogs`
                          LEFT JOIN `glpi_printers` ON `glpi_printers`.`id` = `printers_id`
                    WHERE `glpi_printers`.`id` IS NULL";
   $result=$DB->query($query_select);
   while ($data=$DB->fetch_array($result)) {
      $query_del = "DELETE FROM `glpi_plugin_fusioninventory_printerlogs`
         WHERE `id`='".$data["id"]."'";
      $DB->query($query_del);
   }



   /*
    * Fix problem with mapping with many entries with same mapping
    */
   $a_mapping = array();
   $a_mappingdouble = array();
   $query = "SELECT * FROM `glpi_plugin_fusioninventory_mappings`
      ORDER BY `id`";
   $result=$DB->query($query);
   while ($data=$DB->fetch_array($result)) {
      if (!isset($a_mapping[$data['itemtype'].".".$data['name']])) {
         $a_mapping[$data['itemtype'].".".$data['name']] = $data['id'];
      } else {
         $a_mappingdouble[$data['id']] = $data['itemtype'].".".$data['name'];
      }
   }
   foreach($a_mappingdouble as $mapping_id=>$mappingkey) {
      $query = "UPDATE `glpi_plugin_fusioninventory_snmpmodelmibs`
         SET plugin_fusioninventory_mappings_id='".$a_mapping[$mappingkey]."'
         WHERE plugin_fusioninventory_mappings_id='".$mapping_id."'";
      $DB->query($query);
      $query = "UPDATE `glpi_plugin_fusioninventory_printercartridges`
         SET plugin_fusioninventory_mappings_id='".$a_mapping[$mappingkey]."'
         WHERE plugin_fusioninventory_mappings_id='".$mapping_id."'";
      $DB->query($query);
      $query = "UPDATE `glpi_plugin_fusioninventory_networkportlogs`
         SET plugin_fusioninventory_mappings_id='".$a_mapping[$mappingkey]."'
         WHERE plugin_fusioninventory_mappings_id='".$mapping_id."'";
      $DB->query($query);
      $query = "UPDATE `glpi_plugin_fusioninventory_configlogfields`
         SET plugin_fusioninventory_mappings_id='".$a_mapping[$mappingkey]."'
         WHERE plugin_fusioninventory_mappings_id='".$mapping_id."'";
      $DB->query($query);
      $query = "DELETE FROM `glpi_plugin_fusioninventory_mappings`
         WHERE `id` = '".$mapping_id."'";
      $DB->query($query);
   }



   /*
    * Update networports to convert itemtype 5153 to PluginFusioninventoryUnknownDevice
    */
   $sql = "UPDATE `glpi_networkports`
      SET `itemtype`='PluginFusioninventoryUnknownDevice'
      WHERE `itemtype`='5153'";
   $DB->query($sql);
   $sql = "UPDATE `glpi_networkports`
      SET `itemtype`='PluginFusioninventoryTask'
      WHERE `itemtype`='5166'";
   $DB->query($sql);

   /*
    * Clean display preferences not used
    */
   $sql = "DELETE FROM `glpi_displaypreferences`
      WHERE `itemtype`='5150' ";
   $DB->query($sql);
   $sql = "DELETE FROM `glpi_displaypreferences`
      WHERE `itemtype`='5160' ";
   $DB->query($sql);
   $sql = "DELETE FROM `glpi_displaypreferences`
      WHERE `itemtype`='5161' ";
   $DB->query($sql);
   $sql = "DELETE FROM `glpi_displaypreferences`
      WHERE `itemtype`='5163' ";
   $DB->query($sql);
   $sql = "DELETE FROM `glpi_displaypreferences`
      WHERE `itemtype`='5165' ";
   $DB->query($sql);



   /*
    * Update display preferences
    */
   changeDisplayPreference("5153", "PluginFusioninventoryUnknownDevice");
   changeDisplayPreference("5158", "PluginFusioninventoryAgent");
   changeDisplayPreference("PluginFusinvinventoryBlacklist",
                           "PluginFusioninventoryInventoryComputerBlacklist");
   changeDisplayPreference("5151", "PluginFusinvsnmpModel");
   changeDisplayPreference("PluginFusinvsnmpModel", "PluginFusioninventorySnmpmodel");
   changeDisplayPreference("5152", "PluginFusinvsnmpConfigSecurity");
   changeDisplayPreference("5156", "PluginFusinvsnmpPrinterCartridge");
   changeDisplayPreference("5157", "PluginFusinvsnmpNetworkEquipment");
   changeDisplayPreference("PluginFusinvsnmpNetworkEquipment",
                           "PluginFusioninventoryNetworkEquipment");
   changeDisplayPreference("5159", "PluginFusinvsnmpIPRange");
   changeDisplayPreference("5162", "PluginFusinvsnmpNetworkPortLog");
   changeDisplayPreference("5167", "PluginFusioninventorySnmpmodelConstructDevice");
   changeDisplayPreference("PluginFusinvsnmpConstructDevice",
                           "PluginFusioninventorySnmpmodelConstructDevice");
   changeDisplayPreference("5168", "PluginFusinvsnmpPrinterLog");
   changeDisplayPreference("PluginFusinvsnmpPrinterLogReport",
                           "PluginFusioninventoryPrinterLogReport");

   /*
    * Delete IP and MAC of PluginFusioninventoryUnknownDevice in displaypreference
    */
      $queryd = "DELETE FROM `glpi_displaypreferences`
         WHERE `itemtype`='PluginFusioninventoryUnknownDevice'
            AND (`num`='11' OR `num`='12')";
      $DB->query($queryd);


   /*
    * Modify displaypreference for PluginFusioninventoryPrinterLog
    */
      $pfPrinterLogReport = new PluginFusioninventoryPrinterLog();
      $a_searchoptions = $pfPrinterLogReport->getSearchOptions();
      $query = "SELECT * FROM `glpi_displaypreferences`
      WHERE `itemtype` = 'PluginFusioninventoryPrinterLogReport'
         AND `users_id`='0'";
      $result=$DB->query($query);
      if ($DB->numrows($result) == '0') {
         $query = "INSERT INTO `glpi_displaypreferences` (`id`, `itemtype`, `num`, `rank`,
                        `users_id`)
                     VALUES (NULL, 'PluginFusioninventoryPrinterLogReport', '2', '1', '0'),
             (NULL, 'PluginFusioninventoryPrinterLogReport', '18', '2', '0'),
             (NULL, 'PluginFusioninventoryPrinterLogReport', '20', '3', '0'),
             (NULL, 'PluginFusioninventoryPrinterLogReport', '5', '4', '0'),
             (NULL, 'PluginFusioninventoryPrinterLogReport', '6', '5', '0')";
         $DB->query($query);
      } else {
         while ($data=$DB->fetch_array($result)) {
            if (!isset($a_searchoptions[$data['num']])) {
               $queryd = "DELETE FROM `glpi_displaypreferences`
                  WHERE `id`='".$data['id']."'";
               $DB->query($queryd);
            }
         }
      }



   /*
    * Modify displaypreference for PluginFusinvsnmpNetworkEquipment
    */
      $a_check = array();
      $a_check["2"] = 1;
      $a_check["3"] = 2;
      $a_check["4"] = 3;
      $a_check["5"] = 4;
      $a_check["6"] = 5;
      $a_check["7"] = 6;
      $a_check["8"] = 7;
      $a_check["9"] = 8;
      $a_check["10"] = 9;
      $a_check["11"] = 10;
      $a_check["14"] = 11;
      $a_check["12"] = 12;
      $a_check["13"] = 13;

      foreach ($a_check as $num=>$rank) {
         $query = "SELECT * FROM `glpi_displaypreferences`
         WHERE `itemtype` = 'PluginFusioninventoryNetworkEquipment'
         AND `num`='".$num."'
            AND `users_id`='0'";
         $result=$DB->query($query);
         if ($DB->numrows($result) == '0') {
            $query = "INSERT INTO `glpi_displaypreferences` (`id`, `itemtype`, `num`, `rank`,
                           `users_id`)
                        VALUES (NULL, 'PluginFusioninventoryNetworkEquipment', '".$num."',
                           '".$rank."', '0')";
            $DB->query($query);
         }
      }
      $query = "SELECT * FROM `glpi_displaypreferences`
      WHERE `itemtype` = 'PluginFusioninventoryNetworkEquipment'
         AND `users_id`='0'";
      $result=$DB->query($query);
      while ($data=$DB->fetch_array($result)) {
         if (!isset($a_check[$data['num']])) {
            $queryd = "DELETE FROM `glpi_displaypreferences`
               WHERE `id`='".$data['id']."'";
            $DB->query($queryd);
         }
      }

      // If no PluginFusioninventoryTaskjoblog in preferences, add them
      $query = "SELECT * FROM `glpi_displaypreferences`
      WHERE `itemtype` = 'PluginFusioninventoryTaskjoblog'
         AND `users_id`='0'";
      $result=$DB->query($query);
      if ($DB->numrows($result) == 0) {
         $DB->query("INSERT INTO `glpi_displaypreferences`
            (`id`, `itemtype`, `num`, `rank`, `users_id`)
         VALUES (NULL,'PluginFusioninventoryTaskjoblog', '2', '1', '0'),
                (NULL,'PluginFusioninventoryTaskjoblog', '3', '2', '0'),
                (NULL,'PluginFusioninventoryTaskjoblog', '4', '3', '0'),
                (NULL,'PluginFusioninventoryTaskjoblog', '5', '4', '0'),
                (NULL,'PluginFusioninventoryTaskjoblog', '6', '5', '0'),
                (NULL,'PluginFusioninventoryTaskjoblog', '7', '6', '0'),
                (NULL,'PluginFusioninventoryTaskjoblog', '8', '7', '0')");
      }


      // If no PluginFusioninventoryNetworkPort in preferences, add them
      $query = "SELECT * FROM `glpi_displaypreferences`
      WHERE `itemtype` = 'PluginFusioninventoryNetworkPort'
         AND `users_id`='0'";
      $result=$DB->query($query);
      if ($DB->numrows($result) == 0) {
         $DB->query("INSERT INTO `glpi_displaypreferences`
            (`id`, `itemtype`, `num`, `rank`, `users_id`)
         VALUES (NULL,'PluginFusioninventoryNetworkPort', '3', '1', '0'),
                (NULL,'PluginFusioninventoryNetworkPort', '5', '2', '0'),
                (NULL,'PluginFusioninventoryNetworkPort', '6', '3', '0'),
                (NULL,'PluginFusioninventoryNetworkPort', '7', '4', '0'),
                (NULL,'PluginFusioninventoryNetworkPort', '8', '5', '0'),
                (NULL,'PluginFusioninventoryNetworkPort', '9', '6', '0'),
                (NULL,'PluginFusioninventoryNetworkPort', '10', '7', '0'),
                (NULL,'PluginFusioninventoryNetworkPort', '11', '8', '0'),
                (NULL,'PluginFusioninventoryNetworkPort', '12', '9', '0'),
                (NULL,'PluginFusioninventoryNetworkPort', '13', '10', '0'),
                (NULL,'PluginFusioninventoryNetworkPort', '14', '11', '0')");
      }



   /*
    * Convert taskjob definition from PluginFusinvsnmpIPRange to PluginFusioninventoryIPRange
    * onvert taskjob definition from PluginFusinvdeployPackage to PluginFusioninventoryDeployPackage
    */
   $query = "SELECT * FROM `glpi_plugin_fusioninventory_taskjobs`";
   $result = $DB->query($query);
   while ($data=$DB->fetch_array($result)) {
      $a_defs = importArrayFromDB($data['definition']);
      foreach ($a_defs as $num=>$a_def) {
         if (key($a_def) == 'PluginFusinvsnmpIPRange') {
            $a_defs[$num] = array('PluginFusioninventoryIPRange'=>current($a_def));
         } else if (key($a_def) == 'PluginFusinvdeployPackage') {
            $a_defs[$num] = array('PluginFusioninventoryDeployPackage'=>current($a_def));
         }
      }
      $queryu = "UPDATE `glpi_plugin_fusioninventory_taskjobs`
         SET `definition`='".exportArrayToDB($a_defs)."'
         WHERE `id`='".$data['id']."'";
      $DB->query($queryu);
   }

   /*
    * Convert taskjoblogs itemtype from PluginFusinvdeployPackage to
    * PluginFusioninventoryDeployPackage
    */

   $query = "UPDATE `glpi_plugin_fusioninventory_taskjoblogs` ".
            "SET `itemtype`='PluginFusioninventoryDeployPackage'".
            "WHERE `itemtype`='PluginFusinvdeployPackage'";
   $result = $DB->query($query);

   /*
    * Convert taskjobstates itemtype from PluginFusinvdeployPackage to
    * PluginFusioninventoryDeployPackage
    */

   $query = "UPDATE `glpi_plugin_fusioninventory_taskjobstates` ".
            "SET `itemtype`='PluginFusioninventoryDeployPackage'".
            "WHERE `itemtype` = 'PluginFusinvdeployPackage'";
   $result = $DB->query($query);

   /*
    * Convert taskjob action from PluginFusinvdeployGroup to PluginFusioninventoryDeployGroup
    */
   $query = "SELECT * FROM `glpi_plugin_fusioninventory_taskjobs`";
   $result = $DB->query($query);
   while ($data=$DB->fetch_array($result)) {
      $a_defs = importArrayFromDB($data['action']);
      foreach ($a_defs as $num=>$a_def) {
         if (key($a_def) == 'PluginFusinvdeployGroup') {
            $a_defs[$num] = array('PluginFusioninventoryDeployGroup'=>current($a_def));
         }
      }
      $queryu = "UPDATE `glpi_plugin_fusioninventory_taskjobs`
         SET `action`='".exportArrayToDB($a_defs)."'
         WHERE `id`='".$data['id']."'";
      $DB->query($queryu);
   }


   /*
    * Update rules
    */
   $query = "UPDATE glpi_rules SET `sub_type`='PluginFusioninventoryInventoryRuleImport'
      WHERE `sub_type`='PluginFusioninventoryRuleImportEquipment'";
   $DB->query($query);

   $query = "SELECT * FROM `glpi_rules`
               WHERE `sub_type`='PluginFusioninventoryInventoryRuleImport'";
   $result = $DB->query($query);
   while ($data=$DB->fetch_array($result)) {
      $querya = "UPDATE glpi_ruleactions SET `value`='1'
         WHERE `rules_id`='".$data['id']."'
            AND `value`='0'
            AND `field`='_fusion'";
      $DB->query($querya);
   }

   $query = "UPDATE glpi_rules SET `sub_type`='PluginFusioninventoryInventoryRuleEntity'
      WHERE `sub_type`='PluginFusinvinventoryRuleEntity'";
   $DB->query($query);

   /*
    *  Add default rules
    */
   if (TableExists("glpi_plugin_tracker_config_discovery")) {
      $migration->displayMessage("Create rules");
      $pfSetup = new PluginFusioninventorySetup();
      $pfSetup->initRules();
   }
   // If no rules, add them
   if (countElementsInTable('glpi_rules', "`sub_type`='PluginFusioninventoryInventoryRuleImport'") == 0) {
      $migration->displayMessage("Create rules");
      $pfSetup = new PluginFusioninventorySetup();
      $pfSetup->initRules();
   }


   PluginFusioninventoryProfile::changeProfile();

   /*
    *  Manage configuration of plugin
    */
      $config = new PluginFusioninventoryConfig();
      $pfSetup = new PluginFusioninventorySetup();
      $users_id = $pfSetup->createFusionInventoryUser();
      $a_input = array();
      $a_input['ssl_only'] = 0;
      $a_input['delete_task'] = 20;
      $a_input['inventory_frequence'] = 24;
      $a_input['agent_port'] = 62354;
      $a_input['extradebug'] = 0;
      $a_input['users_id'] = $users_id;
      $config->addValues($a_input, FALSE);
//      $DB->query("DELETE FROM `glpi_plugin_fusioninventory_configs`
//        WHERE `plugins_id`='0'");

//      $query = "SELECT * FROM `glpi_plugin_fusioninventory_configs`
//           WHERE `type`='version'
//           LIMIT 1, 10";
//      $result = $DB->query($query);
//      while ($data=$DB->fetch_array($result)) {
//         $config->delete($data);
//      }

      $a_input = array();
      $a_input['version'] = PLUGIN_FUSIONINVENTORY_VERSION;
      $config->addValues($a_input, TRUE);
      $a_input = array();
      $a_input['ssl_only'] = 0;
      if (isset($prepare_Config['ssl_only'])) {
         $a_input['ssl_only'] = $prepare_Config['ssl_only'];
      }
      $a_input['delete_task'] = 20;
      $a_input['inventory_frequence'] = 24;
      $a_input['agent_port'] = 62354;
      $a_input['extradebug'] = 0;
      $a_input['users_id'] = 0;

      //Deploy configuration options
      $a_input['server_upload_path'] =
           Toolbox::addslashes_deep(
               implode(
                  DIRECTORY_SEPARATOR,
                  array(
                     GLPI_PLUGIN_DOC_DIR,
                     'fusioninventory',
                     'upload'
                  )
               )
           );
      $a_input['alert_winpath'] = 1;
      $a_input['server_as_mirror'] = 1;
      $config->addValues($a_input, FALSE);

      $pfSetup = new PluginFusioninventorySetup();
      $users_id = $pfSetup->createFusionInventoryUser();
      $query = "UPDATE `glpi_plugin_fusioninventory_configs`
                         SET `value`='".$users_id."'
                  WHERE `type`='users_id'";
      $DB->query($query);

      // Update fusinvinventory _config values to this plugin
      $input = array();
      $input['import_monitor']         = 2;
      $input['import_printer']         = 2;
      $input['import_peripheral']      = 2;
      $input['import_software']        = 1;
      $input['import_volume']          = 1;
      $input['import_antivirus']       = 1;
      $input['import_registry']        = 1;
      $input['import_process']         = 1;
      $input['import_vm']              = 1;
      $input['component_processor']    = 1;
      $input['component_memory']       = 1;
      $input['component_harddrive']    = 1;
      $input['component_networkcard']  = 1;
      $input['component_graphiccard']  = 1;
      $input['component_soundcard']    = 1;
      $input['component_drive']        = 1;
      $input['component_networkdrive'] = 1;
      $input['component_control']      = 1;
      $input['states_id_default']      = 0;
      $input['location']               = 0;
      $input['group']                  = 0;
      $input['component_networkcardvirtual'] = 1;
      $config->addValues($input, FALSE);

      // Add new config values if not added
      $input = $config->initConfigModule(TRUE);
      foreach ($input as $name=>$value) {
         $a_conf = $config->find("`type`='".$name."'");
         if (count($a_conf) == 0) {
            $config->add(array('type' => $name, 'value' => $value));
         }
      }




   /*
    * Remove / at the end of printers (bugs in older versions of agents.
    */
      $printer = new Printer();
      $query = "SELECT * FROM `glpi_printers`
         WHERE `serial` LIKE '%/' ";
      $result=$DB->query($query);
      while ($data = $DB->fetch_array($result)) {
         $cleanSerial = preg_replace('/\/$/', '', $data['serial']);
         $querynb = "SELECT * FROM `glpi_printers`
            WHERE `serial`='".$cleanSerial."'
            LIMIT 1";
         $resultnb=$DB->query($querynb);
         if ($DB->numrows($resultnb) == '0') {
            $input = array();
            $input['id'] = $data['id'];
            $input["serial"] = $cleanSerial;
            $printer->update($input);
         }
      }



   /*
    * Update blacklist
    */
   $input = array();
   $input['03000200-0400-0500-0006-000700080009'] = '2';
   $input['6AB5B300-538D-1014-9FB5-B0684D007B53'] = '2';
   $input['01010101-0101-0101-0101-010101010101'] = '2';
   $input['20:41:53:59:4e:ff'] = '3';
   $input['02:00:4e:43:50:49'] = '3';
   $input['e2:e6:16:20:0a:35'] = '3';
   $input['d2:0a:2d:a0:04:be'] = '3';
   $input['00:a0:c6:00:00:00'] = '3';
   $input['d2:6b:25:2f:2c:e7'] = '3';
   $input['33:50:6f:45:30:30'] = '3';
   $input['0a:00:27:00:00:00'] = '3';
   $input['00:50:56:C0:00:01'] = '3';
   $input['00:50:56:C0:00:02'] = '3';
   $input['00:50:56:C0:00:03'] = '3';
   $input['00:50:56:C0:00:04'] = '3';
   $input['00:50:56:C0:00:08'] = '3';
   $input['FE:FF:FF:FF:FF:FF'] = '3';
   $input['00:00:00:00:00:00'] = '3';
   $input['00:0b:ca:fe:00:00'] = '3';
   $input['02:80:37:EC:02:00'] = '3';
   $input['MB-1234567890'] = '1';
   $input['Not Specified'] = '1';
   $input['OEM_Serial'] = '1';
   $input['SystemSerialNumb'] = '1';
   $input['Not'] = '2';
   foreach ($input as $value=>$type) {
      $query = "SELECT * FROM `glpi_plugin_fusioninventory_inventorycomputerblacklists`
         WHERE `plugin_fusioninventory_criterium_id`='".$type."'
          AND `value`='".$value."'";
      $result=$DB->query($query);
      if ($DB->numrows($result) == '0') {
         $query = "INSERT INTO `glpi_plugin_fusioninventory_inventorycomputerblacklists`
            (`plugin_fusioninventory_criterium_id`, `value`) VALUES
            ( '".$type."', '".$value."')";
         $DB->query($query);
      }
   }



   /*
    * Add Crontask if not exist
    */
   $crontask = new CronTask();
   if (!$crontask->getFromDBbyName('PluginFusioninventoryTaskjob', 'taskscheduler')) {
      CronTask::Register('PluginFusioninventoryTaskjob', 'taskscheduler', '60',
                         array('mode' => 2, 'allowmode' => 3, 'logs_lifetime'=> 30));
   }
   if ($crontask->getFromDBbyName('PluginFusioninventoryTaskjobstate', 'cleantaskjob')
           AND $crontask->getFromDBbyName('PluginFusioninventoryTaskjobstatus', 'cleantaskjob')) {
      $crontask->getFromDBbyName('PluginFusioninventoryTaskjobstatus', 'cleantaskjob');
      $crontask->delete($crontask->fields);
   }

   if ($crontask->getFromDBbyName('PluginFusioninventoryTaskjobstatus', 'cleantaskjob')) {
      $query = "UPDATE `glpi_crontasks` SET `itemtype`='PluginFusioninventoryTaskjobstate'
         WHERE `itemtype`='PluginFusioninventoryTaskjobstatus'";
      $DB->query($query);
   }
   if (!$crontask->getFromDBbyName('PluginFusioninventoryTaskjobstate', 'cleantaskjob')) {
      Crontask::Register('PluginFusioninventoryTaskjobstate', 'cleantaskjob', (3600 * 24),
                         array('mode' => 2, 'allowmode' => 3, 'logs_lifetime' => 30));
   }
   if ($crontask->getFromDBbyName('PluginFusinvsnmpNetworkPortLog', 'cleannetworkportlogs')) {
      $crontask->delete($crontask->fields);
   }
   if (!$crontask->getFromDBbyName('PluginFusioninventoryNetworkPortLog', 'cleannetworkportlogs')) {
      Crontask::Register('PluginFusioninventoryNetworkPortLog', 'cleannetworkportlogs', (3600 * 24),
                         array('mode'=>2, 'allowmode'=>3, 'logs_lifetime'=>30));
   }

   /*
    * Update task's agents list from dynamic group periodically in order to automatically target new
    * computer.
    */
   if (!$crontask->getFromDBbyName('PluginFusioninventoryTaskjob', 'updatedynamictasks')) {
      CronTask::Register('PluginFusioninventoryTaskjob', 'updatedynamictasks', '60',
                         array('mode' => 2, 'allowmode' => 3, 'logs_lifetime'=> 30, 'state' => 0));
   }

   /**
   * Add field to manage which group can be refreshed by updatedynamictasks crontask
   */
   if (!FieldExists('glpi_plugin_fusioninventory_deploygroups_dynamicdatas', 'can_update_group')) {
      $migration->addField('glpi_plugin_fusioninventory_deploygroups_dynamicdatas', 'can_update_group', 'bool');
      $migration->addKey('glpi_plugin_fusioninventory_deploygroups_dynamicdatas', 'can_update_group');
      $migration->migrationOneTable('glpi_plugin_fusioninventory_deploygroups_dynamicdatas');
   }
//   $pfIgnoredimportdevice = new PluginFusioninventoryIgnoredimportdevice();
//   $pfIgnoredimportdevice->install();



   // Delete data in glpi_logs(agent problem => ticket http://forge.fusioninventory.org/issues/1546)
   // ** Token
   $query = "DELETE FROM `glpi_logs`
      WHERE `itemtype`='PluginFusioninventoryAgent'
         AND `id_search_option`='9'";
   $DB->query($query);
   // ** Last contact
   $query = "DELETE FROM `glpi_logs`
      WHERE `itemtype`='PluginFusioninventoryAgent'
         AND `id_search_option`='4'";
   $DB->query($query);
   // ** Version
   $query = "DELETE FROM `glpi_logs`
      WHERE `itemtype`='PluginFusioninventoryAgent'
         AND `id_search_option`='8'
         AND `old_value`=`new_value`";
   $DB->query($query);

   /*
    * Import / update SNMP models
    */
   $mode_cli = (basename($_SERVER['SCRIPT_NAME']) == "cli_install.php");

   PluginFusioninventorySnmpmodel::importAllModels('', $mode_cli);

      // Delete snmpmodels_id on networkequipments and printers which has been
      //  deleted and so now not exist
      $query = "SELECT `glpi_plugin_fusioninventory_printers`.`id`
                      FROM `glpi_plugin_fusioninventory_printers`
                           LEFT JOIN `glpi_plugin_fusioninventory_snmpmodels`
                              ON `glpi_plugin_fusioninventory_printers`.".
                                    "`plugin_fusioninventory_snmpmodels_id`=
                                 `glpi_plugin_fusioninventory_snmpmodels`.".
                                    "`id`
                      WHERE `glpi_plugin_fusioninventory_snmpmodels`.`id` IS NULL ";
      $result = $DB->query($query);
      $pfPrinter = new PluginFusioninventoryPrinter();
      while ($data=$DB->fetch_array($result)) {
         $pfPrinter->update(array(
             'id' => $data['id'],
             'plugin_fusioninventory_snmpmodels_id' => 0
         ));
      }
      $query = "SELECT `glpi_plugin_fusioninventory_networkequipments`.`id`
                      FROM `glpi_plugin_fusioninventory_networkequipments`
                           LEFT JOIN `glpi_plugin_fusioninventory_snmpmodels`
                              ON `glpi_plugin_fusioninventory_networkequipments`.".
                                    "`plugin_fusioninventory_snmpmodels_id`=
                                 `glpi_plugin_fusioninventory_snmpmodels`.".
                                    "`id`
                      WHERE `glpi_plugin_fusioninventory_snmpmodels`.`id` IS NULL ";
      $result = $DB->query($query);
      $pfNetworkEquipment = new PluginFusioninventoryNetworkEquipment();
      while ($data=$DB->fetch_array($result)) {
         $pfNetworkEquipment->update(array(
             'id' => $data['id'],
             'plugin_fusioninventory_snmpmodels_id' => 0
         ));
      }


   /*
    * Manage devices with is_dynamic
    */
      $query = "SELECT * FROM `glpi_plugin_fusioninventory_networkequipments`";
      $result=$DB->query($query);
      while ($data=$DB->fetch_array($result)) {
         $DB->query("UPDATE `glpi_networkequipments` SET `is_dynamic`='1'
                        WHERE `id`='".$data['networkequipments_id']."'");
      }

      $query = "SELECT * FROM `glpi_plugin_fusioninventory_inventorycomputercomputers`";
      $result=$DB->query($query);
      while ($data=$DB->fetch_array($result)) {
         $DB->query("UPDATE `glpi_computers` SET `is_dynamic`='1'
                        WHERE `id`='".$data['computers_id']."'");
      }

      $query = "SELECT * FROM `glpi_plugin_fusioninventory_printers`";
      $result=$DB->query($query);
      while ($data=$DB->fetch_array($result)) {
         $DB->query("UPDATE `glpi_printers` SET `is_dynamic`='1'
                        WHERE `id`='".$data['printers_id']."'");
      }



   // Update networkports types
   $pfNetworkporttype = new PluginFusioninventoryNetworkporttype();
   $pfNetworkporttype->init();


   // Define lastup field of fusion networkports
   $query = "SELECT * FROM `glpi_plugin_fusioninventory_mappings`
      WHERE `name`='ifstatus'
      LIMIT 1";
   $result=$DB->query($query);
   while ($data=$DB->fetch_array($result)) {
      $query_np = "SELECT * FROM `glpi_plugin_fusioninventory_networkports`";
      $result_np = $DB->query($query_np);
      while ($data_np = $DB->fetch_array($result_np)) {
         $query_nplog = "SELECT * FROM `glpi_plugin_fusioninventory_networkportlogs`
            WHERE `networkports_id`='".$data_np['networkports_id']."'
               AND `plugin_fusioninventory_mappings_id`='".$data['id']."'
            ORDER BY `date_mod` DESC
            LIMIT 1";
         $result_nplog = $DB->query($query_nplog);
         while ($data_nplog = $DB->fetch_array($result_nplog)) {
            $DB->query("UPDATE `glpi_plugin_fusioninventory_networkports`
               SET `lastup`='".$data_nplog['date_mod']."'
               WHERE `id`='".$data_np['id']."'");
         }
      }
   }
}



function plugin_fusioninventory_displayMigrationMessage ($id, $msg="") {
   static $created=0;
   static $deb;

   if ($created != $id) {
      if (empty($msg)) {
         $msg=__('Work in progress...');
      }

      echo "<div id='migration_message_$id'><p class='center'>$msg</p></div>";
      $created = $id;
      $deb = time();
   } else {
      if (empty($msg)) {
         $msg=__('Task completed.');
      }

      $fin = time();
      $tps = Html::timestampToString($fin-$deb);
      echo "<script type='text/javascript'>document.getElementById('migration_message_$id').".
              "innerHTML = '<p class=\"center\">$msg ($tps)</p>';</script>\n";
   }
   Html::glpi_flush();
}



function changeDisplayPreference($olditemtype, $newitemtype) {
   global $DB;

   $query = "SELECT *, count(`id`) as `cnt` FROM `glpi_displaypreferences`
   WHERE (`itemtype` = '".$newitemtype."'
   OR `itemtype` = '".$olditemtype."')
   group by `users_id`, `num`";
   $result=$DB->query($query);
   while ($data=$DB->fetch_array($result)) {
      if ($data['cnt'] > 1) {
         $queryd = "DELETE FROM `glpi_displaypreferences`
            WHERE `id`='".$data['id']."'";
         $DB->query($queryd);
      }
   }

   $sql = "UPDATE `glpi_displaypreferences`
      SET `itemtype`='".$newitemtype."'
      WHERE `itemtype`='".$olditemtype."' ";
   $DB->query($sql);
}



function pluginFusioninventoryUpdatemapping() {

   /*
    * Udpate mapping
    */
   $pfMapping = new PluginFusioninventoryMapping();

   $a_input = array();
   $a_input['itemtype']    = 'NetworkEquipment';
   $a_input['name']        = 'location';
   $a_input['table']       = 'glpi_networkequipments';
   $a_input['tablefield']  = 'locations_id';
   $a_input['locale']      = 1;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'NetworkEquipment';
   $a_input['name']        = 'firmware';
   $a_input['table']       = 'glpi_networkequipments';
   $a_input['tablefield']  = 'networkequipmentfirmwares_id';
   $a_input['locale']      = 2;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'NetworkEquipment';
   $a_input['name']        = 'firmware1';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 2;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'NetworkEquipment';
   $a_input['name']        = 'firmware2';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 2;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'NetworkEquipment';
   $a_input['name']        = 'contact';
   $a_input['table']       = 'glpi_networkequipments';
   $a_input['tablefield']  = 'contact';
   $a_input['locale']      = 403;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'NetworkEquipment';
   $a_input['name']        = 'comments';
   $a_input['table']       = 'glpi_networkequipments';
   $a_input['tablefield']  = 'comment';
   $a_input['locale']      = 404;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'NetworkEquipment';
   $a_input['name']        = 'uptime';
   $a_input['table']       = 'glpi_plugin_fusioninventory_networkequipments';
   $a_input['tablefield']  = 'uptime';
   $a_input['locale']      = 3;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'NetworkEquipment';
   $a_input['name']        = 'cpu';
   $a_input['table']       = 'glpi_plugin_fusioninventory_networkequipments';
   $a_input['tablefield']  = 'cpu';
   $a_input['locale']      = 12;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'NetworkEquipment';
   $a_input['name']        = 'cpuuser';
   $a_input['table']       = 'glpi_plugin_fusioninventory_networkequipments';
   $a_input['tablefield']  = 'cpu';
   $a_input['locale']      = 401;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'NetworkEquipment';
   $a_input['name']        = 'cpusystem';
   $a_input['table']       = 'glpi_plugin_fusioninventory_networkequipments';
   $a_input['tablefield']  = 'cpu';
   $a_input['locale']      = 402;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'NetworkEquipment';
   $a_input['name']        = 'serial';
   $a_input['table']       = 'glpi_networkequipments';
   $a_input['tablefield']  = 'serial';
   $a_input['locale']      = 13;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'NetworkEquipment';
   $a_input['name']        = 'otherserial';
   $a_input['table']       = 'glpi_networkequipments';
   $a_input['tablefield']  = 'otherserial';
   $a_input['locale']      = 419;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'NetworkEquipment';
   $a_input['name']        = 'name';
   $a_input['table']       = 'glpi_networkequipments';
   $a_input['tablefield']  = 'name';
   $a_input['locale']      = 20;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'NetworkEquipment';
   $a_input['name']        = 'ram';
   $a_input['table']       = 'glpi_networkequipments';
   $a_input['tablefield']  = 'ram';
   $a_input['locale']      = 21;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'NetworkEquipment';
   $a_input['name']        = 'memory';
   $a_input['table']       = 'glpi_plugin_fusioninventory_networkequipments';
   $a_input['tablefield']  = 'memory';
   $a_input['locale']      = 22;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'NetworkEquipment';
   $a_input['name']        = 'vtpVlanName';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 19;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'NetworkEquipment';
   $a_input['name']        = 'vmvlan';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 430;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'NetworkEquipment';
   $a_input['name']        = 'entPhysicalModelName';
   $a_input['table']       = 'glpi_networkequipments';
   $a_input['tablefield']  = 'networkequipmentmodels_id';
   $a_input['locale']      = 17;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'NetworkEquipment';
   $a_input['name']        = 'macaddr';
   $a_input['table']       = 'glpi_networkequipments';
   $a_input['tablefield']  = 'ip';
   $a_input['locale']      = 417;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'NetworkEquipment';
   $a_input['name']        = 'cdpCacheAddress';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 409;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'NetworkEquipment';
   $a_input['name']        = 'cdpCacheDevicePort';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 410;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'NetworkEquipment';
   $a_input['name']        = 'cdpCacheVersion';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 435;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'NetworkEquipment';
   $a_input['name']        = 'cdpCacheDeviceId';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 436;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'NetworkEquipment';
   $a_input['name']        = 'cdpCachePlatform';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 437;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'NetworkEquipment';
   $a_input['name']        = 'lldpRemChassisId';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 431;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'NetworkEquipment';
   $a_input['name']        = 'lldpRemPortId';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 432;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'NetworkEquipment';
   $a_input['name']        = 'lldpLocChassisId';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 432;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'NetworkEquipment';
   $a_input['name']        = 'lldpRemSysDesc';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 438;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'NetworkEquipment';
   $a_input['name']        = 'lldpRemSysName';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 439;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'NetworkEquipment';
   $a_input['name']        = 'lldpRemPortDesc';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 440;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'NetworkEquipment';
   $a_input['name']        = 'vlanTrunkPortDynamicStatus';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 411;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'NetworkEquipment';
   $a_input['name']        = 'dot1dTpFdbAddress';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 412;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'NetworkEquipment';
   $a_input['name']        = 'ipNetToMediaPhysAddress';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 413;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'NetworkEquipment';
   $a_input['name']        = 'dot1dTpFdbPort';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 414;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'NetworkEquipment';
   $a_input['name']        = 'dot1dBasePortIfIndex';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 415;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'NetworkEquipment';
   $a_input['name']        = 'ipAdEntAddr';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 421;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'NetworkEquipment';
   $a_input['name']        = 'PortVlanIndex';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 422;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'NetworkEquipment';
   $a_input['name']        = 'ifIndex';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 408;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'NetworkEquipment';
   $a_input['name']        = 'ifmtu';
   $a_input['table']       = 'glpi_plugin_fusioninventory_networkports';
   $a_input['tablefield']  = 'ifmtu';
   $a_input['locale']      = 4;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'NetworkEquipment';
   $a_input['name']        = 'ifspeed';
   $a_input['table']       = 'glpi_plugin_fusioninventory_networkports';
   $a_input['tablefield']  = 'ifspeed';
   $a_input['locale']      = 5;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'NetworkEquipment';
   $a_input['name']        = 'ifinternalstatus';
   $a_input['table']       = 'glpi_plugin_fusioninventory_networkports';
   $a_input['tablefield']  = 'ifinternalstatus';
   $a_input['locale']      = 6;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'NetworkEquipment';
   $a_input['name']        = 'iflastchange';
   $a_input['table']       = 'glpi_plugin_fusioninventory_networkports';
   $a_input['tablefield']  = 'iflastchange';
   $a_input['locale']      = 7;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'NetworkEquipment';
   $a_input['name']        = 'ifinoctets';
   $a_input['table']       = 'glpi_plugin_fusioninventory_networkports';
   $a_input['tablefield']  = 'ifinoctets';
   $a_input['locale']      = 8;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'NetworkEquipment';
   $a_input['name']        = 'ifoutoctets';
   $a_input['table']       = 'glpi_plugin_fusioninventory_networkports';
   $a_input['tablefield']  = 'ifoutoctets';
   $a_input['locale']      = 9;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'NetworkEquipment';
   $a_input['name']        = 'ifinerrors';
   $a_input['table']       = 'glpi_plugin_fusioninventory_networkports';
   $a_input['tablefield']  = 'ifinerrors';
   $a_input['locale']      = 10;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'NetworkEquipment';
   $a_input['name']        = 'ifouterrors';
   $a_input['table']       = 'glpi_plugin_fusioninventory_networkports';
   $a_input['tablefield']  = 'ifouterrors';
   $a_input['locale']      = 11;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'NetworkEquipment';
   $a_input['name']        = 'ifstatus';
   $a_input['table']       = 'glpi_plugin_fusioninventory_networkports';
   $a_input['tablefield']  = 'ifstatus';
   $a_input['locale']      = 14;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'NetworkEquipment';
   $a_input['name']        = 'ifPhysAddress';
   $a_input['table']       = 'glpi_networkports';
   $a_input['tablefield']  = 'mac';
   $a_input['locale']      = 15;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'NetworkEquipment';
   $a_input['name']        = 'ifName';
   $a_input['table']       = 'glpi_networkports';
   $a_input['tablefield']  = 'name';
   $a_input['locale']      = 16;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'NetworkEquipment';
   $a_input['name']        = 'ifType';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 18;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'NetworkEquipment';
   $a_input['name']        = 'ifdescr';
   $a_input['table']       = 'glpi_plugin_fusioninventory_networkports';
   $a_input['tablefield']  = 'ifdescr';
   $a_input['locale']      = 23;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'NetworkEquipment';
   $a_input['name']        = 'portDuplex';
   $a_input['table']       = 'glpi_plugin_fusioninventory_networkports';
   $a_input['tablefield']  = 'portduplex';
   $a_input['locale']      = 33;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'NetworkEquipment';
   $a_input['name']        = 'ifalias';
   $a_input['table']       = 'glpi_plugin_fusioninventory_networkports';
   $a_input['tablefield']  = 'ifalias';
   $a_input['locale']      = 120;
   $pfMapping->set($a_input);

   // Printers
   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'model';
   $a_input['table']       = 'glpi_printers';
   $a_input['tablefield']  = 'printermodels_id';
   $a_input['locale']      = 25;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'enterprise';
   $a_input['table']       = 'glpi_printers';
   $a_input['tablefield']  = 'manufacturers_id';
   $a_input['locale']      = 420;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'serial';
   $a_input['table']       = 'glpi_printers';
   $a_input['tablefield']  = 'serial';
   $a_input['locale']      = 27;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'contact';
   $a_input['table']       = 'glpi_printers';
   $a_input['tablefield']  = 'contact';
   $a_input['locale']      = 405;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'comments';
   $a_input['table']       = 'glpi_printers';
   $a_input['tablefield']  = 'comment';
   $a_input['locale']      = 406;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'name';
   $a_input['table']       = 'glpi_printers';
   $a_input['tablefield']  = 'comment';
   $a_input['locale']      = 24;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'otherserial';
   $a_input['table']       = 'glpi_printers';
   $a_input['tablefield']  = 'otherserial';
   $a_input['locale']      = 418;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'memory';
   $a_input['table']       = 'glpi_printers';
   $a_input['tablefield']  = 'memory_size';
   $a_input['locale']      = 26;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'location';
   $a_input['table']       = 'glpi_printers';
   $a_input['tablefield']  = 'locations_id';
   $a_input['locale']      = 56;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'informations';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 165;
   $a_input['shortlocale'] = 165;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'tonerblack';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 157;
   $a_input['shortlocale'] = 157;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'tonerblackmax';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 166;
   $a_input['shortlocale'] = 166;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'tonerblackused';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 167;
   $a_input['shortlocale'] = 167;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'tonerblackremaining';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 168;
   $a_input['shortlocale'] = 168;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'tonerblack2';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 157;
   $a_input['shortlocale'] = 157;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'tonerblack2max';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 166;
   $a_input['shortlocale'] = 166;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'tonerblack2used';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 167;
   $a_input['shortlocale'] = 167;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'tonerblack2remaining';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 168;
   $a_input['shortlocale'] = 168;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'tonercyan';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 158;
   $a_input['shortlocale'] = 158;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'tonercyanmax';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 169;
   $a_input['shortlocale'] = 169;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'tonercyanused';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 170;
   $a_input['shortlocale'] = 170;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'tonercyanremaining';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 171;
   $a_input['shortlocale'] = 171;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'tonermagenta';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 159;
   $a_input['shortlocale'] = 159;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'tonermagentamax';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 172;
   $a_input['shortlocale'] = 172;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'tonermagentaused';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 173;
   $a_input['shortlocale'] = 173;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'tonermagentaremaining';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 174;
   $a_input['shortlocale'] = 174;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'toneryellow';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 160;
   $a_input['shortlocale'] = 160;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'toneryellowmax';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 175;
   $a_input['shortlocale'] = 175;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'toneryellowused';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 176;
   $a_input['shortlocale'] = 176;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'toneryellowused';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 177;
   $a_input['shortlocale'] = 177;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'wastetoner';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 151;
   $a_input['shortlocale'] = 151;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'wastetonermax';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 190;
   $a_input['shortlocale'] = 190;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'wastetonerused';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 191;
   $a_input['shortlocale'] = 191;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'wastetonerremaining';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 192;
   $a_input['shortlocale'] = 192;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'cartridgeblack';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 134;
   $a_input['shortlocale'] = 134;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'cartridgeblackphoto';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 135;
   $a_input['shortlocale'] = 135;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'cartridgecyan';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 136;
   $a_input['shortlocale'] = 136;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'cartridgecyanlight';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 139;
   $a_input['shortlocale'] = 139;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'cartridgemagenta';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 138;
   $a_input['shortlocale'] = 138;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'cartridgemagentalight';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 140;
   $a_input['shortlocale'] = 140;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'cartridgeyellow';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 137;
   $a_input['shortlocale'] = 137;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'cartridgegrey';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 196;
   $a_input['shortlocale'] = 196;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'maintenancekit';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 156;
   $a_input['shortlocale'] = 156;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'maintenancekitmax';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 193;
   $a_input['shortlocale'] = 193;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'maintenancekitused';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 194;
   $a_input['shortlocale'] = 194;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'maintenancekitremaining';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 195;
   $a_input['shortlocale'] = 195;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'drumblack';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 161;
   $a_input['shortlocale'] = 161;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'drumblackmax';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 178;
   $a_input['shortlocale'] = 178;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'drumblackused';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 179;
   $a_input['shortlocale'] = 179;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'drumblackremaining';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 180;
   $a_input['shortlocale'] = 180;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'drumcyan';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 162;
   $a_input['shortlocale'] = 162;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'drumcyanmax';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 181;
   $a_input['shortlocale'] = 181;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'drumcyanused';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 182;
   $a_input['shortlocale'] = 182;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'drumcyanremaining';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 183;
   $a_input['shortlocale'] = 183;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'drummagenta';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 163;
   $a_input['shortlocale'] = 163;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'drummagentamax';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 184;
   $a_input['shortlocale'] = 184;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'drummagentaused';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 185;
   $a_input['shortlocale'] = 185;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'drummagentaremaining';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 186;
   $a_input['shortlocale'] = 186;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'drumyellow';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 164;
   $a_input['shortlocale'] = 164;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'drumyellowmax';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 187;
   $a_input['shortlocale'] = 187;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'drumyellowused';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 188;
   $a_input['shortlocale'] = 188;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'drumyellowremaining';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 189;
   $a_input['shortlocale'] = 189;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'pagecountertotalpages';
   $a_input['table']       = 'glpi_plugin_fusioninventory_printerlogs';
   $a_input['tablefield']  = 'pages_total';
   $a_input['locale']      = 28;
   $a_input['shortlocale'] = 128;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'pagecounterblackpages';
   $a_input['table']       = 'glpi_plugin_fusioninventory_printerlogs';
   $a_input['tablefield']  = 'pages_n_b';
   $a_input['locale']      = 29;
   $a_input['shortlocale'] = 129;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'pagecountercolorpages';
   $a_input['table']       = 'glpi_plugin_fusioninventory_printerlogs';
   $a_input['tablefield']  = 'pages_color';
   $a_input['locale']      = 30;
   $a_input['shortlocale'] = 130;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'pagecounterrectoversopages';
   $a_input['table']       = 'glpi_plugin_fusioninventory_printerlogs';
   $a_input['tablefield']  = 'pages_recto_verso';
   $a_input['locale']      = 54;
   $a_input['shortlocale'] = 154;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'pagecounterscannedpages';
   $a_input['table']       = 'glpi_plugin_fusioninventory_printerlogs';
   $a_input['tablefield']  = 'scanned';
   $a_input['locale']      = 55;
   $a_input['shortlocale'] = 155;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'pagecountertotalpages_print';
   $a_input['table']       = 'glpi_plugin_fusioninventory_printerlogs';
   $a_input['tablefield']  = 'pages_total_print';
   $a_input['locale']      = 423;
   $a_input['shortlocale'] = 1423;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'pagecounterblackpages_print';
   $a_input['table']       = 'glpi_plugin_fusioninventory_printerlogs';
   $a_input['tablefield']  = 'pages_n_b_print';
   $a_input['locale']      = 424;
   $a_input['shortlocale'] = 1424;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'pagecountercolorpages_print';
   $a_input['table']       = 'glpi_plugin_fusioninventory_printerlogs';
   $a_input['tablefield']  = 'pages_color_print';
   $a_input['locale']      = 425;
   $a_input['shortlocale'] = 1425;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'pagecountertotalpages_copy';
   $a_input['table']       = 'glpi_plugin_fusioninventory_printerlogs';
   $a_input['tablefield']  = 'pages_total_copy';
   $a_input['locale']      = 426;
   $a_input['shortlocale'] = 1426;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'pagecounterblackpages_copy';
   $a_input['table']       = 'glpi_plugin_fusioninventory_printerlogs';
   $a_input['tablefield']  = 'pages_n_b_copy';
   $a_input['locale']      = 427;
   $a_input['shortlocale'] = 1427;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'pagecountercolorpages_copy';
   $a_input['table']       = 'glpi_plugin_fusioninventory_printerlogs';
   $a_input['tablefield']  = 'pages_color_copy';
   $a_input['locale']      = 428;
   $a_input['shortlocale'] = 1428;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'pagecountertotalpages_fax';
   $a_input['table']       = 'glpi_plugin_fusioninventory_printerlogs';
   $a_input['tablefield']  = 'pages_total_fax';
   $a_input['locale']      = 429;
   $a_input['shortlocale'] = 1429;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'pagecounterlargepages';
   $a_input['table']       = 'glpi_plugin_fusioninventory_printerlogs';
   $a_input['tablefield']  = 'pages_total_large';
   $a_input['locale']      = 434;
   $a_input['shortlocale'] = 1434;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'ifPhysAddress';
   $a_input['table']       = 'glpi_networkports';
   $a_input['tablefield']  = 'mac';
   $a_input['locale']      = 48;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'ifName';
   $a_input['table']       = 'glpi_networkports';
   $a_input['tablefield']  = 'name';
   $a_input['locale']      = 57;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'ifaddr';
   $a_input['table']       = 'glpi_networkports';
   $a_input['tablefield']  = 'ip';
   $a_input['locale']      = 407;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'ifType';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 97;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Printer';
   $a_input['name']        = 'ifIndex';
   $a_input['table']       = '';
   $a_input['tablefield']  = '';
   $a_input['locale']      = 416;
   $pfMapping->set($a_input);


   // ** Computer
   $a_input = array();
   $a_input['itemtype']    = 'Computer';
   $a_input['name']        = 'serial';
   $a_input['table']       = '';
   $a_input['tablefield']  = 'serial';
   $a_input['locale']      = 13;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Computer';
   $a_input['name']        = 'ifPhysAddress';
   $a_input['table']       = '';
   $a_input['tablefield']  = 'mac';
   $a_input['locale']      = 15;
   $pfMapping->set($a_input);

   $a_input = array();
   $a_input['itemtype']    = 'Computer';
   $a_input['name']        = 'ifaddr';
   $a_input['table']       = '';
   $a_input['tablefield']  = 'ip';
   $a_input['locale']      = 407;
   $pfMapping->set($a_input);

}



function update213to220_ConvertField($migration) {
   global $DB;

   // ----------------------------------------------------------------------
   //NETWORK MAPPING MAPPING
   // ----------------------------------------------------------------------
   $constantsfield = array();

   $constantsfield['reseaux > lieu'] = 'location';
   $constantsfield['networking > location'] = 'location';
   $constantsfield['Netzwerk > Standort'] = 'location';

   $constantsfield['réseaux > firmware'] = 'firmware';
   $constantsfield['networking > firmware'] = 'firmware';
   $constantsfield['Netzwerk > Firmware'] = 'firmware';

   $constantsfield['réseaux > firmware'] = 'firmware1';
   $constantsfield['networking > firmware'] = 'firmware1';
   $constantsfield['Netzwerk > Firmware'] = 'firmware1';

   $constantsfield['réseaux > firmware'] = 'firmware2';
   $constantsfield['networking > firmware'] = 'firmware2';
   $constantsfield['Netzwerk > Firmware'] = 'firmware2';

   $constantsfield['réseaux > contact'] = 'contact';
   $constantsfield['networking > contact'] = 'contact';
   $constantsfield['Netzwerk > Kontakt'] = 'contact';

   $constantsfield['réseaux > description'] = 'comments';
   $constantsfield['networking > comments'] = 'comments';
   $constantsfield['Netzwerk > Kommentar'] = 'comments';

   $constantsfield['réseaux > uptime'] = 'uptime';
   $constantsfield['networking > uptime'] = 'uptime';
   $constantsfield['Netzwerk > Uptime'] = 'uptime';

   $constantsfield['réseaux > utilisation du CPU'] = 'cpu';
   $constantsfield['networking > CPU usage'] = 'cpu';
   $constantsfield['Netzwerk > CPU Auslastung'] = 'cpu';

   $constantsfield['réseaux > CPU user'] = 'cpuuser';
   $constantsfield['networking > CPU usage (user)'] = 'cpuuser';
   $constantsfield['Netzwerk > CPU Benutzer'] = 'cpuuser';

   $constantsfield['réseaux > CPU système'] = 'cpusystem';
   $constantsfield['networking > CPU usage (system)'] = 'cpusystem';
   $constantsfield['Netzwerk > CPU System'] = 'cpusystem';

   $constantsfield['réseaux > numéro de série'] = 'serial';
   $constantsfield['networking > serial number'] = 'serial';
   $constantsfield['Netzwerk > Seriennummer'] = 'serial';

   $constantsfield['réseaux > numéro d\'inventaire'] = 'otherserial';
   $constantsfield['networking > Inventory number'] = 'otherserial';
   $constantsfield['Netzwerk > Inventarnummer'] = 'otherserial';

   $constantsfield['réseaux > nom'] = 'name';
   $constantsfield['networking > name'] = 'name';
   $constantsfield['Netzwerk > Name'] = 'name';

   $constantsfield['réseaux > mémoire totale'] = 'ram';
   $constantsfield['networking > total memory'] = 'ram';
   $constantsfield['Netzwerk > Gesamter Speicher'] = 'ram';

   $constantsfield['réseaux > mémoire libre'] = 'memory';
   $constantsfield['networking > free memory'] = 'memory';
   $constantsfield['Netzwerk > Freier Speicher'] = 'memory';

   $constantsfield['réseaux > VLAN'] = 'vtpVlanName';
   $constantsfield['networking > VLAN'] = 'vtpVlanName';
   $constantsfield['Netzwerk > VLAN'] = 'vtpVlanName';

   $constantsfield['réseaux > port > vlan'] = 'vmvlan';
   $constantsfield['networking > port > vlan'] = 'vmvlan';

   $constantsfield['réseaux > modèle'] = 'entPhysicalModelName';
   $constantsfield['networking > model'] = 'entPhysicalModelName';
   $constantsfield['Netzwerk > Modell'] = 'entPhysicalModelName';

   $constantsfield['réseaux > adresse MAC'] = 'macaddr';
   $constantsfield['networking > MAC address'] = 'macaddr';
   $constantsfield['Netzwerk > MAC Adresse'] = 'macaddr';

   $constantsfield['réseaux > Adresse CDP'] = 'cdpCacheAddress';
   $constantsfield['networking > CDP address'] = 'cdpCacheAddress';
   $constantsfield['Netzwerk > Adresse CDP'] = 'cdpCacheAddress';

   $constantsfield['réseaux > port CDP'] = 'cdpCacheDevicePort';
   $constantsfield['networking > CDP port'] = 'cdpCacheDevicePort';
   $constantsfield['Netzwerk > Port CDP'] = 'cdpCacheDevicePort';

   $constantsfield['réseaux > chassis id distant LLDP'] = 'lldpRemChassisId';
   $constantsfield['networking > remote chassis id LLDP'] = 'lldpRemChassisId';

   $constantsfield['réseaux > port distant LLDP'] = 'lldpRemPortId';
   $constantsfield['networking > remote port LLDP'] = 'lldpRemPortId';

   $constantsfield['réseaux > chassis id local LLDP'] = 'lldpLocChassisId';
   $constantsfield['networking > localchassis id LLDP'] = 'lldpLocChassisId';

   $constantsfield['réseaux > port > trunk/tagged'] = 'vlanTrunkPortDynamicStatus';
   $constantsfield['networking > port > trunk/tagged'] = 'vlanTrunkPortDynamicStatus';
   $constantsfield['Netzwerk > Port > trunk/tagged'] = 'vlanTrunkPortDynamicStatus';

   $constantsfield['trunk'] = 'vlanTrunkPortDynamicStatus';

   $constantsfield['réseaux > Adresses mac filtrées (dot1dTpFdbAddress)'] = 'dot1dTpFdbAddress';
   $constantsfield['networking > MAC address filters (dot1dTpFdbAddress)'] = 'dot1dTpFdbAddress';
   $constantsfield['Netzwerk > MAC Adressen Filter (dot1dTpFdbAddress)'] = 'dot1dTpFdbAddress';

   $constantsfield['réseaux > adresses physiques mémorisées (ipNetToMediaPhysAddress)'] =
                  'ipNetToMediaPhysAddress';
   $constantsfield['networking > Physical addresses in memory (ipNetToMediaPhysAddress)'] =
                  'ipNetToMediaPhysAddress';
   $constantsfield['Netzwerk > Physikalische Adressen im Speicher (ipNetToMediaPhysAddress)'] =
                  'ipNetToMediaPhysAddress';

   $constantsfield['réseaux > instances de ports (dot1dTpFdbPort)'] = 'dot1dTpFdbPort';
   $constantsfield['networking > Port instances (dot1dTpFdbPort)'] = 'dot1dTpFdbPort';
   $constantsfield['Netzwerk > Instanzen des Ports (dot1dTpFdbPort)'] = 'dot1dTpFdbPort';

   $constantsfield['réseaux > numéro de ports associé ID du port (dot1dBasePortIfIndex)'] =
                  'dot1dBasePortIfIndex';
   $constantsfield['networking > Port number associated with port ID (dot1dBasePortIfIndex)'] =
                  'dot1dBasePortIfIndex';
   $constantsfield['Netzwerk > Verkn&uuml;pfung der Portnummerierung mit der ID des Ports (dot1dBasePortIfIndex)'] = 'dot1dBasePortIfIndex';

   $constantsfield['réseaux > addresses IP'] = 'ipAdEntAddr';
   $constantsfield['networking > IP addresses'] = 'ipAdEntAddr';
   $constantsfield['Netzwerk > IP Adressen'] = 'ipAdEntAddr';

   $constantsfield['réseaux > portVlanIndex'] = 'PortVlanIndex';
   $constantsfield['networking > portVlanIndex'] = 'PortVlanIndex';
   $constantsfield['Netzwerk > portVlanIndex'] = 'PortVlanIndex';

   $constantsfield['réseaux > port > numéro index'] = 'ifIndex';
   $constantsfield['networking > port > index number'] = 'ifIndex';
   $constantsfield['Netzwerk > Port > Nummerischer Index'] = 'ifIndex';

   $constantsfield['réseaux > port > mtu'] = 'ifmtu';
   $constantsfield['networking > port > mtu'] = 'ifmtu';
   $constantsfield['Netzwerk > Port > MTU'] = 'ifmtu';

   $constantsfield['réseaux > port > vitesse'] = 'ifspeed';
   $constantsfield['networking > port > speed'] = 'ifspeed';
   $constantsfield['Netzwerk > Port > Geschwindigkeit'] = 'ifspeed';

   $constantsfield['réseaux > port > statut interne'] = 'ifinternalstatus';
   $constantsfield['networking > port > internal status'] = 'ifinternalstatus';
   $constantsfield['Netzwerk > Port > Interner Zustand'] = 'ifinternalstatus';

   $constantsfield['réseaux > port > Dernier changement'] = 'iflastchange';
   $constantsfield['networking > ports > Last change'] = 'iflastchange';
   $constantsfield['Netzwerk > Ports > Letzte &Auml;nderung'] = 'iflastchange';

   $constantsfield['réseaux > port > nombre d\'octets entrés'] = 'ifinoctets';
   $constantsfield['networking > port > number of bytes in'] = 'ifinoctets';
   $constantsfield['Netzwerk > Port > Anzahl eingegangene Bytes'] = 'ifinoctets';

   $constantsfield['réseaux > port > nombre d\'octets sortis'] = 'ifoutoctets';
   $constantsfield['networking > port > number of bytes out'] = 'ifoutoctets';
   $constantsfield['Netzwerk > Port > Anzahl ausgehende Bytes'] = 'ifoutoctets';

   $constantsfield['réseaux > port > nombre d\'erreurs entrées'] = 'ifinerrors';
   $constantsfield['networking > port > number of input errors'] = 'ifinerrors';
   $constantsfield['Netzwerk > Port > Anzahl Input Fehler'] = 'ifinerrors';

   $constantsfield['réseaux > port > nombre d\'erreurs sorties'] = 'ifouterrors';
   $constantsfield['networking > port > number of output errors'] = 'ifouterrors';
   $constantsfield['Netzwerk > Port > Anzahl Fehler Ausgehend'] = 'ifouterrors';

   $constantsfield['réseaux > port > statut de la connexion'] = 'ifstatus';
   $constantsfield['networking > port > connection status'] = 'ifstatus';
   $constantsfield['Netzwerk > Port > Verbingungszustand'] = 'ifstatus';

   $constantsfield['réseaux > port > adresse MAC'] = 'ifPhysAddress';
   $constantsfield['networking > port > MAC address'] = 'ifPhysAddress';
   $constantsfield['Netzwerk > Port > MAC Adresse'] = 'ifPhysAddress';

   $constantsfield['réseaux > port > nom'] = 'ifName';
   $constantsfield['networking > port > name'] = 'ifName';
   $constantsfield['Netzwerk > Port > Name'] = 'ifName';

   $constantsfield['réseaux > port > type'] = 'ifType';
   $constantsfield['networking > ports > type'] = 'ifType';
   $constantsfield['Netzwerk > Ports > Typ'] = 'ifType';

   $constantsfield['réseaux > port > description du port'] = 'ifdescr';
   $constantsfield['networking > port > port description'] = 'ifdescr';
   $constantsfield['Netzwerk > Port > Port Bezeichnung'] = 'ifdescr';

   $constantsfield['réseaux > port > type de duplex'] = 'portDuplex';
   $constantsfield['networking > port > duplex type'] = 'portDuplex';
   $constantsfield['Netzwerk > Port > Duplex Typ'] = 'portDuplex';

   $constantsfield['imprimante > modèle'] = 'model';
   $constantsfield['printer > model'] = 'model';
   $constantsfield['Drucker > Modell'] = 'model';

   $constantsfield['imprimante > fabricant'] = 'enterprise';
   $constantsfield['printer > manufacturer'] = 'enterprise';
   $constantsfield['Drucker > Hersteller'] = 'enterprise';

   $constantsfield['imprimante > numéro de série'] = 'serial';
   $constantsfield['printer > serial number'] = 'serial';
   $constantsfield['Drucker > Seriennummer'] = 'serial';

   $constantsfield['imprimante > contact'] = 'contact';
   $constantsfield['printer > contact'] = 'contact';
   $constantsfield['Drucker > Kontakt'] = 'contact';

   $constantsfield['imprimante > description'] = 'comments';
   $constantsfield['printer > comments'] = 'comments';
   $constantsfield['Drucker > Kommentar'] = 'comments';

   $constantsfield['imprimante > nom'] = 'name';
   $constantsfield['printer > name'] = 'name';
   $constantsfield['Drucker > Name'] = 'name';

   $constantsfield['imprimante > numéro d\'inventaire'] = 'otherserial';
   $constantsfield['printer > Inventory number'] = 'otherserial';
   $constantsfield['Drucker > Inventarnummer'] = 'otherserial';

   $constantsfield['imprimante > mémoire totale'] = 'memory';
   $constantsfield['printer > total memory'] = 'memory';
   $constantsfield['Drucker > Gesamter Speicher'] = 'memory';

   $constantsfield['imprimante > lieu'] = 'location';
   $constantsfield['printer > location'] = 'location';
   $constantsfield['Drucker > Standort'] = 'location';

   $constantsfield['Informations diverses regroupées'] = 'informations';
   $constantsfield['Many informations grouped'] = 'informations';
   $constantsfield['Many informations grouped'] = 'informations';

   $constantsfield['Toner Noir'] = 'tonerblack';
   $constantsfield['Black toner'] = 'tonerblack';

   $constantsfield['Toner Noir Max'] = 'tonerblackmax';
   $constantsfield['Black toner Max'] = 'tonerblackmax';

   $constantsfield['Toner Noir Utilisé'] = 'tonerblackused';

   $constantsfield['Toner Noir Restant'] = 'tonerblackremaining';

   $constantsfield['Toner Noir'] = 'tonerblack2';
   $constantsfield['Black toner'] = 'tonerblack2';

   $constantsfield['Toner Noir Max'] = 'tonerblack2max';
   $constantsfield['Black toner Max'] = 'tonerblack2max';

   $constantsfield['Toner Noir Utilisé'] = 'tonerblack2used';

   $constantsfield['Toner Noir Restant'] = 'tonerblack2remaining';

   $constantsfield['Toner Cyan'] = 'tonercyan';
   $constantsfield['Cyan toner'] = 'tonercyan';

   $constantsfield['Toner Cyan Max'] = 'tonercyanmax';
   $constantsfield['Cyan toner Max'] = 'tonercyanmax';

   $constantsfield['Toner Cyan Utilisé'] = 'tonercyanused';

   $constantsfield['Toner Cyan Restant'] = 'tonercyanremaining';

   $constantsfield['Toner Magenta'] = 'tonermagenta';
   $constantsfield['Magenta toner'] = 'tonermagenta';

   $constantsfield['Toner Magenta Max'] = 'tonermagentamax';
   $constantsfield['Magenta toner Max'] = 'tonermagentamax';

   $constantsfield['Toner Magenta Utilisé'] = 'tonermagentaused';
   $constantsfield['Magenta toner Utilisé'] = 'tonermagentaused';

   $constantsfield['Toner Magenta Restant'] = 'tonermagentaremaining';
   $constantsfield['Magenta toner Restant'] = 'tonermagentaremaining';

   $constantsfield['Toner Jaune'] = 'toneryellow';
   $constantsfield['Yellow toner'] = 'toneryellow';

   $constantsfield['Toner Jaune Max'] = 'toneryellowmax';
   $constantsfield['Yellow toner Max'] = 'toneryellowmax';

   $constantsfield['Toner Jaune Utilisé'] = 'toneryellowused';
   $constantsfield['Yellow toner Utilisé'] = 'toneryellowused';

   $constantsfield['Toner Jaune Restant'] = 'toneryellowremaining';
   $constantsfield['Yellow toner Restant'] = 'toneryellowremaining';

   $constantsfield['Bac récupérateur de déchet'] = 'wastetoner';
   $constantsfield['Waste bin'] = 'wastetoner';
   $constantsfield['Abfalleimer'] = 'wastetoner';

   $constantsfield['Bac récupérateur de déchet Max'] = 'wastetonermax';
   $constantsfield['Waste bin Max'] = 'wastetonermax';

   $constantsfield['Bac récupérateur de déchet Utilisé'] = 'wastetonerused';
   $constantsfield['Waste bin Utilisé'] = 'wastetonerused';

   $constantsfield['Bac récupérateur de déchet Restant'] = 'wastetonerremaining';
   $constantsfield['Waste bin Restant'] = 'wastetonerremaining';

   $constantsfield['Cartouche noir'] = 'cartridgeblack';
   $constantsfield['Black ink cartridge'] = 'cartridgeblack';
   $constantsfield['Schwarze Kartusche'] = 'cartridgeblack';

   $constantsfield['Cartouche noir photo'] = 'cartridgeblackphoto';
   $constantsfield['Photo black ink cartridge'] = 'cartridgeblackphoto';
   $constantsfield['Photoschwarz Kartusche'] = 'cartridgeblackphoto';

   $constantsfield['Cartouche cyan'] = 'cartridgecyan';
   $constantsfield['Cyan ink cartridge'] = 'cartridgecyan';
   $constantsfield['Cyan Kartusche'] = 'cartridgecyan';

   $constantsfield['Cartouche cyan clair'] = 'cartridgecyanlight';
   $constantsfield['Light cyan ink cartridge'] = 'cartridgecyanlight';
   $constantsfield['Leichtes Cyan Kartusche'] = 'cartridgecyanlight';

   $constantsfield['Cartouche magenta'] = 'cartridgemagenta';
   $constantsfield['Magenta ink cartridge'] = 'cartridgemagenta';
   $constantsfield['Magenta Kartusche'] = 'cartridgemagenta';

   $constantsfield['Cartouche magenta clair'] = 'cartridgemagentalight';
   $constantsfield['Light ink magenta cartridge'] = 'cartridgemagentalight';
   $constantsfield['Leichtes Magenta Kartusche'] = 'cartridgemagentalight';

   $constantsfield['Cartouche jaune'] = 'cartridgeyellow';
   $constantsfield['Yellow ink cartridge'] = 'cartridgeyellow';
   $constantsfield['Gelbe Kartusche'] = 'cartridgeyellow';

   $constantsfield['Cartouche grise'] = 'cartridgegrey';
   $constantsfield['Grey ink cartridge'] = 'cartridgegrey';
   $constantsfield['Grey ink cartridge'] = 'cartridgegrey';

   $constantsfield['Kit de maintenance'] = 'maintenancekit';
   $constantsfield['Maintenance kit'] = 'maintenancekit';
   $constantsfield['Wartungsmodul'] = 'maintenancekit';

   $constantsfield['Kit de maintenance Max'] = 'maintenancekitmax';
   $constantsfield['Maintenance kit Max'] = 'maintenancekitmax';

   $constantsfield['Kit de maintenance Utilisé'] = 'maintenancekitused';
   $constantsfield['Maintenance kit Utilisé'] = 'maintenancekitused';

   $constantsfield['Kit de maintenance Restant'] = 'maintenancekitremaining';
   $constantsfield['Maintenance kit Restant'] = 'maintenancekitremaining';

   $constantsfield['Tambour Noir'] = 'drumblack';
   $constantsfield['Black drum'] = 'drumblack';

   $constantsfield['Tambour Noir Max'] = 'drumblackmax';
   $constantsfield['Black drum Max'] = 'drumblackmax';

   $constantsfield['Tambour Noir Utilisé'] = 'drumblackused';
   $constantsfield['Black drum Utilisé'] = 'drumblackused';

   $constantsfield['Tambour Noir Restant'] = 'drumblackremaining';
   $constantsfield['Black drum Restant'] = 'drumblackremaining';

   $constantsfield['Tambour Cyan'] = 'drumcyan';
   $constantsfield['Cyan drum'] = 'drumcyan';

   $constantsfield['Tambour Cyan Max'] = 'drumcyanmax';
   $constantsfield['Cyan drum Max'] = 'drumcyanmax';

   $constantsfield['Tambour Cyan Utilisé'] = 'drumcyanused';
   $constantsfield['Cyan drum Utilisé'] = 'drumcyanused';

   $constantsfield['Tambour Cyan Restant'] = 'drumcyanremaining';
   $constantsfield['Cyan drumRestant'] = 'drumcyanremaining';

   $constantsfield['Tambour Magenta'] = 'drummagenta';
   $constantsfield['Magenta drum'] = 'drummagenta';

   $constantsfield['Tambour Magenta Max'] = 'drummagentamax';
   $constantsfield['Magenta drum Max'] = 'drummagentamax';

   $constantsfield['Tambour Magenta Utilisé'] = 'drummagentaused';
   $constantsfield['Magenta drum Utilisé'] = 'drummagentaused';

   $constantsfield['Tambour Magenta Restant'] = 'drummagentaremaining';
   $constantsfield['Magenta drum Restant'] = 'drummagentaremaining';

   $constantsfield['Tambour Jaune'] = 'drumyellow';
   $constantsfield['Yellow drum'] = 'drumyellow';

   $constantsfield['Tambour Jaune Max'] = 'drumyellowmax';
   $constantsfield['Yellow drum Max'] = 'drumyellowmax';

   $constantsfield['Tambour Jaune Utilisé'] = 'drumyellowused';
   $constantsfield['Yellow drum Utilisé'] = 'drumyellowused';

   $constantsfield['Tambour Jaune Restant'] = 'drumyellowremaining';
   $constantsfield['Yellow drum Restant'] = 'drumyellowremaining';

   $constantsfield['imprimante > compteur > nombre total de pages imprimées'] =
                  'pagecountertotalpages';
   $constantsfield['printer > meter > total number of printed pages'] = 'pagecountertotalpages';
   $constantsfield['Drucker > Messung > Gesamtanzahl gedruckter Seiten'] = 'pagecountertotalpages';

   $constantsfield['imprimante > compteur > nombre de pages noir et blanc imprimées'] =
                  'pagecounterblackpages';
   $constantsfield['printer > meter > number of printed black and white pages'] =
                  'pagecounterblackpages';
   $constantsfield['Drucker > Messung > Gesamtanzahl gedrucker Schwarz/Wei&szlig; Seiten'] =
                  'pagecounterblackpages';

   $constantsfield['imprimante > compteur > nombre de pages couleur imprimées'] =
                  'pagecountercolorpages';
   $constantsfield['printer > meter > number of printed color pages'] = 'pagecountercolorpages';
   $constantsfield['Drucker > Messung > Gesamtanzahl gedruckter Farbseiten'] =
                  'pagecountercolorpages';

   $constantsfield['imprimante > compteur > nombre de pages recto/verso imprimées'] =
                  'pagecounterrectoversopages';
   $constantsfield['printer > meter > number of printed duplex pages'] =
                  'pagecounterrectoversopages';
   $constantsfield['Drucker > Messung > Anzahl der gedruckten Duplex Seiten'] =
                  'pagecounterrectoversopages';

   $constantsfield['imprimante > compteur > nombre de pages scannées'] = 'pagecounterscannedpages';
   $constantsfield['printer > meter > nomber of scanned pages'] = 'pagecounterscannedpages';
   $constantsfield['Drucker > Messung > Anzahl der gescannten Seiten'] = 'pagecounterscannedpages';

   $constantsfield['imprimante > compteur > nombre total de pages imprimées (impression)'] =
                  'pagecountertotalpages_print';
   $constantsfield['printer > meter > total number of printed pages (print mode)'] =
                  'pagecountertotalpages_print';
   $constantsfield['Drucker > Messung > Gesamtanzahl gedruckter Seiten (Druck)'] =
                  'pagecountertotalpages_print';

   $constantsfield['imprimante > compteur > nombre de pages noir et blanc imprimées (impression)'] =
                  'pagecounterblackpages_print';
   $constantsfield['printer > meter > number of printed black and white pages (print mode)'] =
                  'pagecounterblackpages_print';
   $constantsfield['Drucker > Messung > Gesamtanzahl gedruckter Schwarz/Wei&szlig; Seiten (Druck)']=
                  'pagecounterblackpages_print';

   $constantsfield['imprimante > compteur > nombre de pages couleur imprimées (impression)'] =
                  'pagecountercolorpages_print';
   $constantsfield['printer > meter > number of printed color pages (print mode)'] =
                  'pagecountercolorpages_print';
   $constantsfield['Drucker > Messung > Gesamtanzahl farbig gedruckter Seiten (Druck)'] =
                  'pagecountercolorpages_print';

   $constantsfield['imprimante > compteur > nombre total de pages imprimées (copie)'] =
                  'pagecountertotalpages_copy';
   $constantsfield['printer > meter > total number of printed pages (copy mode)'] =
                  'pagecountertotalpages_copy';
   $constantsfield['Drucker > Messung > Gesamtanzahl gedruckter Seiten (Kopie)'] =
                  'pagecountertotalpages_copy';

   $constantsfield['imprimante > compteur > nombre de pages noir et blanc imprimées (copie)'] =
                  'pagecounterblackpages_copy';
   $constantsfield['printer > meter > number of printed black and white pages (copy mode)'] =
                  'pagecounterblackpages_copy';
   $constantsfield['Drucker > Messung > Gesamtanzahl gedruckter Schwarz/Wei&szlig; Seite (Kopie)'] =
                  'pagecounterblackpages_copy';

   $constantsfield['imprimante > compteur > nombre de pages couleur imprimées (copie)'] =
                  'pagecountercolorpages_copy';
   $constantsfield['printer > meter > number of printed color pages (copy mode)'] =
                  'pagecountercolorpages_copy';
   $constantsfield['Drucker > Messung > Gesamtanzahl farbig gedruckter Seiten (Kopie)'] =
                  'pagecountercolorpages_copy';

   $constantsfield['imprimante > compteur > nombre total de pages imprimées (fax)'] =
                  'pagecountertotalpages_fax';
   $constantsfield['printer > meter > total number of printed pages (fax mode)'] =
                  'pagecountertotalpages_fax';
   $constantsfield['Drucker > Messung > Gesamtanzahl gedruckter Seiten (Fax)'] =
                  'pagecountertotalpages_fax';

   $constantsfield['imprimante > compteur > nombre total de pages larges imprimées'] =
                  'pagecounterlargepages';
   $constantsfield['printer > meter > total number of large printed pages'] =
                  'pagecounterlargepages';

   $constantsfield['imprimante > port > adresse MAC'] = 'ifPhysAddress';
   $constantsfield['printer > port > MAC address'] = 'ifPhysAddress';
   $constantsfield['Drucker > Port > MAC Adresse'] = 'ifPhysAddress';

   $constantsfield['imprimante > port > nom'] = 'ifName';
   $constantsfield['printer > port > name'] = 'ifName';
   $constantsfield['Drucker > Port > Name'] = 'ifName';

   $constantsfield['imprimante > port > adresse IP'] = 'ifaddr';
   $constantsfield['printer > port > IP address'] = 'ifaddr';
   $constantsfield['Drucker > Port > IP Adresse'] = 'ifaddr';

   $constantsfield['imprimante > port > type'] = 'ifType';
   $constantsfield['printer > port > type'] = 'ifType';
   $constantsfield['Drucker > port > Typ'] = 'ifType';

   $constantsfield['imprimante > port > numéro index'] = 'ifIndex';
   $constantsfield['printer > port > index number'] = 'ifIndex';
   $constantsfield['Drucker > Port > Indexnummer'] = 'ifIndex';

   if (TableExists("glpi_plugin_tracker_snmp_history")) {
      echo "Converting history port ...\n";
      $i = 0;
      $nb = count($constantsfield);
         $migration->addKey("glpi_plugin_tracker_snmp_history",
                         "Field");
      $migration->addKey("glpi_plugin_tracker_snmp_history",
                         array("Field", "old_value"),
                         "Field_2");
      $migration->addKey("glpi_plugin_tracker_snmp_history",
                         array("Field", "new_value"),
                         "Field_3");
      $migration->migrationOneTable("glpi_plugin_tracker_snmp_history");

      foreach($constantsfield as $langvalue=>$mappingvalue) {
         $i++;
         $query_update = "UPDATE `glpi_plugin_tracker_snmp_history`
            SET `Field`='".$mappingvalue."'
            WHERE `Field`=\"".$langvalue."\" ";
         $DB->query($query_update);
         $migration->displayMessage("$i / $nb");
      }
      $migration->displayMessage("$i / $nb");

      // Move connections from glpi_plugin_fusioninventory_snmp_history to
      // glpi_plugin_fusioninventory_snmp_history_connections
      echo "Moving creation connections history\n";
      $query = "SELECT *
                FROM `glpi_plugin_tracker_snmp_history`
                WHERE `Field` = '0'
                  AND ((`old_value` NOT LIKE '%:%')
                        OR (`old_value` IS NULL))";
      if ($result=$DB->query($query)) {
         $nb = $DB->numrows($result);
         $i = 0;
         $migration->displayMessage("$i / $nb");
         while ($data=$DB->fetch_array($result)) {
            $i++;

            // Search port from mac address
            $query_port = "SELECT * FROM `glpi_networkports`
               WHERE `mac`='".$data['new_value']."' ";
            if ($result_port=$DB->query($query_port)) {
               if ($DB->numrows($result_port) == '1') {
                  $input = array();
                  $data_port = $DB->fetch_assoc($result_port);
                  $input['FK_port_source'] = $data_port['id'];

                  $query_port2 = "SELECT * FROM `glpi_networkports`
                     WHERE `items_id` = '".$data['new_device_ID']."'
                        AND `itemtype` = '".$data['new_device_type']."' ";
                  if ($result_port2=$DB->query($query_port2)) {
                     if ($DB->numrows($result_port2) == '1') {
                        $data_port2 = $DB->fetch_assoc($result_port2);
                        $input['FK_port_destination'] = $data_port2['id'];

                        $input['date'] = $data['date_mod'];
                        $input['creation'] = 1;
                        $input['process_number'] = $data['FK_process'];
                        $query_ins = "INSERT INTO `glpi_plugin_fusinvsnmp_networkportconnectionlogs`
                           (`date_mod`, `creation`, `networkports_id_source`,
                            `networkports_id_destination`)
                           VALUES ('".$input['date']."',
                                   '".$input['creation']."',
                                   '".$input['FK_port_source']."',
                                   '".$input['FK_port_destination']."')";
                        $DB->query($query_ins);
                     }
                  }
               }
            }

            $query_delete = "DELETE FROM `glpi_plugin_tracker_snmp_history`
                  WHERE `ID`='".$data['ID']."' ";
            $DB->query($query_delete);
            if (preg_match("/000$/", $i)) {
               $migration->displayMessage("$i / $nb");
            }
         }
         $migration->displayMessage("$i / $nb");
      }

      echo "Moving deleted connections history\n";
      $query = "SELECT *
                FROM `glpi_plugin_tracker_snmp_history`
                WHERE `Field` = '0'
                  AND ((`new_value` NOT LIKE '%:%')
                        OR (`new_value` IS NULL))";
      if ($result=$DB->query($query)) {
         $nb = $DB->numrows($result);
         $i = 0;
         $migration->displayMessage("$i / $nb");
         while ($data=$DB->fetch_array($result)) {
            $i++;

            // Search port from mac address
            $query_port = "SELECT * FROM `glpi_networkports`
               WHERE `mac`='".$data['old_value']."' ";
            if ($result_port=$DB->query($query_port)) {
               if ($DB->numrows($result_port) == '1') {
                  $input = array();
                  $data_port = $DB->fetch_assoc($result_port);
                  $input['FK_port_source'] = $data_port['id'];

                  $query_port2 = "SELECT * FROM `glpi_networkports`
                     WHERE `items_id` = '".$data['old_device_ID']."'
                        AND `itemtype` = '".$data['old_device_type']."' ";
                  if ($result_port2=$DB->query($query_port2)) {
                     if ($DB->numrows($result_port2) == '1') {
                        $data_port2 = $DB->fetch_assoc($result_port2);
                        $input['FK_port_destination'] = $data_port2['id'];

                        $input['date'] = $data['date_mod'];
                        $input['creation'] = 1;
                        $input['process_number'] = $data['FK_process'];
                        if ($input['FK_port_source'] != $input['FK_port_destination']) {
                           $query_ins = "INSERT INTO
                                 `glpi_plugin_fusinvsnmp_networkportconnectionlogs`
                              (`date_mod`, `creation`, `networkports_id_source`,
                               `networkports_id_destination`)
                              VALUES ('".$input['date']."',
                                      '".$input['creation']."',
                                      '".$input['FK_port_source']."',
                                      '".$input['FK_port_destination']."')";
                           $DB->query($query_ins);
                        }
                     }
                  }
               }
            }

            $query_delete = "DELETE FROM `glpi_plugin_tracker_snmp_history`
                  WHERE `ID`='".$data['ID']."' ";
            $DB->query($query_delete);
            if (preg_match("/000$/", $i)) {
               $migration->displayMessage("$i / $nb");
            }
         }
         $migration->displayMessage("$i / $nb");
      }
   }
}

function pluginFusioninventorychangeDisplayPreference($olditemtype, $newitemtype) {
   global $DB;

   $query = "SELECT *, count(`id`) as `cnt` FROM `glpi_displaypreferences`
   WHERE (`itemtype` = '".$newitemtype."'
   OR `itemtype` = '".$olditemtype."')
   group by `users_id`, `num`";
   $result=$DB->query($query);
   while ($data=$DB->fetch_array($result)) {
      if ($data['cnt'] > 1) {
         $queryd = "DELETE FROM `glpi_displaypreferences`
            WHERE `id`='".$data['id']."'";
         $DB->query($queryd);
      }
   }
}



function migrateTablesFusionInventory($migration, $a_table) {
   global $DB;

   foreach ($a_table['oldname'] as $oldtable) {
      $migration->renameTable($oldtable, $a_table['name']);
   }

   if (!TableExists($a_table['name'])) {

      if (strstr($a_table['name'], 'glpi_plugin_fusioninventory_dblock')) {
         $query = "CREATE TABLE `".$a_table['name']."` (
                        `value` int(11) NOT NULL AUTO_INCREMENT,
                        PRIMARY KEY (`value`)
                     ) ENGINE=MEMORY  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1";
      } else {
         $query = "CREATE TABLE `".$a_table['name']."` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        PRIMARY KEY (`id`)
                     ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1";
      }

      $DB->query($query);
   }

   foreach ($a_table['renamefields'] as $old=>$new) {
      $migration->changeField($a_table['name'],
                              $old,
                              $new,
                              $a_table['fields'][$new]['type'],
                              array('value' => $a_table['fields'][$new]['value'],
                                    'update'=> TRUE));
   }

   foreach ($a_table['oldfields'] as $field) {
      $migration->dropField($a_table['name'],
                            $field);
   }
   $migration->migrationOneTable($a_table['name']);

   foreach ($a_table['fields'] as $field=>$data) {
      $migration->changeField($a_table['name'],
                              $field,
                              $field,
                              $data['type'],
                              array('value' => $data['value']));
   }
   $migration->migrationOneTable($a_table['name']);

   foreach ($a_table['fields'] as $field=>$data) {
      $migration->addField($a_table['name'],
                           $field,
                           $data['type'],
                           array('value' => $data['value']));
   }
   $migration->migrationOneTable($a_table['name']);

   foreach ($a_table['oldkeys'] as $field) {
      $migration->dropKey($a_table['name'],
                          $field);
   }
   $migration->migrationOneTable($a_table['name']);

   foreach ($a_table['keys'] as $data) {
      $migration->addKey($a_table['name'],
                         $data['field'],
                         $data['name'],
                         $data['type']);
   }
   $migration->migrationOneTable($a_table['name']);

   $DB->list_fields($a_table['name'], FALSE);
}

/**
 * Migrate tables from plugin fusinvdeploy to fusioninventory
 *    all datas in exploded tables are merged and stored in json in order table
 * @param  Migration $migration
 * @return nothing
 */
function migrateTablesFromFusinvDeploy ($migration) {
   global $DB;



   if (     TableExists("glpi_plugin_fusioninventory_deployorders")
         && TableExists("glpi_plugin_fusinvdeploy_checks")
         && TableExists("glpi_plugin_fusinvdeploy_files")
         && TableExists("glpi_plugin_fusinvdeploy_actions")
   ) {


      //add json field in deploy order table to store datas from old misc tables
      $field_created = $migration->addField("glpi_plugin_fusioninventory_deployorders",
                                    "json",
                                    "longtext DEFAULT NULL");
      $migration->migrationOneTable("glpi_plugin_fusioninventory_deployorders");

      $final_datas = array();

      //== glpi_plugin_fusioninventory_deployorders ==
      $o_query = "SELECT * FROM glpi_plugin_fusioninventory_deployorders";
      $o_res = $DB->query($o_query);
      while($o_datas = $DB->fetch_assoc($o_res)) {
         $order_id = $o_datas['id'];

         $o_line = array();
         $of_line = array();

         $o_line['checks'] = array();
         $o_line['actions'] = array();
         $o_line['associatedFiles'] = array();

         //=== Checks ===

         if (TableExists("glpi_plugin_fusinvdeploy_checks")) {
            $c_query = "SELECT type, path, value, 'error' as `return`
               FROM glpi_plugin_fusinvdeploy_checks
               WHERE plugin_fusinvdeploy_orders_id = $order_id
               ORDER BY ranking ASC";
            $c_res = $DB->query($c_query);
            $c_i = 0;
            while ($c_datas = $DB->fetch_assoc($c_res)) {
               foreach ($c_datas as $c_key => $c_value) {
                  //specific case for filesytem sizes, convert to bytes
                  if (
                     !empty($c_value)
                     && is_numeric($c_value)
                     && $c_datas['type'] !== 'freespaceGreater'
                  ) {
                     $c_value = $c_value * 1024 * 1024;
                  }

                  //construct job check entry
                  $o_line['checks'][$c_i][$c_key] = $c_value;
               }
                $c_i++;
            }
         }

         $files_list = array();
         //=== Files ===
         if (TableExists("glpi_plugin_fusinvdeploy_files")) {
            $f_query =
               "SELECT id, name, is_p2p as p2p, filesize, mimetype, ".
               "p2p_retention_days as `p2p-retention-duration`, uncompress, sha512 ".
               "FROM glpi_plugin_fusinvdeploy_files ".
               "WHERE plugin_fusinvdeploy_orders_id = $order_id";
            $f_res = $DB->query($f_query);
            while ($f_datas = $DB->fetch_assoc($f_res)) {

               //jump to next entry if sha512 is empty
               // This kind of entries could happen sometimes on upload errors
               if (empty($f_datas['sha512'])) {
                  continue;
               }

               //construct job file entry
               $o_line['associatedFiles'][] = $f_datas['sha512'];

               foreach ($f_datas as $f_key => $f_value) {

                  //we don't store the sha512 field in json
                  if (  $f_key == "sha512"
                     || $f_key == "id"
                     || $f_key == "filesize"
                     || $f_key == "mimetype") {
                     continue;
                  }

                  //construct order file entry
                  $of_line[$f_datas['sha512']][$f_key] = $f_value;
               }

               if (!in_array($f_datas['sha512'], $files_list)) {
                  $files_list[] = $f_datas['sha512'];
               }

            }
         }



         //=== Actions ===
         $cmdStatus['RETURNCODE_OK'] = 'okCode';
         $cmdStatus['RETURNCODE_KO'] = 'errorCode';
         $cmdStatus['REGEX_OK'] = 'okPattern';
         $cmdStatus['REGEX_KO'] = 'errorPattern';

         if (TableExists("glpi_plugin_fusinvdeploy_actions")) {
            $a_query = "SELECT *
               FROM glpi_plugin_fusinvdeploy_actions
               WHERE plugin_fusinvdeploy_orders_id = $order_id
               ORDER BY ranking ASC";
            $a_res = $DB->query($a_query);
            $a_i = 0;
            while ($a_datas = $DB->fetch_assoc($a_res)) {

               //get type
               $type = strtolower(str_replace("PluginFusinvdeployAction_", "", $a_datas['itemtype']));

               //specific case for command type
               $type = str_replace("command", "cmd", $type);

               //table for action itemtype
               $a_table = getTableForItemType($a_datas['itemtype']);

               //get table fields
               $at_query = "SELECT *
                  FROM $a_table
                  WHERE id = ".$a_datas['items_id'];
               $at_res = $DB->query($at_query);
               while($at_datas = $DB->fetch_assoc($at_res)) {
                  foreach($at_datas as $at_key => $at_value) {
                     //we don't store the id field of action itemtype table in json
                     if ($at_key == "id") {
                        continue;
                     }

                     //specific case for 'path' field
                     if ($at_key == "path") {
                        $o_line['actions'][$a_i][$type]['list'][] = $at_value;
                     } else {
                        //construct job actions entry
                        $o_line['actions'][$a_i][$type][$at_key] = $at_value;
                     }
                  }

                  //specific case for commands : we must add status and env vars
                  if ($a_datas['itemtype'] === "PluginFusinvdeployAction_Command") {
                     $ret_cmd_query = "SELECT type, value
                        FROM glpi_plugin_fusinvdeploy_actions_commandstatus
                        WHERE plugin_fusinvdeploy_commands_id = ".$at_datas['id'];
                     $ret_cmd_res = $DB->query($ret_cmd_query);
                     while ($res_cmd_datas = $DB->fetch_assoc($ret_cmd_res)) {
                        // Skip empty retchecks type:
                        // This surely means they have been drop at some point but entry has not been
                        // removed from database.
                        if (!empty($res_cmd_datas['type'])) {
                           //construct command status array entry
                           $o_line['actions'][$a_i][$type]['retChecks'][] = array(
                              'type'  => $cmdStatus[$res_cmd_datas['type']],
                              'values' => array($res_cmd_datas['value'])
                           );
                        }
                     }
                  }
               }
               $a_i++;
            }
         }
         $final_datas[$order_id]['jobs'] = $o_line;
         $final_datas[$order_id]['associatedFiles'] = $of_line;
         unset($o_line);
         unset($of_line);
      }
      $options = 0;
      if (version_compare(PHP_VERSION, '5.3.3') >= 0) {
         $options = $options | JSON_NUMERIC_CHECK;
      }
      if (version_compare(PHP_VERSION, '5.4.0') >= 0) {
         $options = $options | JSON_UNESCAPED_SLASHES;
      }

      //store json in order table
      foreach ($final_datas as $order_id => $data) {
         $json = $DB->escape(json_encode($data, $options));

         $order_query = "UPDATE glpi_plugin_fusioninventory_deployorders
            SET json = '$json'
            WHERE id = $order_id";
         $DB->query($order_query);
      }
   }



   //=== Fileparts ===
   if (     TableExists('glpi_plugin_fusinvdeploy_fileparts')
         && TableExists('glpi_plugin_fusinvdeploy_files')
   ) {
      $files_list = $DB->request('glpi_plugin_fusinvdeploy_files');
      // multipart file datas
      foreach ($files_list as $file) {
         $sha = $file['sha512'];
         if( empty($sha) ){
            continue;
         }
         $shortsha = substr($sha, 0, 6);
         $fp_query = "SELECT  fp.`sha512` as filepart_hash, ".
            "        f.`sha512`  as file_hash      ".
            "FROM `glpi_plugin_fusinvdeploy_files` as f ".
            "INNER JOIN `glpi_plugin_fusinvdeploy_fileparts` as fp ".
            "ON   f.`id` = fp.`plugin_fusinvdeploy_files_id` ".
            "     AND f.`shortsha512` = '{$shortsha}' ".
            "GROUP BY fp.`sha512` ".
            "ORDER BY fp.`id`";


         $fp_res = $DB->query($fp_query);
         if ($DB->numrows($fp_res) > 0) {
            print("writing file : " . GLPI_PLUGIN_DOC_DIR."/fusioninventory/files/manifests/{$sha}" . "\n");
            $fhandle = fopen(
               GLPI_PLUGIN_DOC_DIR."/fusioninventory/files/manifests/{$sha}",
               'w+'
            );
            while ($fp_datas = $DB->fetch_assoc($fp_res)) {
               if ($fp_datas['file_hash'] === $sha) {
                  fwrite($fhandle, $fp_datas['filepart_hash']."\n");
               }
            }
            fclose($fhandle);
         }
      }
   }

   //migrate fusinvdeploy_files to fusioninventory_deployfiles
   if (TableExists("glpi_plugin_fusinvdeploy_files")) {
      $DB->query("TRUNCATE TABLE `glpi_plugin_fusioninventory_deployfiles`");
      if (FieldExists("glpi_plugin_fusinvdeploy_files", "filesize")) {
         $f_query =
            implode(array(
               "SELECT  files.`id`, files.`name`,",
               "        files.`filesize`, files.`mimetype`,",
               "        files.`sha512`, files.`shortsha512`,",
               "        files.`create_date`,",
               "        pkgs.`entities_id`, pkgs.`is_recursive`",
               "FROM glpi_plugin_fusinvdeploy_files as files",
               "LEFT JOIN glpi_plugin_fusioninventory_deployorders as orders",
               "  ON orders.`id` = files.`plugin_fusinvdeploy_orders_id`",
               "LEFT JOIN glpi_plugin_fusioninventory_deploypackages as pkgs",
               "  ON orders.`plugin_fusioninventory_deploypackages_id` = pkgs.`id`",
               "WHERE",
               "  files.`shortsha512` != \"\""
            ), " \n");
         $f_res = $DB->query($f_query);
         while($f_datas = $DB->fetch_assoc($f_res)) {
            $entry = array(
               "id"        => $f_datas["id"],
               "name"      => $f_datas["name"],
               "filesize"  => $f_datas["filesize"],
               "mimetype"  => $f_datas["mimetype"],
               "shortsha512"  => $f_datas["shortsha512"],
               "sha512"  => $f_datas["sha512"],
               "comments"  => "",
               "date_mod"  => $f_datas["create_date"],
               "entities_id"  => $f_datas["entities_id"],
               "is_recursive"  => $f_datas["is_recursive"],
            );
            $migration->displayMessage("\n");
            // Check if file exists
            $i_DeployFile = new PluginFusioninventoryDeployFile();
            $migration->displayMessage(
               "migrating file ". $entry['name'] .
               " sha:" . $entry['sha512'] .
               "\n"
            );
            if ($i_DeployFile->checkPresenceManifest($entry['sha512'])) {
               $migration->displayMessage(
                  "manifest exists" .
                  "\n"
               );
               $migration->insertInTable(
                  "glpi_plugin_fusioninventory_deployfiles", $entry
               );
            }
         }
      }
   }

   /**
    * JSON orders fixer:
    *    This piece of code makes sure that JSON orders in database are valid and will fix it
    *    otherwise.
    */

   $orders = $DB->request('glpi_plugin_fusioninventory_deployorders');
   foreach( $orders as $order_config ) {
      $pfDeployOrder = new PluginFusioninventoryDeployOrder();
      $json_order = json_decode($order_config['json']);
//      print("deployorders fixer : actual order structure for ID ".$order_config['id']."\n" . print_r($json_order,true) ."\n");

      // Checks for /jobs json property
      if( !isset($json_order->jobs) || !is_object($json_order->jobs) ) {
//         print("deployorders fixer : create missing required 'jobs' property\n");
         $json_order->jobs = new stdClass();
      }

      if ( !isset($json_order->jobs->checks) ) {
//         print("deployorders fixer : create missing required '/jobs/checks' array property\n");
         $json_order->jobs->checks = array();
      }
      if ( !isset($json_order->jobs->actions) ) {
//         print("deployorders fixer : create missing required '/jobs/actions' array property\n");
         $json_order->jobs->actions = array();
      }
      if ( !isset($json_order->jobs->associatedFiles) ) {
//         print("deployorders fixer : create missing required '/jobs/associatedFiles' array property\n");
         $json_order->jobs->associatedFiles = array();
      }

      // Checks for /associatedFiles json property
      if( !isset($json_order->associatedFiles) || !is_object($json_order->associatedFiles) ) {
//         print("deployorders fixer : create missing required 'associatedFiles' property\n");
         $json_order->associatedFiles = new stdClass();
      }
//      print(
//         "deployorders fixer : final order structure for ID ".$order_config['id']."\n" .
//         json_encode($json_order,JSON_PRETTY_PRINT) ."\n"
//      );
      $pfDeployOrder::updateOrderJson($order_config['id'], $json_order);
   }

   /**
    * Drop unused tables
    */
   $old_deploy_tables = array(
      'glpi_plugin_fusinvdeploy_actions',
      'glpi_plugin_fusinvdeploy_actions_commandenvvariables',
      'glpi_plugin_fusinvdeploy_actions_commands',
      'glpi_plugin_fusinvdeploy_actions_commandstatus',
      'glpi_plugin_fusinvdeploy_actions_copies',
      'glpi_plugin_fusinvdeploy_actions_deletes',
      'glpi_plugin_fusinvdeploy_actions_messages',
      'glpi_plugin_fusinvdeploy_actions_mkdirs',
      'glpi_plugin_fusinvdeploy_actions_moves',
      'glpi_plugin_fusinvdeploy_checks',
      'glpi_plugin_fusinvdeploy_fileparts',
      'glpi_plugin_fusinvdeploy_files',
      'glpi_plugin_fusinvdeploy_files_mirrors'
   );
   foreach ($old_deploy_tables as $table) {
      $migration->dropTable($table);
   }

   //drop unused views
   $old_deploy_views = array(
      'glpi_plugin_fusinvdeploy_taskjobs',
      'glpi_plugin_fusinvdeploy_tasks'
   );
   foreach ($old_deploy_views as $view) {
      $DB->query("DROP VIEW IF EXISTS $view");
   }
}

?>
