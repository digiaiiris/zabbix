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
   $result = false;
   $error = false;
   try {
      enable_test_maintenance($org_item['hostid'], $org_item['host']['maintenance_status']);

      $param_test_value = $test_value;
      $test_item = $org_item;
      // use original key as identifier for new keys. Needs to be modified not to break the new key
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
   
   if (!$error && $result) {
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

      $response = API::Item()->update($item);

      if ($response) {
         $result = remove_from_testing_table($test_item_id);
         if ($result) {
            remove_test_maintenance($item['hostid']);
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
   $result = DB::delete('item_testing', array('itemid'=>$itemid));

   return $result;
}

/**
 * Put host on maintenance at start of testing
 *
 * @param string $hostid   host to start testing
 * @param integer $in_maintenance   maintenance status
 * 
 * @return bool
 */
function enable_test_maintenance($hostid, $in_maintenance) {
   $test_maintenance = false;
   $result = false;

   $now = date('Y-m-d+H:i');
   $year = (365 * 24 * 60 * 60);
   $next_year = $now + $year;
	$active_since_date = $now;
   $active_till_date = $next_year;

   if ($in_maintenance == 1) { 
      $test_maintenance = API::Maintenance()->get([
         'selectHosts' => ['hostid'],
         'selectTimeperiods' => API_OUTPUT_EXTEND,
         'filter' => ['name'='Testing maintenance'],
         'output' => ['maintenanceid', 'name', 'active_till']
      ]);
      if ($test_maintenance) {
         $test_maintenance = $test_maintenance[0];
      }
   }

   if (!$test_maintenance) { # create new test maintenance
      $maintenance = [
         'name' => 'Testing maintenance',
         'description' => 'Maintenance for testing tool',
         'active_since' => $active_since_date,
         'active_till' => $active_till_date,
         'timeperiods' => [{
            "timeperiod_type": 0,
            "period": $year
         }],
         'hostids' => $hostid
      ];
      $result = API::Maintenance()->create($maintenance);
   
   } else { # update test maintenance
      $hostids = $test_maintenance['hostids'];
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
 * Remove test maintenance on host (only if there is no items to test)
 *
 * @param string $hostid   host to start testing
 * 
 * @return bool
 */
function remove_test_maintenance($hostid) {
   $test_maintenance = API::Maintenance()->get([
      'selectHosts' => ['hostid'],
      'selectTimeperiods' => API_OUTPUT_EXTEND,
      'filter' => ['name'='Testing maintenance'],
      'output' => ['maintenanceid', 'name', 'active_till']
   ]);
}

/*
{
  "Form data": {
    "sid": "5cb8c5e124738363",
    "form_refresh": "3",
    "form": "create",
    "timeperiods[0][timeperiod_type]": "0",
    "timeperiods[0][every]": "1",
    "timeperiods[0][month]": "0",
    "timeperiods[0][dayofweek]": "0",
    "timeperiods[0][day]": "1",
    "timeperiods[0][start_time]": "43200",
    "timeperiods[0][start_date]": "2019-12-17+08:25",
    "timeperiods[0][period]": "90000",
    "mname": "twst",
    "maintenance_type": "0",
    "active_since": "2019-12-17+00:00",
    "active_till": "2019-12-18+00:00",
    "description": "sse",
    "hostids[]": "10084",
    "tags_evaltype": "0",
    "tags[0][tag]": "",
    "tags[0][operator]": "2",
    "tags[0][value]": "",
    "add": "Add"
  }
}
*/