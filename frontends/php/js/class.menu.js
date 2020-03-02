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


const MENU_EXPAND_SELECTED_DELAY = 5000;

const MENU_EVENT_BLUR            = 'blur';
const MENU_EVENT_EXPAND          = 'expand';
const MENU_EVENT_FOCUS           = 'focus';

class CMenu extends CBaseComponent {

	constructor(node) {
		super(node);

		this.init();
		this.registerEvents();
	}

	init() {
		this._items = [];
		for (const el of this._node.childNodes) {
			this._items.push(new CMenuItem(el));
		}

		if (this.hasClass('submenu')) {
			this._node.style.maxHeight = this._node.scrollHeight + 'px';
		}
	}

	collapseAll(excluded_item = null) {
		for (const item of this._items) {
			if (item !== excluded_item && item.hasSubmenu()) {
				item.collapseSubmenu();
			}
		}

		return this;
	}

	expandSelected() {
		for (const item of this._items) {
			if (item.hasSubmenu()) {
				if (item.isSelected()) {
					item.expandSubmenu();
				}
				else {
					item.collapseSubmenu();
				}
			}
		}

		return this;
	}

	focusSelected() {
		for (const item of this._items) {
			if (item.hasSubmenu()) {
				item.getSubmenu().focusSelected();
			}
			else if (item.isSelected()) {
				item.focus();
			}
		}

		return this;
	}

	/**
	 * Register all DOM events.
	 */
	registerEvents() {
		this._events = {

			focus: (e) => {
				if (!this._node.contains(e.relatedTarget)) {
					this.trigger((e.type === 'focusin') ? MENU_EVENT_FOCUS : MENU_EVENT_BLUR);
				}
			},

			expand: (e) => {
				this.collapseAll(e.detail.targetObj);
				this.trigger(MENU_EVENT_EXPAND, {menu_item: e.detail.targetObj});
			}
		};

		this.on('focusin focusout', this._events.focus);

		for (const item of this._items) {
			if (item.hasSubmenu()) {
				item.on(MENUITEM_EVENT_EXPAND, this._events.expand);
			}
		}
	}
}
