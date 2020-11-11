<?php

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

   if (!$error) {
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
      $items = [];
      if ($response) {
         $items[] = $test_item_id;
         $result = DB::delete('item_testing', array('itemid'=>$items));
      }
   } catch (Exception $e) {
      $error = true;
   }

   if (!$error) {
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