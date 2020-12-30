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

$widget = (new CWidget())
	->setTitle(_('Items in testing'))
	->setControls((new CList())
		->addItem(get_icon('fullscreen', ['fullscreen' => getRequest('fullscreen')]))
	)
;

// create form
$itemForm = (new CForm())->setName('items');
if (!empty($data['hostid'])) {
	$itemForm->addVar('hostid', $data['hostid']);
}

$url = (new CUrl('zabbix.php'))
	->setArgument('action', 'test.list')
	->getUrl();

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

	// host name
	$host = (new CLink(CHtml::encode(
			$item['hostname']
		),
		'hosts.php?form=update&hostid='.$item['hostid']
	));

	// item name
	$name = (new CLink(CHtml::encode(
			$item['itemname']
		),
		'items.php?form=update&hostid='.$item['hostid'].'&itemid='.$item['itemid']
	));

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
