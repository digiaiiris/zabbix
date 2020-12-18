<?php
/*
** Zabbix
** Copyright (C) 2001-2019 Zabbix SIA
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
**
** For Digia Iiris testing tool
**
**/


/**
 * Converts given item to DBmonitor item for testing 
 *
 * @param array $org_item   item to start testing
 * @param string $test_value   value to use during test
 * @param string $test_delay   interval to send test value
 * 
 * @return bool
 */
function convert_item_to_test($org_item, $test_value, $test_delay) {
   $response = false;
   $error = false;
   try {
      enable_test_maintenance($org_item['hostid']);

      $param_test_value = $test_value;
      $test_item = $org_item;
      // use original key as identifier for new keys. Original key needs to be modified not to break the new key
      $org_key = $test_item['key_'];
      $org_key = str_replace('[','(',$org_key);
      $org_key = str_replace(']',')',$org_key);

      if ($test_item['value_type'] == '0') { # original item is integer
         $param_test_value = (int)$test_value;
      } elseif ($test_item['value_type'] == '3') { # original item is float
         $param_test_value = (float)$test_value;
      }

      $test_item['type'] = '11';
      $test_item['key_'] = 'db.odbc.select[' . $org_key . ',zabbix]' ;
      $test_item['params'] = 'SELECT ' . $param_test_value;
      $test_item['delay'] = $test_delay;

      $response = API::Item()->update($test_item);
      
   } catch (Exception $e) {
      $error = true;
   }
   
   if (!$error && $response) {
      return TRUE;
   } else {
      return FALSE;
   }
}

/**
 * Converts given test item back to original item
 *
 * @param string $test_item_id   item to revert back to original item
 * 
 * @return bool
 */
function revert_test_to_original($test_item_id) {
   $result = false;
   $error = false;
   try {
      $sql_select = 'SELECT * FROM item_testing WHERE itemid='. zbx_dbstr($test_item_id);
      $result = DBselect($sql_select);
      $testItem = DBfetchArray($result)[0];

      $item = [];
      foreach ($testItem as $key => $value) {
         if($key != 'test_value' && $key != 'test_delay' && $key != 'testid') {
            $item[$key] = $value;
         }
      }
      $item['itemid'] = $test_item_id;

      // remove fields that can't be updated
      $remove = ['lastlogsize', 'mtime'];
      foreach ($remove as $element ) {
         unset($item[$element]);
      }
      
      $response = API::Item()->update($item);

      if ($response) {
         $result = remove_from_testing_table($test_item_id);
         
         $sql_test_items_on_host = 'SELECT COUNT(hostid) AS "hosts" FROM item_testing WHERE hostid=' . zbx_dbstr($item['hostid']);
         $sql_result = DBselect($sql_test_items_on_host);
         $host_test_item_count = DBfetchArray($sql_result)[0]['hosts'];

         if ($result && !($host_test_item_count > 0)) {
            remove_from_test_maintenance($item['hostid']);
         }
      }
   } catch (Exception $e) {
      $error = true;
   }

   if (!$error && $result) {
      return TRUE;
   } else {
      return FALSE;
   }
}

/**
 * Adds item to item_testing table 
 *
 * @param array $item   item to be added
 * 
 * @return bool
 */
function add_to_testing_table($item) {
   $result = false;
   $values = array_values($item);
   foreach($values as $value) {
      if(gettype($value) == 'string') {
         $value = zbx_dbstr($value);
      }
   }

   $sql = 'INSERT INTO item_testing ('.implode(',', array_keys($item)).')'.
         ' VALUES ('.implode(',', $values).')';

   $result = DBexecute($sql);

   return $result;
}

/**
 * Removes item from item_testing table 
 *
 * @param string $itemid   item to be removed
 * 
 * @return bool
 */
function remove_from_testing_table($itemid) {
   $result = false;
   try {
      $result = DB::delete('item_testing', array('itemid'=>$itemid));
   } catch (Exception $e) {
      $result = true;
   }

   return $result;
}

/**
 * Put host on maintenance at start of testing
 *
 * @param string $hostid   host to start testing
 * 
 * @return bool
 */
function enable_test_maintenance($hostid) {
   $test_maintenance = false;
   $result = false;

   $year = (365 * 24 * 60 * 60);
	$active_since_date = time();
   $active_till_date = time() + $year;

   // check if test maintenance exists
   $result = API::Maintenance()->get([
      'selectHosts' => ['hostid'],
      'selectTimeperiods' => API_OUTPUT_EXTEND,
      'filter' => ['name'=>'Testing maintenance'],
      'output' => ['maintenanceid', 'name', 'active_till']
   ]);
   if ($result) {
      $test_maintenance = $result[0];
   }

   if (!$test_maintenance) { # create new test maintenance
      $maintenance = [
         'name' => 'Testing maintenance',
         'description' => 'Maintenance for testing tool. DO NOT REMOVE HOSTS.',
         'active_since' => $active_since_date,
         'active_till' => $active_till_date,
         'timeperiods' => [ 
            "0"=> [
               "timeperiod_type" => 0,
               "period" => $year
            ]
         ],
         'hostids' => [ $hostid ]
      ];
      
      $result = API::Maintenance()->create($maintenance);
   
   } else { # update test maintenance
      $hosts = $test_maintenance['hosts'];
      $hostids = [];

      foreach ($hosts as $host) {
         $hostids[] = $host['hostid'];
      }
      $hostids[] = $hostid;

      $maintenance = [
         'maintenanceid' => $test_maintenance['maintenanceid'],
         'hostids' => $hostids
      ];
      $result = API::Maintenance()->update($maintenance);
   }
   return $result;
}

/**
 * Remove host from test maintenance 
 * (and delete test maintenance if there is no hosts left)
 *
 * @param string $hostid   host to start testing
 * 
 * @return bool
 */
function remove_from_test_maintenance($hostid) {
   $test_maintenance = API::Maintenance()->get([
      'selectHosts' => ['hostid'],
      'selectTimeperiods' => API_OUTPUT_EXTEND,
      'filter' => ['name'=>'Testing maintenance'],
      'output' => ['maintenanceid', 'name', 'active_till']
   ]);

   $old_hosts = $test_maintenance[0]['hosts'];
   $new_hostids = [];

   if(count($test_maintenance[0]['hosts']) > 1) {
      foreach ($old_hosts as $value) {
         
         if($value['hostid'] != $hostid) {
            $new_hostids[] = $value['hostid'];
         }
      }

      $update_maintenance = [
         'maintenanceid' => $test_maintenance[0]['maintenanceid'],
         'hostids' => $new_hostids
      ];

      $result = API::Maintenance()->update($update_maintenance);

   } else {
      $remove_maintenance = [
         $test_maintenance[0]['maintenanceid']
      ];

      $result = API::Maintenance()->delete($remove_maintenance);
   }

   return $result;
}