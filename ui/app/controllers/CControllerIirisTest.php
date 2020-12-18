<?php
/*
** Zabbix
** Copyright (C) 2001-2020 Zabbix SIA
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

class CControllerIirisTest extends CController {

	protected function init() {
		$this->disableSIDValidation();
	}

	protected function checkInput() {
		$fields = [
			'sort' =>				'in item,host',
			'sortorder' =>			'in '.ZBX_SORT_DOWN.','.ZBX_SORT_UP
		];

		$ret = $this->validateInput($fields);

		if (!$ret) {
			$this->setResponse(new CControllerResponseFatal());
		}

		return $ret;
	}

	protected function checkPermissions() {
		return ($this->getUserType() == USER_TYPE_SUPER_ADMIN);
	}
	
	protected function doAction() {
		$sql = 'SELECT h.host AS hostname, h.hostid AS hostid, i.name AS itemname, i.itemid AS itemid, t.test_value as test_value FROM item_testing t JOIN items i ON i.itemid = t.itemid JOIN hosts h ON h.hostid = t.hostid;';
		$data['items'] = array();
		$result = DBselect($sql);
		while ($row = DBfetch($result)) {
			$data['items'][] = $row;
		}

		$data['sort'] = "item";
		$data['sortorder'] = "ASC";

	/*
		$result_ids = DBselect('SELECT itemid FROM item_testing;');
		$itemIds = DBfetchColumn($result_ids, 'itemid');
		$itemTriggerIds = API::Item()->get([
			'output' => ['itemid'],
			'selectTriggers' => ['triggerid'],
			'itemids' => $itemIds
		]);
		// echo '<script>console.log('. json_encode( $itemTriggerIds ) .')</script>';


		$data['itemTriggers'] = API::Trigger()->get([
			'triggerids' => $itemTriggerIds,
			'output' => ['triggerid', 'description', 'expression', 'recovery_mode', 'recovery_expression', 'priority',
				'status', 'state', 'error', 'templateid', 'flags'
			],
			'selectHosts' => ['hostid', 'name', 'host'],
			'preservekeys' => true
		]);
	*/

		$response = new CControllerResponseData($data);
		$response->setTitle(_('Item Testing'));
		$this->setResponse($response);
	}
	
}
