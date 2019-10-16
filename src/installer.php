<?php
/**
* @package Ev Preloader Plugin for Joomla 3.x
* @version $Id: installer.php 100 2015-08-05 $
* @author EverLive.net
* @copyright (C) 2015 EverLive.net. All rights reserved.
* @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/

// No direct access to this file
defined( '_JEXEC' ) or die( 'Restricted access' );

class plgSystemEv_preloaderInstallerScript
{		
	public function preflight($type, $parent) {
		
	}
	
	public function postflight($type, $parent) 
	{	
		$this->showInstall();	
		return true;
	}
	
	public function install($parent) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$fields = array(
			$db->quoteName('enabled') . ' = ' . (int) 1,
			$db->quoteName('ordering') . ' = ' . (int) 9999
		);

		$conditions = array(
			$db->quoteName('element') . ' = ' . $db->quote('ev_preloader'), 
			$db->quoteName('type') . ' = ' . $db->quote('plugin')
		);

		$query->update($db->quoteName('#__extensions'))->set($fields)->where($conditions);

		$db->setQuery($query);   
		$db->execute();     		
	}
		
	protected function showInstall() {
		$lang = JFactory::getLanguage(); 
		$lang->load('plg_system_ev_preloader', JPATH_ADMINISTRATOR); 
		$db	= JFactory::getDBO();
		$q  = "SELECT extension_id FROM #__extensions WHERE `element` = 'ev_preloader' AND `type` = 'plugin'";
		$db->setQuery( $q );
		$plgId = $db->loadResult();
		$linkEdit = '';
		if ($plgId > 0){
			$linkEdit = JURI::root().'administrator/index.php?option=com_plugins&task=plugin.edit&extension_id='.$plgId;
			$linkEdit = '<a target="_blank" href="'.$linkEdit.'" 
							class="btn btn-primary btn-large">'
							.JText::_('PLG_SYS_EV_PRELOADER_POSTINSTALL_EDIT_PLUGIN').'</a>';
		}
?>
<style type="text/css">
#ev-installer-left {
	float: left;
	padding: 10px;
	width:30%;	
}

#ev-installer-right {
	float: left;
	padding: 10px;
	width:60%;
}

</style>

<div id="ev-installer-left">
	<a target="_blank" href="http://www.everlive.net/getting-started-cloud-backup.html">
		<img src="<?php echo JURI::root(); ?>plugins/system/ev_preloader/images/everlive2_300.png" 
		alt="<?php echo JText::_('PLG_SYS_EV_PRELOADER_POSTINSTALL_JEEV'); ?>" />
	</a>
	<h2><a target="_blank" href="http://www.EverLive.net/joomla-extensions.html"><?php 
		echo JText::_('PLG_SYS_EV_PRELOADER_POSTINSTALL_JEEV_ALL'); ?></a></h2>
</div>
<div id="ev-installer-right">
<a target="_blank" href="<?php echo JURI::root(); ?>" class="btn btn-primary btn-large"><?php 
	echo JText::_('PLG_SYS_EV_PRELOADER_POSTINSTALL_JEEV_HOWTO_CHECK') ?></a>
<a target="_blank" href="http://www.everlive.net/joomla-extensions/19-preloader-for-joomla.html" 
	class="btn btn-primary btn-large"><?php 
	echo JText::_('PLG_SYS_EV_PRELOADER_POSTINSTALL_JEEV_UGCAP') ?></a>
<?php echo $linkEdit ?>	
</div><br/><br/>
<?php	
	}
}
?>
