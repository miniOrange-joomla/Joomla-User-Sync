<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
jimport('com_miniorange_usersync.admin.helpers.MoUserSyncCustomer');
/**
 * @package     Joomla.Plugin
 * @subpackage  plg_user_usersync
 *
 * @author      miniOrange Security Software Pvt. Ltd.
 * @copyright   Copyright (C) 2015 miniOrange (https://www.miniorange.com)
 * @license     GNU General Public License version 3; see LICENSE.txt
 * @contact     info@xecurify.com
 */
use Joomla\CMS\Factory;
include_once JPATH_SITE . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_miniorange_usersync' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'MoUserSyncUtility.php';
class plgUserMiniorangeUserSyncInstallerScript
{
    /**
     * This method is called after a component is installed.
     *
     * @param  \stdClass $parent - Parent object calling this method.
     *
     * @return void
     */
    public function install($parent) 
    {
        $db  = MoUserSyncUtility::moGetDatabase();
          $query1 = $db->getQuery(true);
          $query1->update('#__extensions');
          $query1->set($db->quoteName('enabled') . ' = 1');
          $query1->where($db->quoteName('element') . ' = ' . $db->quote('miniorangeusersync'));
          $query1->where($db->quoteName('type') . ' = ' . $db->quote('plugin'));
          $db->setQuery($query1);
          $db->execute();
    }

    /**
     * This method is called after a component is uninstalled.
     *
     * @param  \stdClass $parent - Parent object calling this method.
     *
     * @return void
     */
    public function uninstall($parent) 
    {

    }

    /**
     * This method is called after a component is updated.
     *
     * @param  \stdClass $parent - Parent object calling object.
     *
     * @return void
     */
    public function update($parent) 
    {
    
    }

    /**
     * Runs just before any installation action is performed on the component.
     * Verifications and pre-requisites should run in this function.
     *
     * @param  string    $type   - Type of PreFlight action. Possible values are:
     *                           - * install
     *                           - * update
     *                           - * discover_install
     * @param  \stdClass $parent - Parent object calling object.
     *
     * @return void
     */
    public function preflight($type, $parent) 
    {
    
    }

    /**
     * Runs right after any installation action is performed on the component.
     *
     * @param  string    $type   - Type of PostFlight action. Possible values are:
     *                           - * install
     *                           - * update
     *                           - * discover_install
     * @param  \stdClass $parent - Parent object calling object.
     *
     * @return void
     */
    function postflight($type, $parent) 
    {
      
    }

    
}
