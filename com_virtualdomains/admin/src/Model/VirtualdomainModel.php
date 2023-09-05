<?php

/**
* @version		$Id:virtualdomain.php  1 2014-02-26 11:56:55Z mliebler $
* @package		Virtualdomains
* @subpackage 	Models
* @copyright	Copyright (C) 2014, Michael Liebler. All rights reserved.
* @license #http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/

namespace Janguo\Component\VirtualDomains\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\MVC\View\GenericDataException;

class VirtualDomainModel extends AdminModel { 
	/**
	 * The URL option for the component.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $option = 'com_virtualdomains';

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed    A JForm object on success, false on failure
	 *
	 * @since   0.0.1
	 */
	public function getForm($data = array(), $loadData = true)
	{
		FormHelper::addRulePath(JPATH_COMPONENT_ADMINISTRATOR.'/src/Rule');

		// Get the form.
		$form = $this->loadForm(
			'com_virtualdomains.virtualdomain',
			'virtualdomain',
			array(
				'control' => 'jform',
				'load_data' => $loadData
			)
		);

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 */
	protected function loadFormData()
	{
		
		// Check the session for previously entered form data.
		$app  = Factory::getApplication();
		$data = $app->getUserState('com_virtualdomains.edit.virtualdomain.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		
		}
		
		if(version_compare(JVERSION,'4','<')){
			$this->preprocessData('com_virtualdomains.virtualdomain', $data);
		}
		

		return $data;
	}
	
	public function beforeSave($data)
	{
		$db = $this->getDatabase();

		if(isset($data['template_style_id'])) {
			$query = $db->getQuery(true);
			$query->select('template')
				->from('#__template_styles')
				->where('id = ' . (int) $data['template_style_id']);
			$db->setQuery($query);
			$data['template'] = $db->loadResult();
		}

		$query = $db->getQuery(true);
		$query->select('id')
			->from('#__viewlevels')
			->where('title = ' . $db->Quote($data['domain']) . ' OR id = ' . (int) $data['viewlevel']);
		$db->setQuery($query);
		$viewlevel = $db->loadResult();
		
		//Add or update viewlevel
		if($viewlevel) {
			$query = "UPDATE #__viewlevels SET title = ".$db->Quote($data['domain'])." WHERE id = ". (int) $viewlevel ;
			$db->setQuery($query);
			$db->execute();
			$data['viewlevel'] = $viewlevel;
		} else {
			$query = "INSERT INTO #__viewlevels SET rules = ". $db->Quote('[]').",  title = ".$db->Quote($data['domain']);
			$db->setQuery($query);
			$db->execute();
			$data['viewlevel'] = $db->insertid();
		}

		return $data;
	}
	
	/**
	 * Method to delete assigned viewlevels
	 * @param int/array $cid
	 * @return boolean
	 */
	public function preDelete($cid) {
		$db = $this->getDatabase();

		if(is_array($cid)) {
			foreach($cid as $id) {
				$row = $this->getTable();
				$row->load($id);
				var_dump($row);
				if($row->viewlevel) {
					echo 'DELETE FROM #__viewlevels WHERE id = '.(int) $row->viewlevel.'<br />';
					$db->setQuery('DELETE FROM #__viewlevels WHERE id = '.(int) $row->viewlevel);
					$db->execute();
				}
			}
		} else {
			$row = $this->getTable();
			$row->load($cid);
			if($row->viewlevel) {
				echo 'DELETE FROM #__viewlevels WHERE id = '.(int) $row->viewlevel.'<br />';
				$db->setQuery('DELETE FROM #__viewlevels WHERE id = '.(int) $row->viewlevel);
				$db->execute();
			}
		}

		return true;	
	}
	
	/**
	 * Override parent method validate
	 * @param Form $form
	 * @param array $data
	 * @param string $group
	 * @return array
	 */
	public function validate($form, $data, $group = null) {
		$origparams = isset($data['params']) ? $data['params'] : array();
		$data = parent::validate($form, $data, $group);
		$data['params'] = isset($data['params']) ? array_merge($data['params'], $origparams) : $origparams;
		return $data;
	}	
	
	/**
	 * Method to set a template style as home.
	 *
	 * @param	int		The primary key ID for the style.
	 *
	 * @return	boolean	True if successful.
	 * @throws	Exception
	 */
	public function setDefault($cids, $value = 1)
	{
		// Initialise variables.
		$db		= $this->getDatabase();		
		$cids = (array) $cids;
		$id = (int) $cids[0];

		// Reset the home fields for the client_id.
		$db->setQuery(
				'UPDATE #__virtualdomain' .
				' SET home= ' .$db->Quote('0').
				' WHERE home = '.$db->Quote('1')
		);
	
		if (!$db->execute()) {
			throw new GenericDataException($db->getErrorMsg(), 500);
		}
	
		// Set the new home style.
		$db->setQuery(
				'UPDATE  #__virtualdomain' .
				' SET home ='. (int) $value.
				' WHERE id = '.(int) $id
		);
	
		if (!$db->execute()) {
			throw new GenericDataException($db->getErrorMsg(), 500);
		}
	
		return true;
	}
	

	/**
	 * @notice Zurück zu Revision 11
	 *
	 * VirtualdomainsModelVirtualdomain::getParamFields()
	 *
	 * @return
	 */
	public function getParamFields()
	{
		$item = $this->getItem();

		$db = $this->getDatabase();
		$query = $db->getQuery(true);
		$query->select('name, "" AS value')
			->from('#__virtualdomain_params');

		$db->setQuery($query);
		$result = $db->loadObjectList();
		$params = (array)  $item->params;

		if (count($params )) {
			for ($i = 0; $i < count($result); $i++) {
				foreach ($params as $key=>$value) {
					if ($result[$i]->name == $key) {
						$result[$i]->value = $value;
					}
				}
			}
		}
		
		return $result;
	}
}
