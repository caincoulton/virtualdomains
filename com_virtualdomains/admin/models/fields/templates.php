<?php
/**
 * @version		$Id: templates.php 12633 2009-08-13 14:28:31Z erdsiger $
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Framework
 * @subpackage	Form
 * @since		1.6
 */
class JFormFieldTemplates extends JFormFieldList {

	/**
	 * The field type.
	 *
	 * @var		string
	 */
	public $type = 'Templates';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return  array  An array of HTMLHelper options.
	 */
	protected function getOptions()
	{
		$db    = Factory::getDBO();
		$query = $db->getQuery(true);
		$query->select('name, element');
		$query->from('#__extensions')
				->where('type = "template"')
				->where('enabled = TRUE')
				->order('`name` ASC');

		$db->setQuery((string) $query);
		$templates = $db->loadObjectList();
		$options  = array(
			HTMLHelper::_('select.option', '', '- Select Template -')
		);

		if ($templates)
		{
			foreach ($templates as $template)
			{
				$options[] = HTMLHelper::_('select.option', $template->element, $template->name);
			}
		}

		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
