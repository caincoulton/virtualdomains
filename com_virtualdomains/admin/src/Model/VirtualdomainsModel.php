<?php

namespace Janguo\Component\VirtualDomains\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;

class VirtualDomainsModel extends ListModel
{
	/**
     * Method to auto-populate the model state.
     *
     * This method should only be called once per instantiation and is designed
     * to be called on the first call to the getState() method unless the model
     * configuration flag to ignore the request is set.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param   string  $ordering   An optional ordering field.
     * @param   string  $direction  An optional direction (asc|desc).
     *
     * @return  void
     *
     * @since   1.6
     */
	protected function populateState($ordering = null, $direction = null)
	{
		parent::populateState();

		$app = Factory::getApplication();
		$id = $app->input->get('id', 0, 'int');
		$this->setState('virtualdomainlist.id', $id);			
		
		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$value = $this->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'));
		$limit = $value;
		$this->setState('list.limit', $limit);
		
		$value = $this->getUserStateFromRequest($this->context.'.limitstart', 'limitstart', 0);
		$limitstart = ($limit != 0 ? (floor($value / $limit) * $limit) : 0);
		$this->setState('list.start', $limitstart);
		
		$value = $this->getUserStateFromRequest($this->context.'.ordercol', 'filter_order', $ordering);
		$this->setState('list.ordering', $value);			
		
		$value = $this->getUserStateFromRequest($this->context.'.orderdirn', 'filter_order_Dir', $direction);
		$this->setState('list.direction', $value);

		$state = $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'string');
		$this->setState('filter.state', $state);		
	}
    
	/**
     * Method to get a store id based on the model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param   string  $id  An identifier string to generate the store id.
     *
     * @return  string  A store id.
     *
     * @since   1.6
     */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':'.$this->getState('virtualdomainlist.id');
		$id .= ':' . $this->getState('filter.state');
		return parent::getStoreId($id);
	}	
	
	/**
     * Method to get a DatabaseQuery object for retrieving the data set from a database.
     *
     * @return  DatabaseQuery  A DatabaseQuery object to retrieve the data set.
     *
     * @since   1.6
     */
	protected function getListQuery()
	{
		
		$db		= $this->getDatabase();
		$query	= $db->getQuery(true);		
		$query->select('a.*');
		$query->from('#__virtualdomain as a');
	
		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->quote('%' . $db->escape($search, true) . '%');
				$query->where('(a.domain LIKE ' . $search . '  OR a.template LIKE ' . $search . ' )');
			}
		}
				
		$published = $this->getState('filter.state');
		
		if (is_numeric($published))
		{
			$query->where('a.published = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(a.published IN (0, 1))');
		}
		
		// Add the list ordering clause.
		$orderCol = $this->getState('list.ordering', 'ordering');
		$orderDirn = $this->getState('list.direction', 'ASC');
		if(empty($orderCol)) $orderCol = 'ordering';
		if(empty($orderDirn)) $orderDirn = 'DESC'; 		
		$query->order($db->escape($orderCol . ' ' . $orderDirn));
							
		return $query;
	}	
}