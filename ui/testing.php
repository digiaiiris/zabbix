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
**/

require_once dirname(__FILE__).'/include/config.inc.php';
$page['title'] = _('Item Testing');
$page['file'] = 'testing.php';
$page['type'] = detect_page_type(PAGE_TYPE_HTML);
require_once dirname(__FILE__).'/include/page_header.php';

$widget = (new CWidget())
   ->setTitle(_('Items in testing'))
   ->setControls((new CList())
      ->addItem(get_icon('fullscreen', ['fullscreen' => getRequest('fullscreen')]))
   )
;
$sql = 'SELECT h.host AS hostname, h.hostid AS hostid, i.name AS itemname, i.itemid AS itemid, t.test_value as test_value FROM item_testing t JOIN items i ON i.itemid = t.itemid JOIN hosts h ON h.hostid = t.hostid;';
$data['items'] = array();
$result = DBselect($sql);
while ($row = DBfetch($result)) {
   $data['items'][] = $row;
}

$result_ids = DBselect('SELECT itemid FROM item_testing;');
$itemIds = DBfetchColumn($result_ids, 'itemid');

$itemTriggerIds = API::Item()->get([
   'output' => ['itemid'],
   'selectTriggers' => ['triggerid'],
   'itemids' => $itemIds
]);
// echo '<script>console.log('. json_encode( $itemTriggerIds ) .')</script>';

/*
$data['itemTriggers'] = API::Trigger()->get([
   'triggerids' => $itemTriggerIds,
   'output' => ['triggerid', 'description', 'expression', 'recovery_mode', 'recovery_expression', 'priority',
      'status', 'state', 'error', 'templateid', 'flags'
   ],
   'selectHosts' => ['hostid', 'name', 'host'],
   'preservekeys' => true
]);
*/
$data['sort'] = "item";
$data['sortorder'] = "ASC";

echo '<script>console.log('. json_encode( $data ) .')</script>';
//$widget->addItem($data['main_filter']); // ????

// create form
$itemForm = (new CForm())->setName('items');
if (!empty($data['hostid'])) {
       $itemForm->addVar('hostid', $data['hostid']);
}

$url = (new CUrl('testing.php'))->getUrl();

// create table
$itemTable = (new CTableInfo())
       ->setHeader([
      make_sorting_header(_('Host'), 'host', $data['sort'], $data['sortorder'], $url),
               make_sorting_header(_('Item'), 'item', $data['sort'], $data['sortorder'], $url),
               //_('Triggers'),
      _('Test value'),
      _('Test')
       ]);

foreach ($data['items'] as $item) {

/*
   // get item triggerids
   $item['triggers'] = API::Item()->get([
      'selectTriggers' => ['triggerid'],
      'itemids' => $item['itemid']
   ]);
*/
   // host name
   $host = (new CLink(
      CHtml::encode(
         $item['hostname']
      ),
      'hosts.php?form=update&hostid='.$item['hostid']
   ));

       // item name
       $name = (new CLink(
      CHtml::encode(
         $item['itemname']
      ),
      'items.php?form=update&hostid='.$item['hostid'].'&itemid='.$item['itemid']
   ));
/*
       // triggers info
       $triggerHintTable = (new CTableInfo())->setHeader([_('Name'), _('Expression'), _('Status'), _('Value')]);

       foreach ($item['triggers'] as $num => &$trigger) {
               $trigger = $data['itemTriggers'][$trigger['triggerid']];

               $trigger_description = [];

               $trigger['hosts'] = zbx_toHash($trigger['hosts'], 'hostid');

               if ($trigger['flags'] == ZBX_FLAG_DISCOVERY_CREATED) {
                       $trigger_description[] = new CSpan(CHtml::encode($trigger['description']));
               }
               else {
                       $trigger_description[] = new CLink(
                               CHtml::encode($trigger['description']),
                               'triggers.php?form=update&hostid='.key($trigger['hosts']).'&triggerid='.$trigger['triggerid']
                       );
               }

               if ($trigger['state'] == TRIGGER_STATE_UNKNOWN) {
                       $trigger['error'] = '';
               }

               if ($trigger['recovery_mode'] == ZBX_RECOVERY_MODE_RECOVERY_EXPRESSION) {
                       $expression = [
                               _('Problem'), ': ', $trigger['expression'], BR(),
                               _('Recovery'), ': ', $trigger['recovery_expression']
                       ];
               }
               else {
                       $expression = $trigger['expression'];
               }

               $triggerHintTable->addRow([
                       $trigger_description,
                       $expression,
                       (new CSpan(triggerIndicator($trigger['status'], $trigger['state'])))
            ->addClass(triggerIndicatorStyle($trigger['status'], $trigger['state'])),
         $trigger['value']
               ]);
       }
       unset($trigger);

       if ($triggerHintTable->getNumRows()) {
               $triggerInfo = (new CLinkAction(_('Triggers')))->setHint($triggerHintTable);
               $triggerInfo = [$triggerInfo];
               $triggerInfo[] = CViewHelper::showNum($triggerHintTable->getNumRows());

               $triggerHintTable = [];
       }
       else {
               $triggerInfo = '';
   }
*/
   // stop testing button
   $style = ITEM_STATUS_DISABLED;
   $testBtn = new CLink( _('Stop'), 'items.php?group_itemid[]='.$item['itemid'].'&action=test.stop');
   $testBtn->addSID();
   $testBtn->addConfirmation('Stop testing?');

   // stop test button
   $stop = new CCol(($testBtn)
      ->addClass(ZBX_STYLE_LINK_ACTION)
      ->addClass(itemIndicatorStyle($style))
   );

   // add all elements to table
       $itemTable->addRow([
      $host,
      $name,
               //$triggerInfo,
      $item['test_value'],
      $stop
       ]);
}

// append table to form
$itemForm->addItem($itemTable);

// append form to widget
$widget->addItem($itemForm);

$widget->show();

require_once dirname(__FILE__).'/include/page_footer.php';
