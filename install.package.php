<?php
/**
 * This is the special installer addon created by Andrew Eddie and the team of jXtended.
 * We thank for this cool idea of extending the installation process easily
 * @copyright 2005-2008 New Life in IT Pty Ltd.  All rights reserved.
 */

/**
 * @version $Id$
 * @package    virtualdomains
 * @subpackage Base
 * @author     EasyJoomla {@link http://www.easy-joomla.org Easy-Joomla.org}
 * @author     Michael Liebler {@link http://www.janguo.de}
 * @author     Created on 14-Aug-09
 */
 
//--No direct access
defined('_JEXEC') or die('=;)');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

$status = new JObject();
$status->modules = array();
$status->plugins = array();

//no frontend menu item for VD
$db	= & Factory::getDBO();
$query = "UPDATE #__components set link='' WHERE `option` = 'com_virtualdomains'";
$db->setQuery($query);
$db->query();

//check if table virtualdomain already exists 

$is_update = in_array($db->getPrefix().'virtualdomain', $db->getTableList());

//prepare the new json parameters 
if ($is_update ) {

	$found_params = array();
	$query = "SELECT COUNT( * ) FROM #__virtualdomain WHERE SUBSTRING( `params` , 1, 1 ) = '{'";
	$db->setQuery($query);
	$newParams=$db->loadResult();
	if (!$newParams) {
		$query = "SELECT * FROM #__virtualdomain";
		$db->setQuery($query);
		$rows=$db->loadObjectList();
		for ($i=0;$i<count($rows);$i++) {
			$obj = new JParameter($rows[$i]->params);
			$params = json_encode($obj->toArray());
			$found_params = array_merge($found_params, $obj->toArray());
			$query = "UPDATE #__virtualdomain SET `params`=".$db->Quote($params)." WHERE id = ".(int) $rows[$i]->id;
			$db->setQuery($query);
			$db->query();
		} 
	}	
}

/***********************************************************************************************
* ---------------------------------------------------------------------------------------------
* PLUGIN INSTALLATION SECTION
* ---------------------------------------------------------------------------------------------
***********************************************************************************************/

$plugins = &$this->manifest->getElementByPath('plugins');
if (is_a($plugins, 'JSimpleXMLElement') && count($plugins->children())) 
	foreach ($plugins->children() as $plugin) {
		$pname		= $plugin->attributes('plugin');
		$pgroup		= $plugin->attributes('group');
		$porder		= $plugin->attributes('order');

		//--Set the installation path
		if ( ! empty($pname) && ! empty($pgroup)) {
			$this->parent->setPath('extension_root', JPATH_ROOT.'/plugins/'.$pgroup);
		} else {
			$this->parent->abort(Text::_('Plugin').' '.Text::_('Install').': '.Text::_('No plugin file specified'));
			return false;
		}

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Filesystem Processing Section
		 * ---------------------------------------------------------------------------------------------
		 */

		//--If the plugin directory does not exist, lets create it
		$created = false;
		if ( ! file_exists($this->parent->getPath('extension_root'))) {
			if ( ! $created = JFolder::create($this->parent->getPath('extension_root'))) {
				$this->parent->abort(Text::_('Plugin').' '.Text::_('Install').': '.Text::_('Failed to create directory').': "'.$this->parent->getPath('extension_root').'"');
				return false;
			}
		}

		/*
		* If we created the plugin directory and will want to remove it if we
		* have to roll back the installation, lets add it to the installation
		* step stack
		*/
		if ($created) {
			$this->parent->pushStep(array ('type' => 'folder', 'path' => $this->parent->getPath('extension_root')));
		}

		//--Copy all necessary files
		$element = &$plugin->getElementByPath('files');
		if ($this->parent->parseFiles($element, -1) === false) {
			//--Install failed, roll back changes
			$this->parent->abort();
			return false;
		}

		//--Copy all necessary files
		$element = &$plugin->getElementByPath('languages');
		if ($this->parent->parseLanguages($element, 1) === false) {
			//--Install failed, roll back changes
			$this->parent->abort();
			return false;
		}

		//--Copy media files
		$element = &$plugin->getElementByPath('media');
		if ($this->parent->parseMedia($element, 1) === false) {
			//--Install failed, roll back changes
			$this->parent->abort();
			return false;
		}

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Database Processing Section
		 * ---------------------------------------------------------------------------------------------
		 */
		$db = &Factory::getDBO();

		//--Check to see if a plugin by the same name is already installed
		$query = 'SELECT `id`' .
		' FROM `#__plugins`' .
		' WHERE folder = '.$db->Quote($pgroup) .
		' AND element = '.$db->Quote($pname);
		$db->setQuery($query);
		if ( ! $db->Query()) {
			//--Install failed, roll back changes
			$this->parent->abort(Text::_('Plugin').' '.Text::_('Install').': '.$db->stderr(true));
			return false;
		}
		$id = $db->loadResult();

		//--Was there a plugin already installed with the same name?
		if ($id) {

			if ( ! $this->parent->getOverwrite()) {
				//--Install failed, roll back changes
				$this->parent->abort(Text::_('Plugin').' '.Text::_('Install').': '.Text::_('Plugin').' "'.$pname.'" '.Text::_('already exists!'));
				return false;
			}

		} else {
			$row =& JTable::getInstance('plugin');
			$row->name = Text::_(ucfirst($pgroup)).' - '.Text::_(ucfirst($pname));
			$row->ordering = $porder;
			$row->folder = $pgroup;
			$row->iscore = 0;
			$row->access = 0;
			$row->client_id = 0;
			$row->element = $pname;
			$row->published = 1;
			$row->params = '';

			if ( ! $row->store()) {
				//--Install failed, roll back changes
				$this->parent->abort(Text::_('Plugin').' '.Text::_('Install').': '.$db->stderr(true));
				return false;
			}
		}

		$status->plugins[] = array('name'=>$pname,'group'=>$pgroup);
	}//foreach


/***********************************************************************************************
* ---------------------------------------------------------------------------------------------
* SETUP DEFAULTS
* ---------------------------------------------------------------------------------------------
***********************************************************************************************/

/***********************************************************************************************
* ---------------------------------------------------------------------------------------------
* Execute specific system steps to ensure a consistent installtion
* ---------------------------------------------------------------------------------------------
***********************************************************************************************/


/***********************************************************************************************
* ---------------------------------------------------------------------------------------------
* OUTPUT TO SCREEN
* ---------------------------------------------------------------------------------------------
***********************************************************************************************/
$rows = 0;
?>

<h2>Virtual Domains Installation</h2>
<?php
	if (isset($found_params) && count($found_params)) { 
		?>
		<span style="color:red;font-size:1.4em">Attention! Found custom parameters in virtualdomains table.<br /> 
		Please go to <a href="index.php?option=com_virtualdomains&view=params">Virtualdomains-&gt;Params</a> and add the following parameters manually:</span>
		<ul>
		<?php
		foreach ($found_params as $key => $value) {
			echo '<li>'.$key.'</li>';
		}
		?>
		</ul>
		<span style="color:red">
			You can ignore this notice, if you don't use the parameters above.
		</span>
		<?php
	}
?>		
<table class="adminlist">
	<thead>
		<tr>
			<th class="title" colspan="2"><?php echo Text::_('Extension'); ?></th>
			<th width="30%"><?php echo Text::_('Status'); ?></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="3"></td>
		</tr>
	</tfoot>
	<tbody>
		<tr class="row0">
			<td class="key" colspan="2"><?php echo 'virtualdomains '.Text::_('Component'); ?></td>
			<td><img src="images/publish_g.png" alt="OK" /> <strong><?php echo Text::_('Installed'); ?></strong></td>
		</tr>
<?php if (count($status->modules)) : ?>
		<tr>
			<th><?php echo Text::_('Module'); ?></th>
			<th><?php echo Text::_('Client'); ?></th>
			<th></th>
		</tr>
	<?php foreach ($status->modules as $module) : ?>
		<tr class="row<?php echo (++ $rows % 2); ?>">
			<td class="key"><?php echo $module['name']; ?></td>
			<td class="key"><?php echo ucfirst($module['client']); ?></td>
			<td><img src="images/publish_g.png" alt="OK" /> <strong><?php echo Text::_('Installed'); ?></strong></td>
		</tr>
	<?php endforeach;
	endif;
if (count($status->plugins)) : ?>
		<tr>
			<th><?php echo Text::_('Plugin'); ?></th>
			<th><?php echo Text::_('Group'); ?></th>
			<th></th>
		</tr>
	<?php foreach ($status->plugins as $plugin) : ?>
		<tr class="row<?php echo (++ $rows % 2); ?>">
			<td class="key"><?php echo ucfirst($plugin['name']); ?></td>
			<td class="key"><?php echo ucfirst($plugin['group']); ?></td>
			<td><img src="images/publish_g.png" alt="OK" /> <strong><?php echo Text::_('Installed'); ?></strong></td>
		</tr>
	<?php endforeach;
endif; ?>
	</tbody>
</table>
