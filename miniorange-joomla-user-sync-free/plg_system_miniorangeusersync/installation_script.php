<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
/**
 * @package     Joomla.Plugin
 * @subpackage  plg_system_miniorangeusersync
 *
 * @author      miniOrange Security Software Pvt. Ltd.
 * @copyright   Copyright (C) 2015 miniOrange (https://www.miniorange.com)
 * @license     GNU General Public License version 3; see LICENSE.txt
 * @contact     info@xecurify.com
 */
use Joomla\CMS\Factory;
include_once JPATH_SITE . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_miniorange_usersync' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'MoUserSyncUtility.php';
class plgSystemMiniorangeusersyncInstallerScript
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
          $query = $db->getQuery(true);
          $query->update('#__extensions');
          $query->set($db->quoteName('enabled') . ' = 1');
          $query->where($db->quoteName('element') . ' = ' . $db->quote('miniorangeusersync'));
          $query->where($db->quoteName('type') . ' = ' . $db->quote('plugin'));
          $db->setQuery($query);
          $db->execute();
    }
}