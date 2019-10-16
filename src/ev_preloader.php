<?php
/**
* @package Ev Preloader Plugin for Joomla 3.x
* @version $Id: ev_preloader.php 100 2015-08-05 $
* @author EverLive.net
* @copyright (C) 2015 EverLive.net. All rights reserved.
* @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
if (!defined('DS')) define('DS','/');

jimport( 'joomla.plugin.plugin' );

class plgSystemEv_preloader extends JPlugin
{
	var $_rplTag = '<!-- @#@ Never Remove this comment @#@ -->';
	var $_cm_isAdmin;
	var $_pluginPath;
	var $_pluginUrlPath;
	var $_isPro=false;
	var $_objProHelper;
	
	function plgSystemEv_preloader(& $subject, $config) {
		parent::__construct($subject, $config);
		$this->loadLanguage();		
	}

	private function _isAdmin(){
		$app = JFactory::getApplication();
		$b = $app->isAdmin();
		
		$this->_cm_isAdmin = $b;
		return $b;
	}

	private function _initVars(){
		$this->_pluginPath = JPATH_ROOT.'/plugins/system/ev_preloader/';
		$this->_pluginUrlPath = JURI::root().'plugins/system/ev_preloader/';
		
		if ( !$this->_cm_isAdmin ){
			$fn = JPATH_PLUGINS.'/system/ev_preloader/helper_pro.php';
			if (file_exists($fn)){
				require_once($fn);
				$this->_isPro = true;
				$this->_objProHelper = new helper_proEv_preloader($this);
			}
		}
	}
	
	public function onAfterInitialise(){
		$this->_isAdmin();
		$this->_initVars();
	}

	public function onAfterRoute(){
	}
	
	public function onAfterDispatch(){
	}
	
	public function onAfterRender(){
		$this->insertPreloader2();
	}
	
	private function insertPreloader2(){
		if ( $this->_cm_isAdmin ) return;
		
		$ev_preloaded = JRequest::getVar('ev_preloaded',0, 'cookie');
		setcookie('ev_preloaded', 1, time() + 86400);

		$content  = JResponse::getBody(); 
		
		$style = 1;
		if ($this->_isPro){
			$this->_objProHelper->setStyle($this, $style);
		}
		//$style = 11;

		$js = '';
		if (stripos($content,'/jquery.js"')===false && stripos($content,'/jquery.min.js"')===false){
			$js .= '<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js" 
				type="text/javascript"></script>'."\n";
		}
		
		//new styles
		if ($this->_isPro){
			if ($style>=12){
				if ($style==12) $this->_objProHelper->loadStyle12($content, $js, $ev_preloaded);
				if ($style==13) $this->_objProHelper->loadStyle13($content, $js, $ev_preloaded);
				if ($style==14) $this->_objProHelper->loadStyle14($content, $js, $ev_preloaded);
				return;
			}
		}
		
		$css = '<style>'.file_get_contents($this->_pluginPath.'styles/style-'.$style.'.css')."\n";
		
		if ($this->_isPro){
			$this->_objProHelper->modifCss($this, $css, $style);
		}		
		$css.= '</style>';
		
		$js .= '<script src="'
				.$this->_pluginUrlPath.'styles/pfinal.js" async type="text/javascript"></script>'."\n";
				
		$html ='';
		$html.= file_get_contents($this->_pluginPath.'styles/style-'.$style.'.html')."\n";		
		$html.= '<script type="text/javascript">
					jQuery( document ).ready(function() {
						if (!evBrowserSupportsCSSProperty("animation")) {
							var e = document.getElementById("ev__loading");
							e.style.display="none";						
						}
					});
					';
		if (!$this->_isPro){
			$html.= 'var e = document.getElementById("evpwb");
					var c = ev__getStyle(e.parentNode,"backgroundColor");
					var c = ev__GetContrastYIQ(c);
					e.style.color = c;
					document.getElementById("evpwb-a").style.color = c;
					';
		}else{			
			$this->_objProHelper->setLogo($this, $html, $style);
		}
		
		$html.= '</script>';
		
		$ev=JText::_('PLG_SYS_EV_PRELOADER_POW_BY2');
		if ($ev!='EverLive.net')$ev='EverLive.net';
		
		$pwb = '<div id="evpwb" style="text-align: right;padding-right: 10px; z-index: 1"><i><small>'.
					JText::_('PLG_SYS_EV_PRELOADER_POW_BY1').' <a id="evpwb-a" target="_blank" 
					href="'.JText::_('PLG_SYS_EV_PRELOADER_POW_BY3').'">'
					.$ev.'</a></small></i>
				</div>
				';
		if ($this->_isPro) $this->_objProHelper->finalizeAni($pwb);
	
		if ($ev_preloaded==1) return;
		
		$content  = $this->str_ireplace_first('<link',$css.'<link',$content);
		$content  = $this->str_ireplace_first('</head',$js.'</head',$content);
		
		$bdPos  = stripos($content,'<body'); if($bdPos===false)$bdPos=0;
		$content  = $this->str_ireplace_first('<div',$html.'<div',$content,$bdPos);
		$content  = $this->str_ireplace_first('<div id="ev__loading">','<div id="ev__loading">'.$pwb,$content);
		
		if ($this->_isPro) $this->_objProHelper->getMoreProCode($this, $style, $content);
		
		JResponse::setBody( $content );		
	}	
	
	public function str_ireplace_first($search, $replace, $subject, $bdPos=0) {
		$pos = stripos($subject, $search, $bdPos);
		if ($pos !== false) {
			$subject = substr_replace($subject, $replace, $pos, strlen($search));
		}
		return $subject;
	}	

	function jeDebugS( $str ){
		$jsdebugs 	= JRequest::getVar('debugje', 0, 'request');
		
		if ($jsdebugs==1){
			echo $str . '<hr />';
		}

		if ($jsdebugs==2) $this->addToLog($str);
	}
	
	function addToLog($str){
		$fh = fopen(JPATH_PLUGINS."/system/ev_preloader/log.txt", 'a');// or die("can't open file");
		$str = "\n".date('Y-m-d H:i:s')."\n".$str."\n";
		fwrite($fh, $str);
		fclose($fh);	
	}
	
	function getResult( $query, $isCustom=false ){
		$db	= JFactory::getDBO();
		if ($isCustom) $db->custom = 1;

		$db->setQuery( $query );
		return $db->loadResult();
	}

	function doQuery( $query, $isCustom=false ){
		$db	= JFactory::getDBO();
		if ($isCustom) $db->custom = 1;
		
		$db->setQuery( $query );

	    return $db->query();
	}

	function getRows( $query, $limitStart=0, $recs=0, $key='', $isCustom=false ){
		$db	= JFactory::getDBO();
		if ($isCustom) $db->custom = 1;

		if ( $recs > 0 ) {
			$db->setQuery( $query, $limitStart, $recs );
		}else{
			$db->setQuery( $query );
		}

		if ($key!=''){
			$rows = $db->loadObjectList($key);
		}else{
			$rows = $db->loadObjectList();
		}
		return $rows;
	}

	function getRow( $query, $isCustom=false ){
	    $rows = $this->getRows( $query,0,0,'', $isCustom );
		$row  = '';
		if ( count( $rows ) > 0 ) $row  = $rows[0];
		return $row;
	}	

	function getBracketValSimple($str,$start,$end,$include=false){
		if ($start==''){
			$pos=0;
		}else{
			$pos = stripos($str,$start);		
			if ($pos === false) return '';
		}
		
		$pos_e = stripos($str,$end,$pos+strlen($start));
		if ($include){
			$res   = substr($str,$pos,$pos_e-$pos+strlen($end));//+strlen($start)
		}else{	
			$res   = substr($str,$pos+strlen($start),$pos_e-$pos-strlen($start));
		}
		return $res;
	}
		
}