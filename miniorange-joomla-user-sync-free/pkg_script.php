<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * @package     Joomla.Package
 *
 * @author      miniOrange Security Software Pvt. Ltd.
 * @copyright   Copyright (C) 2015 miniOrange (https://www.miniorange.com)
 * @license     GNU General Public License version 3; see LICENSE.txt
 * @contact     info@xecurify.com
 */
use Joomla\CMS\Factory;
class pkg_MiniorangeUserSyncInstallerScript
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
        require_once JPATH_ADMINISTRATOR . '/components/com_miniorange_usersync/helpers/MoUserSyncUtility.php';
        $siteName = $_SERVER['SERVER_NAME'];
        $email = Factory::getConfig()->get('mailfrom');
        $moPluginVersion = MoUserSyncUtility::moGetPluginVersion();
        $jCmsVersion = MoUserSyncUtility::getJoomlaCmsVersion();
        $phpVersion = phpversion();
        $moSererType = MoUserSyncUtility::getServerType();
        $query1 = '[Plugin ' . $moPluginVersion . ' | PHP ' . $phpVersion .' | Joomla Version '. $jCmsVersion .' | Server type '. $moSererType .']';
        $content = '<div>
            Hello,<br><br>
            API based user provisioning Plugin has been successfully installed on the following site.<br><br>
            <strong>Company:</strong> <a href="http://' . $siteName . '" target="_blank">' . $siteName . '</a><br>
            <strong>Admin Email:</strong> <a href="mailto:' . $email . '">' . $email . '</a><br>
            <strong>System Information:</strong> ' . $query1 . '<br><br>
        </div>';
        MoUserSyncUtility::send_efficiency_mail($email, $content); 
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
       if ($type == 'uninstall') {
        return true;
        }
       $this->showInstallMessage('');
    }

    protected function showInstallMessage($messages=array()) {
        ?>

    <style>
        .mo-row {
            width: 100%;
            display: block;
            margin-bottom: 2%;
        }

        .mo-row:after {
            clear: both;
            display: block;
            content: "";
        }

        .mo_usersync_btn {
            display: inline-block;
            font-weight: 300;
            text-align: center;
            vertical-align: middle;
            user-select: none;
            background-color: transparent;
            border: 1px solid transparent;
            padding: 4px 12px;
            font-size: 0.85rem;
            line-height: 1.5;
            border-radius: 0.25rem;
            transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        } 
       
        .mo_usersync_btn-cstm {
            background: #001b4c;
            border: none;
            font-size: 1.1rem;
            padding: 0.3rem 1.5rem;
            color: #fff !important;
            cursor: pointer;
        }
            
        :root[data-color-scheme=dark] {
            .mo_usersync_btn-cstm {
                color: white;
                background-color: #000000;
                border-color:1px solid #ffffff; 
            }

            .mo_usersync_btn-cstm:hover {
                background-color: #000000;
                border-color: #ffffff; 
            }
        }
    </style>
   
   <h3>Steps to use the User Sync plugin</h3>
    <ul>
        <li>Click on <b>Components</b></li>
        <li>Click on <b>miniOrange User Sync</b> and select <b>Manage Application </b>tab</li>
        <li>You can start configuring</li>
    </ul>
    <div class="mo-row">
        <a class="mo_usersync_btn mo_usersync_btn-cstm" href="index.php?option=com_miniorange_usersync&view=accountsetup&tab-panel=usersync">Get Started!</a>
	    <a class="mo_usersync_btn mo_usersync_btn-cstm" href="https://www.miniorange.com/contact" target="_blank">Get Support!</a>
    </div>
        <?php
    }
}