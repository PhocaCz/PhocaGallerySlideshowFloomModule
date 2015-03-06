<?php
/*
 * @package		Joomla.Framework
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @component Phoca Module
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License version 2 or later;
 */

defined('_JEXEC') or die('Restricted access');
if (!JComponentHelper::isEnabled('com_phocagallery', true)) {
	return JError::raiseError(JText::_('Phoca Gallery Error'), JText::_('Phoca Gallery is not installed on your system'));
}

if (! class_exists('PhocaGalleryLoader')) {
    require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_phocagallery'.DS.'libraries'.DS.'loader.php');
}

phocagalleryimport('phocagallery.path.path');
phocagalleryimport('phocagallery.file.file');
phocagalleryimport('phocagallery.file.filethumbnail');

$db 		= &JFactory::getDBO();
$document	= JFactory::getDocument();

JHTML::stylesheet('modules/mod_phocagallery_slideshow_floom/css/style.css' );



$moduleclass_sfx	= trim( $params->get( 'moduleclass_sfx' ) );

$catId 				= $params->get( 'category_id', 0 );
$count				= $params->get( 'count_images', 5 );
$width 				= $params->get( 'width', 970 );
$height				= $params->get( 'height', 230 );

//$buttons 			= $params->get( 'display_buttons', 1 );
$desc 				= $params->get( 'display_desc', 1 );
$duration 			= $params->get( 'duration', 70 );
$interval 			= $params->get( 'interval', 8000 );
$progressbar		= $params->get( 'display_progressbar', 1 );
//$transition 		= $params->get( 'fx_transition', 'Bounce' );
//$ease 			= $params->get( 'fx_ease', 'easeOut');
//$pathImg 			= JURI::base(true).'/modules/mod_phocagallery_slideshow_floom/images/';
$mode				= 'horizontal';
$iCss 				= $params->get( 'css_description', 'width:500px;
	height:60px;
	padding: 10px;
	-moz-border-radius: 10px 0px 0px 0px;
	-webkit-border-radius: 10px 0px 0px 0px;
	border-radius: 10px 0px 0px 0px;
	color: #000;
	bottom:0px;
	right:0px;
	background: #fff;
	position:absolute;' );

JHTML::_('behavior.framework', true);
$document->addScript(JURI::base(true).'/modules/mod_phocagallery_slideshow_floom/javascript/floom.js');
$document->addCustomTag( "<style type=\"text/css\"> \n"  
		." #pgfs .pgfs_floom_caption {".strip_tags($iCss)."} \n"			
		." </style> \n");

// IMAGES
$query      = ' SELECT a.title, a.description, a.filename'
			. ' FROM #__phocagallery_categories AS cc'
			. ' LEFT JOIN #__phocagallery AS a ON a.catid = cc.id'
			. ' WHERE a.published = 1 AND a.catid = ' . (int)$catId
			//. ' WHERE cc.published = 1 AND a.published = 1 AND a.catid = ' . (int)$catId
			//. ' ORDER BY RAND()'
			. ' ORDER BY a.ordering'
			. ' LIMIT '.(int)$count;
$db->setQuery($query);
$images = $db->loadObjectList();


// Params
$descO = '';
if ($desc != 1) {
	$descO = 'captions: false,'. "\n";
}
$durO = '';
if ((int)$duration > 0) {
	$durO = 'animation: '.(int)$duration.', '. "\n";
}
$proO = '';
if ($progressbar != 1) {
	$proO = 'progressbar: false, '. "\n";;
}
$intO = '';
if ((int)$interval > 0) {
	$intO = 'interval: '.(int)$interval.', '. "\n";
}


$js = '';
if (!empty($images)) {
	$js = '<script language="javascript" type="text/javascript" charset="utf-8">
	//<![CDATA[
	window.addEvent(\'domready\',function(e){'	. "\n";
	
	$js .= '   var pOItemsFloom =['	. "\n";
	$jsi	= array();
	foreach ($images as $k => $v) {
		
		$thumbLink	= PhocaGalleryFileThumbnail::getThumbnailName($v->filename, 'large');
		$img		= JURI::base(true).'/'.$thumbLink->rel;
		$caption	= trim(strip_tags( addslashes($v->description)));
		$caption 	= str_replace ("\r", '', $caption);
		$caption 	= str_replace ("\n", ' ', $caption);
		$title 		= htmlspecialchars( addslashes($v->title));
		if ($title != '' && $caption != '') {
			$caption = $title .' - '. $caption;
		} else if ($title != '' && $caption == '') {
			$caption = $title;
		}
	
		$jsi[] = '  {image: \''.$img.'\', title:\''.$title.'\', caption:\''.$caption.'\'}'. "\n";
	}
	$js .= implode($jsi, ',');
	$js .= '   ]'. "\n";
	
	$js .= '$(\'pgfs\').floom(pOItemsFloom, {
				slidesBase: \'\','. "\n";
	$js .= '				'.$descO;
	$js .= '				'.$durO;
	$js .= '				'.$proO;
	$js .= '				'.$intO;
	$js .= '			sliceFxIn: {
					left: [20, 0]'. "\n";
	$js .= '	}
			});'. "\n";
	
	$js .= '   });'. "\n";
	$js .= '   //]]> '. "\n";
	$js .= '</script>'. "\n";
}

// HTML
if (!empty($images)) {
	echo '<div class="'.$moduleclass_sfx.'" id="pgfsbox" style="width: '.$width.'px; height: '.$height.'px;">
		<div id="pgfs" style="width: '.$width.'px; height: '.$height.'px;">
		</div>
	</div>';
}
$document->addCustomTag($js);
?>