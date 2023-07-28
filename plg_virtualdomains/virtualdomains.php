<?php

/**
 * @version		$Id: virtualdomains.php 13 2013-03-30 00:14:57Z michel $
 * @package		Virtualdomains
 * @subpackage	plug_virtualdomains
 * @copyright	Copyright (C) 2008 - 2009 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Virtualdomains is free software. This version may have been modified pursuant
 * @author     	Michael Liebler {@link http://www.janguo.de}
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\String\StringHelper;
use Joomla\CMS\Router\Router;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Associations;
use Janguo\Component\VirtualDomains\Site\Library\VdUser;
use Janguo\Component\VirtualDomains\Site\Library\VdAccess;
use Janguo\Component\VirtualDomains\Site\Library\VdLanguage;
use Janguo\Component\VirtualDomains\Site\Library\VdMenuFilter;


class PlgSystemVirtualdomains extends CMSPlugin
{

	private $_db = null;
	private $_request = array();
	private $_hostparams = null;
	private $_curhost = array();
	private $input = null;

	/**
	 * Constructor
	 * @access	protected
	 * @param	object	$subject The object to observe
	 * @param 	array   $config  An array that holds the plugin configuration
	 * @since	1.0
	 */
	public function __construct( &$subject, $config )
	{
		$this->_db = Factory::getDBO();
		$this->input = Factory::getApplication()->input;
		parent::__construct( $subject, $config );
	}

	/**
	 *  onAfterInitialise
	 */
	public function onAfterInitialise()
	{
		if(!Folder::exists(JPATH_ADMINISTRATOR.'/components/com_virtualdomains')) {
			return false;
		}

		$app 	= Factory::getApplication();
		$db 	= Factory::getDBO();
		$user 	= Factory::getUser();
		$conf 	= ComponentHelper::getParams('com_virtualdomains');
		$uri = Uri::getInstance();

		// we just have to give full access for all users in backend - nothing else has to be done
		if ( $app->isClient('administrator') )
		{

			if(!$conf->get('denyadminaccess', 0)) {
				//Full access for all users in backend
				$this->fullAccess();
			}
			return; // Dont do anymore in backend
		}

		// this is done when the frontend is called by the ajax check function from VD backend
		if($this->input->get('option') == 'com_virtualdomains') {
			$this->hostCheck();
		}

		// strip the www from hostname
		$this->_curhost = str_replace( 'www.', '', $uri->getHost() );

		// if the core API is being called, no further processing needs to be done
		if(str_contains($uri, $this->_curhost . '/api/index.php')) {
			return;
		}

		// Cachebuster - special param for apps registeredurlparams
		// prevents getting wrong things from cache store
		if (!empty($app->registeredurlparams))
		{
			$registeredurlparams = $app->registeredurlparams;
		}
		else
		{
			$registeredurlparams = new stdClass;
		}

		$registeredurlparams->vdcachbuster = 'WORD';
		$app->registeredurlparams = $registeredurlparams;
		$this->input->set('vdcachbuster', $this->_curhost);

		// Remove first segment of SEF url which is the site root menu item
		if($conf->get('stripmenuroot')){
			$router = $app->getRouter();

			// If URL uses top level segment, then redirect to new URL
			$this->redirectToRemoveTopLevelAlias($uri);
			
			// Remove top level menu item from SEF url
			$router->attachBuildRule(function(&$router, &$uri) {
				$homeAlias = $this->getBuildSiteAlias($uri);
				$uri->setPath(preg_replace('/(' . $homeAlias . '\/)/', '', $uri->getPath(), 1));
			}, Router::PROCESS_AFTER);

			// Put top level menu item back in for internal processing
			$router->attachParseRule(function(&$router, &$uri) {
				// If path is empty, processing isn't required (presumably it's all been handled, and/or it's the homepage)
				if(strlen($uri->getPath()) > 0) {
					$homeAlias = $this->getParseSiteAlias($uri);
					$url = $uri->toString();
					$url = str_replace($uri->getHost(), $uri->getHost() . '/' . $homeAlias . '/', $url);
					$uri->parse($url);

					// Remove leading slash from path
					$uri->setPath(implode('/', array_filter(explode('/', $uri->getPath()))));
				}
			}, Router::PROCESS_BEFORE);
		}

		// get current domains settings
		$currentDomain = $this->getCurrentDomain();

		// let joomla do its work, if the domain is not managed by VD
		if ($currentDomain === null) return;

		// set the vd id to users session
		$user->set('virtualdomain_id', $currentDomain->id);
		// add viewlevel(s) for the current domain to the user object
		$vdUser = new VdUser($user->get('id'));
		// there may be viewlevels inherited from other domains
		$viewlevels =  (array) $currentDomain->params->get('access');
		// add current domains viewlevel
		$viewlevels[] = $currentDomain ->viewlevel;
		// override method addAuthorisedViewLevels
		VdAccess::addAuthorisedViewLevels($user->get('id'), $viewlevels);
		// add viewlevels to the user object
		$vdUser->addAuthLevel($viewlevels);

		// Get the params
		$this->_hostparams = $currentDomain ->params;

		// legacy global var for the developers needs
		if(isset($currentDomain->Team_ID) && $currentDomain->Team_ID) $GLOBALS['Team_ID'] = $currentDomain->Team_ID;

		// override the original config with domain specific settings
		$this->setConfig();

		// Set the route, if necessary
		if ( !$this->reRoute( $currentDomain , $uri ) )
		{
			$this->setActions();
		}

	    // check if admin denied access to some components
		$this->checkComponent();

		// set default languaeg if required
		$this->setLangVars();

		//set the template
		if ( $currentDomain->template )
		{

			if( $currentDomain->template_style_id ) {
				$this->addRequest( 'templateStyle' , $currentDomain->template_style_id);
			} else {
			    $this->addRequest('template' , $currentDomain->template );
			}
		}

		// filter menues if required
		$this->filterMenus($currentDomain->menuid, $currentDomain->template, $currentDomain->template_style_id);

		//set all requests
		$this->setRequests();
	}

	/**
	 *
	 * Method to add or set a var to the request
	 */
	private function addRequest( $var, $value )
	{
		$this->_request[$var] = $value;
	}

	/**
	 * Check if a component is denied for current domain
	 */
	private function checkComponent() {

		// get denied components
		$componentsDenied = (array) $this->_hostparams->get('components');
		if(!count($componentsDenied)) return;

		$input = Factory::getApplication()->input;
		$option = false;

		//try to get current component from input
		if (!($option = $input->get('option'))) {

			//try to get current component from mene
			$menu = Factory::getApplication()->getMenu('site',array());
			$active = $menu->getActive();
			if ($active && $active->type == 'component') {
				$option = $active->component;
			}
		}

		// check if component is denied
		if($option && in_array($option, $componentsDenied)) {
			Factory::getLanguage()->load('lib_joomla');
			if (class_exists('Exception')) {
				throw new Exception(Text::_('JLIB_APPLICATION_ERROR_COMPONENT_NOT_FOUND'), 404);
			} else {
				JError::raiseError(404, Text::_('JLIB_APPLICATION_ERROR_COMPONENT_NOT_FOUND'));
			}
		}
	}

	/**
	 *
	 * Method to check, if current menu item is the domains home
	 * @param object $curDomain
	 */
	private function checkHome(&$curDomain) {

	    $app = Factory::getApplication();

	    $menu = $app->getMenu('site', array());
		$menuItem = $menu->getItem(( int ) $curDomain->menuid );

		$router = $app->getRouter();
		$uri = Uri::getInstance();
		$mode_sef = Factory::getConfig()->get('sef');

		$origHome = $this->getDefaultmenu();
		$curDomain->isHome = false;
		$clonedUri = clone ( $uri );
		$curDomain->query_link = $router->parse( $clonedUri);
		$curDomain->activeItemId  = ( int )$curDomain->query_link['Itemid'];

		//do nothing, if we are not on frontpage
		if (  ( int )$curDomain->query_link['Itemid'] === ( int )$origHome->id  )
		{
			$curDomain->isHome = true;
		}

		// may be we are routed to a component by a form
		$option = $app->input->get('option');
		if($option && ($menuItem->component != $option )) {
			$curDomain->isHome = false;
		}

		//its clear: we are not at home
		if(!$curDomain->isHome) return;

		if($mode_sef) {
			$route	= $uri->getPath();
			$route_lowercase = StringHelper::strtolower($route);

			// Handle an empty URL (special case)
			if (empty($route)) {
				$curDomain->isHome = false;
			} elseif($route_lowercase === '/') {
				$curDomain->isHome = true;
			} else {
				$items = array_reverse($menu->getMenu());

				$found = false;

				foreach ($items as $item) {
					$length = strlen($item->route); //get the length of the route

					if ($length > 0 && StringHelper::strpos($route_lowercase.'/', $item->route.'/') === 0 && $item->type != 'menulink') {
						$route = substr($route, $length);
						if ($route) {
							$route = substr($route, 1);
						}
						$found = true;
						break;
					}
				}

				//this is the case, if active menu item has changed before
				if(!$found) {
					$curDomain->isHome = false;
					return;
				}
			}
		}
	}

	/**
	 * Method to hide/show Menu items and translate home item
	 *
	 * @param int $default - Current domains home menu item
	 */
	private function filterMenus($default, $template, $style)
	{
		$menu = new VdMenuFilter();
		$menu->filterMenues($this->_hostparams, $default);
	}

	/**
	 * Gives the user access on all domains - used for the backend
	 */
	private function fullAccess() {

		// Needed for searching articles on backend,
		// thanks to Javi
		$db = Factory::getDbo();
		$user = Factory::getUser();

		// get VD domains from db
		try {
			$db->setQuery("SELECT * FROM #__virtualdomain WHERE published > 0");
			$allDomains = $db->loadObjectList();
		} catch(Exception $e) {
			throw new Exception($e->getMessage(), 500);
			return false;
		}

		// current user
		$vdUser = new VdUser($user->get('id'));

		// assign all viewlevels to user session
		foreach($allDomains as $domain) {
			$viewlevels[] = $domain ->viewlevel;
			$vdUser->addAuthLevel($viewlevels);
		}
	}

	/**
	 * Method to get the uri base path
	 * we dont need the $path .= '/';
	 * joomla does this for us
	 */
	private function getBase()
	{
		$path = '';
		if ( strpos( php_sapi_name(), 'cgi' ) !== false && !empty( $_SERVER['REQUEST_URI'] ) )
		{
			//Apache CGI
			$path = rtrim( dirname( str_replace( array( '"', '<', '>', "'" ), '', $_SERVER["PHP_SELF"] ) ), '/\\' );
			# var_dump($path);
		} else
		{
			//Others
			$path = rtrim( dirname( $_SERVER['SCRIPT_NAME'] ), '/\\' );
		}
		/*	$path .= '/';*/
		return $path;
	}

	/**
	 * Retrieves menu root alias with respect to the current URL and domain
	 * 
	 * @param Uri
	 * 
	 * @return string
	 */
	private function getBuildSiteAlias(&$uri) {

		if(!empty($instance)) return $instance;

		$domainMenuItem = $this->getDomainMenuItem();
		$parentAlias = $this->getTopLevelAlias($domainMenuItem);
		$associationAliases = array();

		// Load parent aliases on an associations of home menu item
		// Check language is not generic signifying that a specific language is set
		if($domainMenuItem->language != '*') {
			$db = $this->_db;
			$query = $db->getQuery(true);
			$query->clear()
				->select('mparent.alias')
				->from('#__menu AS mparent')
				->join('INNER', '#__menu AS m ON mparent.id = m.parent_id')
				->join('INNER', '#__associations AS a ON a.id = m.id')
				->join('INNER', '#__associations AS a2 ON a.`key` = a2.`key`')
				->where('a2.id = ' . $domainMenuItem->id)
				->where('m.menutype != \'' . $domainMenuItem->menutype . '\'');
			$associationAliases = $db->setQuery($query)->loadColumn();
		}

		$topLevelAliases = array_merge($associationAliases, array($parentAlias));
		foreach($topLevelAliases as $alias) {
			if(strpos($uri->getPath(), $alias)) {
				return $alias;
			}
		}
	}

	/**
	 * Retrieves menu root alias with respect to the current URL and domain
	 * 
	 * @param Uri
	 * 
	 * @return string
	 */
	private function getParseSiteAlias(&$uri) {

		if(!empty($instance)) return $instance;

		$domainMenuItem = $this->getDomainMenuItem();
		$parentAlias = $this->getTopLevelAlias($domainMenuItem);

		// Load parent aliases on an associations of home menu item
		// Check language is not generic signifying that a specific language is set
		if($domainMenuItem->language == '*') {
			return $parentAlias;
		} else {
			$db = $this->_db;
			$query = $db->getQuery(true);
			$query->clear()
				->select('mparent.alias')
				->from('#__menu AS mparent')
				->join('INNER', '#__menu AS m ON mparent.id = m.parent_id')
				->join('INNER', '#__associations AS a ON a.id = m.id')
				->join('INNER', '#__associations AS a2 ON a.`key` = a2.`key`')
				->where('a2.id = ' . $domainMenuItem->id)
				->where('m.language = \'' . Factory::getLanguage()->getTag() . '\'');
			return $db->setQuery($query)->loadResult();
		}
	}

	/**
	 * 
	 */
	private function getDomainMenuItem() {
		$db = $this->_db;
		$query = $db->getQuery(true);

		// Get menutype from domain
		$query->select('m.*')
			->from($db->quoteName('#__menu') . ' AS m')
			->join('INNER', '#__virtualdomain AS vd ON m.id = vd.menuid')
			->where('domain = ' . $db->quote($this->_curhost));
		return $db->setQuery($query)->loadObject();
	}

	/**
	 * 
	 */
	private function getTopLevelAlias($domainMenuItem) {
		$db = $this->_db;
		$query = $db->getQuery(true);

		// Get parent alias for given domain
		$query->clear()
			->select('alias')
			->from('#__menu AS m')
			->where('id = ' . $domainMenuItem->parent_id);
		return $db->setQuery($query)->loadResult();
	}

	/**
	 * 
	 */
	private function redirectToRemoveTopLevelAlias(&$uri) {
		$db = $this->_db;

		// Get the current language tag.
		$languageTag = Factory::getLanguage()->getTag();

		// Get all languages.
		$languages = LanguageHelper::getLanguages('lang_code');

		// Get the SEF tag of current language.
		$sefTag = $languages[$languageTag]->sef;
		$currentUriPath = $uri->getPath();

		// If page file extension is not enabled, then use:  /^\/([a-z]{2}\/)?(.+)$/
		preg_match('/^\/([a-z]{2}\/)?(.+)(\.html)$/', $currentUriPath, $matches);

		// If no matches, then assume we don't have to redirect (possibly home page with no path)
		if(count($matches) == 0) {
			return;
		}

		$searchPath = $matches[2];

		// Get menu item path
		$query = $db->getQuery(true);
		$query->select('m.id')
			->from($db->quoteName('#__menu') . ' AS m')
			->where('path = ' . $db->quote($searchPath));
		$itemId = $db->setQuery($query)->loadResult();

		// If uri path exists on a menu item, then we need to redirect to remove the top level alias
		if(!is_null($itemId)) {
			$domainMenuItem = $this->getDomainMenuItem();
			$topLevelAlias = $this->getTopLevelAlias($domainMenuItem);
			Factory::getApplication()->redirect(str_replace('/' . $topLevelAlias, '', $currentUriPath));
		}
	}

	/**
	 * Get domains data from database
	 * @return object|NULL <mixed, NULL>
	 */
	private function getCurrentDomain() {

		static $instance;

		$vd = ComponentHelper::getComponent('com_virtualdomains');
		$app = Factory::getApplication();
		$db = $this->_db;

		if(!empty($instance)) return $instance;

		$db->setQuery(
			"SELECT * FROM #__virtualdomain
			WHERE `domain` = ".$db->Quote($this->_curhost ). " AND published > 0"
		);

		try {
            $curDomain = $db->loadObject();
		} catch(Exception $e) {
			$app->enqueueMessage(Text::_($e->getMessage()), 'error');
			return null;
		}

		if($curDomain === null) {
			return null;
		}

		$curDomain->params = new CMSObject(json_decode($curDomain->params));

		//Set the global override styles settings, if not configured
		if($curDomain->params->get('override') === '') $curDomain->params->set('override', $vd->params->get('override'));

		$uri = Uri::getInstance();

		// Standard Domain uses Joomla settings
		if($curDomain->home == 1) {
			$curDomain->template = null;
			$curDomain->menuid = null;
		}

		$this->checkHome($curDomain);

		//override style?
		switch($curDomain->params->get('override')) {
			case '2': // VD controls template, unless manually set on menu item

				// Try and find correct menuItem id from path
				preg_match('/\/(.*)\.html/', $uri->getPath(), $matches);
				$path = $matches[1];
				$db->setQuery("SELECT * FROM #__menu WHERE path = '$path'");
				$menuItemId = $db->loadResult();

				$menuItem = $app->getMenu()->getItem($menuItemId);

				// Don't set VD template if style id exists on menu item
				if($menuItem->template_style_id) {
					$curDomain->template = null;
				}
				break;
			case '1':
				if(!$curDomain->isHome ) {
					$curDomain->template = null;
				}
				break;
			case '0':
				$curDomain->template = null;
				break;
		}

		/**
		 * If languages are used, and a non-default language is being shown to the user, force the menuid to be
		 * the activeItemId.  This fixes the issue where modules (ie menu) are not shown when switching to a non-default
		 * language on the home page of the site, with no path (ie site.com/de/)
		 */
		if($app->getLanguage()->getDefault() !== $app->getLanguage()->get('tag')) {
			$curDomain->menuid = $curDomain->activeItemId;
		}

		$instance = $curDomain;

		return $instance;
	}

	/**
	 * Get default home menu item
	 * @return _defaultmenu <object| NULL>
	 */
	private function getDefaultmenu() {
		static $_defaultmenu;

		if(!empty($_defaultmenu)) return $_defaultmenu;

		$menu = Factory::getApplication()->getMenu('site', array());
		$_defaultmenu = $menu->getDefault();

		// fallback if default home item was not found
		if($_defaultmenu === 0) {
			$lang = Factory::getLanguage();
			$db = $this->_db;

			// first try to find a language specific home item
			$query  = "SELECT * FROM #__menu WHERE home = 1 AND language = ".$db->Quote(Factory::getLanguage()->getTag())." AND published >0";
			$db->setQuery($query);
			$_defaultmenu = $db->loadObject();

			// language specific home item was not found - get the global one
			if($_defaultmenu === null) {
				$query  = "SELECT * FROM #__menu WHERE home = 1 AND language = '*' AND published >0";
				$db->setQuery($query);
				$_defaultmenu = $db->loadObject();
			}
		}

		return $_defaultmenu;
	}

	/**
	 * Return the host check on backend request
	 */
	private function hostCheck() {
		$app = Factory::getApplication();
		// Joomla 3.2 will throw an error, if language filter is set
		if(method_exists($app, 'setLanguageFilter')) {
			$app->setLanguageFilter(false);
		}
		$host = $_SERVER['HTTP_HOST'];
		$data = json_encode(array('hostname'=>$host));

		ob_clean();
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('content-type: application/json; charset=utf-8');
		header("access-control-allow-origin: *");
		echo json_encode($data);
		exit;
	}

	/**
	 * 
	 */
	private function getLangHomeMenuItem($vdHomeMenuId) {
		$menu = Factory::getApplication()->getMenu('site', array());
		$menuItem = $menu->getItem( ( int ) $vdHomeMenuId);

		// Check menu item associations for current language, if menu item is not for all languages
		$langTag = Factory::getLanguage()->getTag();
		if($menuItem->language != '*' && $menuItem->language != $langTag) {
			$associations = Associations::getAssociations('com_menus', '#__menu', 'com_menus.item', (int)$menuItem->id, 'id', 'alias', null);
			$menuItem = $menu->getItem(explode(':', $associations[$langTag]->id)[0]);
		}

		return $menuItem;
	}

	/**
	 *
	 *  Routes to VD-Hosts home, if necessery
	 */
	private function reRoute($curDomain, &$uri )
	{
		//Is this the default domain?
		if($curDomain->home == 1) return;

		if ( $this->params->get( 'noreroute' ) )
		{
			return false;
		}

		// get domains home item
		$menu = Factory::getApplication()->getMenu('site', array());
		$menuItem = $menu->getItem( ( int ) $curDomain->menuid );
		if ( !$menuItem )
		{
			//item is lost
			return false;
		}

		// if / is called this should be home and we have to rewrite the request uri
		$rewrite = (str_replace('/', '', $_SERVER['REQUEST_URI'] ) == '');

		// the menu link
		$menulink = $menuItem->link;

		// change menu properties to point to the new home item
		$this->switchMenu( $menu, $menuItem );

		//do nothing else, if we are not on frontpage
		if ( !$curDomain->isHome )
		{
			return false;
		}

		// set domains home item as active item
		$menu->setActive( $curDomain->menuid );
		// push itemid to the request
		$this->addRequest( 'Itemid', $curDomain->menuid );

		//rewrite the uri
		$link = $menulink . "&Itemid=" . $curDomain->menuid;

		//Parse the new Url
		$parse = parse_url( $this->getBase() . $link );

		//Build the new Query
		if($rewrite) {
			$request = array();
			parse_str( $parse['query'], $request );

			$this->_request = array_merge( $request, $this->_request );
			$parse['query'] = Uri::buildQuery( $this->_request );

			//rewrite some server environment vars to fool joomla
			$_SERVER['QUERY_STRING'] = $parse['query'];
		}
		$_SERVER['REQUEST_URI'] = $this->getBase() . $link;
		$_SERVER['PHP_SELF'] = $this->getBase() . $parse['path'];
		$_SERVER['SCRIPT_NAME'] = $this->getBase() . $parse['path'];
		$_SERVER['SCRIPT_FILENAME'] = $_SERVER['DOCUMENT_ROOT'] . '/'. preg_replace( '#[/\\\\]+#', '/', $parse['path'] );

		//set userdefined actions
		$this->setActions( 1 );

		return true;
	}

	/**
	 *
	 *  Method to set userdefined params to $REQUEST or $GLOBALS
	 */
	private function setActions( $home = 0 )
	{
		$db = $this->_db;
		$db->setQuery( 'Select * From #__virtualdomain_params Where 1' );
		$result = $db->loadObjectList();

		$params = $this->_hostparams->getProperties();
		if ( count( $params ) )
		{
			for ( $i = 0; $i < count( $result ); $i++ )
			{
				foreach ( $params as $key => $value )
				{

					if ( !$home && $result[$i]->home ) continue;

					if ( $result[$i]->name == $key )
					{
						$value = urlencode( $value );
						$result[$i]->name = urlencode( $result[$i]->name );
						switch ( $result[$i]->action )
						{
							case 'globals':
								$GLOBALS[$result[$i]->name] = $value;
								break;
							case 'request':
								$this->addRequest( $result[$i]->name, $value );
								break;
						}
					}
				}
			}
		}
	}

	/**
	 * Change the domains global settings
	 */
	private function setConfig() {
		$config = Factory::getConfig();
		// options that can be changed
		$options = array('MetaDesc', 'sitename', 'list_limit', 'mailfrom', 'fromname');

		if ( is_object( $this->_hostparams ) )
		{
			// keywords is different fro Joomlas var MetaKeys - set it separately
			if ( trim( $this->_hostparams->get( 'keywords' ) ) )
			{
				$config->set( 'MetaKeys', $this->_hostparams->get( 'keywords' ) );
			}

			// Now iter over the available options that can be overrided
			foreach($options as $option) {
				if ( trim( $this->_hostparams->get( strtolower($option) ) ) )
				{
					$config->set( $option, $this->_hostparams->get( strtolower($option) ) );
				}
			}
		}
	}

	/**
	 * Method to set a domain specific default language
	 */
	private function setLangVars()
	{
		// default language is not set
		if ( !$this->_hostparams->get( 'language' ) )
		{
			return;
		}

		$hash = ApplicationHelper::getHash('language');

		//Joomla Language selection is active?  do nothing
		$joomlacookie = $this->input->cookie->get($hash);
		if($joomlacookie) {
			return;
		}

		// we have to override the joomla method to set the language
		$lang = new VdLanguage();
		$lang_code = $this->_hostparams->get( 'language' );

		$lang->setDefault($this->_hostparams->get( 'language' ));
		$lang = Factory::getLanguage();

		// don't override Joomfish cookie if present
		$jfcookie = $this->input->cookie->get('jfcookie');
		if ( isset( $jfcookie["lang"] ) && $jfcookie["lang"] != "" )
		{
			return;
		}

		// set Joomfish cookie
		$conf = Factory::getConfig();
		$cookie_domain 	= $conf->get('config.cookie_domain', '');
		$cookie_path 	= $conf->get('config.cookie_path', '/');
		setcookie($hash , $lang_code, time() + 365 * 86400, $cookie_path, $cookie_domain);

		// set the request var Joomla and Joomfish style
		$this->addRequest( 'lang', $lang_code );
		$_POST['lang'] = $lang_code ;
		$this->addRequest( 'language', $lang_code);
	}

	/**
	 * Method to set the requests
	 */
	private function setRequests() {
		if(count($this->_request)) {
			foreach( $this->_request as $key=>$var) {
				// set the request
				$this->input->set($key, $var);
			}
		}
	}

	/**
	 *
	 *  Method to switch the menu to the VD-hosts home
	 */
	private function switchMenu( & $menu, &$newhome )
	{

		// nohome should be a reference to menu object
		$nohome = $menu->getDefault();

		// unset old home item
		if($nohome !== 0) {
			$nohome->home = null;
		}

		// set new home item
		$newhome->home = 1;
		$menu->setDefault( $newhome->id);
	}

}
