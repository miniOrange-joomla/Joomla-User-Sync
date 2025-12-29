<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  plg_user_usersync
 *
 * @author      miniOrange Security Software Pvt. Ltd.
 * @copyright   Copyright (C) 2015 miniOrange (https://www.miniorange.com)
 * @license     GNU General Public License version 3; see LICENSE.txt
 * @contact     info@xecurify.com
 */
// no direct access

defined('_JEXEC') or die('Restricted access');

jimport('joomla.user.helper');
jimport('joomla.plugin.plugin');
use Joomla\CMS\Plugin\CMSPlugin;

class plgUserminiorangeusersync extends CMSPlugin
{

    public function onUserAfterDelete($user, $success, $msg)
	{
		
    
	}

    public function onUserAfterSave($user, $success, $msg){
        
    
    }

    function onExtensionBeforeUninstall($id)
    {
     
    }
    

}