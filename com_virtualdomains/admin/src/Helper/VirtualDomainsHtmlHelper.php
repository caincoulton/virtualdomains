<?php

namespace Janguo\Component\VirtualDomains\Administrator\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * @version		$Id$
 * @package		Virtualdomain
 * @subpackage 	Helpers
 * @copyright	Copyright (C) 2010, . All rights reserved.
 * @author     	Michael Liebler {@link http://www.janguo.de}
 * @copyright	Copyright (C) 2008 - 2013 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Virtualdomains is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

abstract class VirtualDomainsHTMLHelper {
    /**
     * 
     */
    public static function domains ($domain, $control = 'jform[domain]', $attribs = array('selecttext'=>null, 'class'=>"inputbox", 'onchange'=>'', 'multiple'=>'', 'size'=>''))
    { 
           
        $db             = Factory::getDbo();
        
        $query = "SELECT domain as value, domain as text
                                        FROM #__virtualdomain
                                        WHERE published =1";
        $db->setQuery($query );
        
        $options = $db->loadObjectList();
        
        $selecttext  = (isset($attribs['selecttext']) && $attribs['selecttext']) ? $attribs['selecttext'] :  Text::_('JALL');

        array_unshift($options, HTMLHelper::_('select.option', '',$selecttext));
        
        $attr = "";
        $attr .= (isset($attribs['class']) && $attribs['class']) ? ' class="'.(string) $attribs['class'].'"' : '';
        $attr .= (isset($attribs['onchange']) && $attribs['onchange']) ? ' onchange="'.(string) $attribs['onchange'].'"' : '';
        $attr .= (isset($attribs['multiple']) && $attribs['multiple']) ? ' multiple="multiple"' : '';
        $attr .= (isset($attribs['size']) && $attribs['size']) ? ' size="'.(int) $attribs['size'].'"' : '';
        //return HTMLHelper::_('access.level', $this->name, $this->value, $attr, $options, $this->id);

        return HTMLHelper::_('select.genericlist', $options, $control,
                array(
                        'list.attr' => $attr,
                        'list.select' => $domain,
                        'id' => 'form_vd_domain'
                ));     
    }
   
    /**
     * 
     */
    public static function languages($lang,  $control = 'jform[language]', $attribs='class="inputbox"') {
                   
        $options = HTMLHelper::_('contentlanguage.existing', true, true);
        array_unshift($options, HTMLHelper::_('select.option', '', Text::_('JALL')));

            return HTMLHelper::_('select.genericlist', $options, $control,
        array(
                'list.attr' => $attribs,
                'list.select' => $lang,
                'id' => 'form_vd_lang'
        ));                             

   }
}

