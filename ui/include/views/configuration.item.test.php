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

$widget = (new CWidget())->setTitle(_('Testing'));

if (!empty($data['hostid'])) {
   $widget->addItem(get_header_host_table('items', $data['hostid']));
}

// Create form.
$form = (new CForm())
   ->setName('testForm')
   ->setAttribute('aria-labeledby', ZBX_STYLE_PAGE_TITLE)
   ->addVar('form', 'test')
   ->addVar('hostid', $data['hostid'])
   ->addVar('itemid', $data['itemid']);


// Create form list.
$testFormList = new CFormList('itemFormList');

$triggerTable = (new CTableInfo())
   ->setHeader([
      _('Trigger description'),
      _('Trigger expression')
   ]);

foreach ($data['triggers'] as $trg) {
   $triggerTable->addRow([
      $trg['description'],
      $trg['expression']
   ]);
}

$testFormList->addRow(
   ($triggerTable)
);

$testFormList->addRow(
   (new CLabel(_('Test value'), 'test_value'))->setAsteriskMark(),
   (new CTextArea('test_value'))
      ->setWidth(ZBX_TEXTAREA_STANDARD_WIDTH)
      ->setAriaRequired()
      ->setAttribute('autofocus', 'autofocus')
);

$testFormList->addRow(
   (new CLabel(_('Test delay'), 'test_delay'))->setAsteriskMark(),
   (new CTextBox('test_delay'))
      ->setWidth(ZBX_TEXTAREA_STANDARD_WIDTH)
      ->setAriaRequired()
);

// Append tabs to form.
$itemTab = (new CTabView())
   ->addTab('itemTab', 'Item test', $testFormList);

$itemTab->setFooter(makeFormFooter(
   new CSubmit('start', _('Start')),
   [new CButtonCancel(url_param('hostid'))]
));

$form->addItem($itemTab);
$widget->addItem($form);

return $widget;