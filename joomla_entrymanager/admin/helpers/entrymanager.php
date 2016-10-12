<?php

/**
 * @version		$Id: entrymanager.php 74 2010-12-01 22:04:52Z chdemko $
 * @package		Joomla16.Tutorials
 * @subpackage	Components
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @author		Christophe Demko
 * @link		http://joomlacode.org/gf/project/entrymanager_1_6/
 * @license		License GNU General Public License version 2 or later
 */

// No direct access to this file
defined('_JEXEC') or die;

/**
 * EntryManager component helper.
 */
abstract class EntryManagerHelper
{
	/**
	 * Configure the Linkbar.
	 */
	public static function addSubmenu($submenu) 
	{
		JSubMenuHelper::addEntry(JText::_('COM_ENTRYMANAGER_SUBMENU_MESSAGES'), 'index.php?option=com_entrymanager', $submenu == 'messages');
		JSubMenuHelper::addEntry(JText::_('COM_ENTRYMANAGER_SUBMENU_CATEGORIES'), 'index.php?option=com_categories&view=categories&extension=com_entrymanager', $submenu == 'categories');
		// set some global property
		$document = JFactory::getDocument();
		$document->addStyleDeclaration('.icon-48-entrymanager {background-image: url(../media/com_entrymanager/images/tux-48x48.png);}');
		if ($submenu == 'categories') 
		{
			$document->setTitle(JText::_('COM_ENTRYMANAGER_ADMINISTRATION_CATEGORIES'));
		}
	}
	/**
	 * Get the actions
	 */
	public static function getActions($messageId = 0)
	{
		$user	= JFactory::getUser();
		$result	= new JObject;

		if (empty($messageId)) {
			$assetName = 'com_entrymanager';
		}
		else {
			$assetName = 'com_entrymanager.message.'.(int) $messageId;
		}

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.delete'
		);

		foreach ($actions as $action) {
			$result->set($action,	$user->authorise($action, $assetName));
		}

		return $result;
	}
}
