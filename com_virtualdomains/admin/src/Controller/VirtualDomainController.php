<?php

/**
* @version		$Id: virtualdomain.php 136 2013-09-24 14:49:14Z michel $ $Revision$ $DAte$ $Author$ $
* @package		Virtualdomains
* @subpackage 	Controllers
* @copyright	Copyright (C) 2014, Michael Liebler. All rights reserved.
* @license #http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/

namespace Janguo\Component\VirtualDomains\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\FormController;

/**
 * VirtualdomainsVirtualdomain Controller
 *
 * @package    Virtualdomains
 * @subpackage Controllers
 */
class VirtualDomainController extends FormController
{
		/**
	 * Constructor.
	 *
	 * @param   array  $config	An optional associative array of configuration settings.
	 *
	 * @return  VirtualDomainController
	 * @see     JController
	 */
	public function __construct($config = array())
	{
        //$this->name = 'VirtualDomain';
        $this->model_prefix = 'VirtualDomainsModel';
        $this->view_item = 'VirtualDomain';
		parent::__construct($config);
	}

   	/**
	 * Proxy for getModel.
	 *
	 * @param   string	$name	The name of the model.
	 * @param   string	$prefix	The prefix for the PHP class name.
	 *
	 * @return  JModel
	 * @since   1.6
	 */
	public function getModel($name = 'VirtualDomain', $prefix = 'VirtualDomainsModel', $config = array('ignore_request' => true))
    {
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	/**
	 *
	 */
	public function save($key = null, $urlVar = null) {
		$data  = $this->input->post->get('jform', array(), 'array');

		$model = $this->getModel();
		$data = $model->beforeSave($data);
		$this->input->post->set('jform', $data);
		// we have to add data to the request
		if (class_exists('JRequest')) {
			JRequest::setVar('jform', $data, 'post');
		}
		return parent::save($key, $urlVar);

	}
}
