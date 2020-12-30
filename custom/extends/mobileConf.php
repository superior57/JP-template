<?php
	include_once './include/extends/MobileUtil.php';

	//　携帯分岐
	if( $mobile_flag && !$terminal_type ){
		$terminal_type = MobileUtil::getTerminal();
	}
	
	switch($terminal_type){
		case MobileUtil::$TYPE_NUM_DOCOMO:
		case MobileUtil::$TYPE_NUM_MOBILE_CRAELER:
			$OUTPUT_CHARACODE = "SJIS-win";
			$LONG_OUTPUT_CHARACODE = "Shift_JIS";
			
			header("Content-type: application/xhtml+xml;charset=".$LONG_OUTPUT_CHARACODE);
			include_once "./include/extends/mobile/EmojiDocomo.php";
			break;
		case MobileUtil::$TYPE_NUM_AU:
			$OUTPUT_CHARACODE = "SJIS-win";
			$LONG_OUTPUT_CHARACODE = "Shift_JIS";
			
			header("Content-type: text/html; charset=".$LONG_OUTPUT_CHARACODE);
			include_once "./include/extends/mobile/EmojiAU.php";
			break;
		case MobileUtil::$TYPE_NUM_SOFTBANK:
			include_once "./include/extends/mobile/EmojiSoftbank.php";
			break;
		default:
			include_once "./include/extends/mobile/EmojiPc.php";
			break;
	}
	if( $SYSTEM_CHARACODE != $OUTPUT_CHARACODE){
    	mb_convert_variables($SYSTEM_CHARACODE,$OUTPUT_CHARACODE, $_POST);
    	mb_convert_variables($SYSTEM_CHARACODE,$OUTPUT_CHARACODE, $_GET);
	}

    if($terminal_type)
	{
		if($mobile_flag) $template_path = "./template/mobile/";
		//$IMAGE_NOT_FOUND = '<img src="./common/img/no_img_120x90.gif" />';
    }

	if($sp_flag)
	{// スマートフォン分岐
		if(file_exists("./include/extends/SmartPhoneUtil.php"))
		{
			include_once "./include/extends/SmartPhoneUtil.php";

			if( SmartPhoneUtil::checkTablet() || SmartPhoneUtil::checkSP() )
			{
				global $sp_device;
				$sp_device = true;
				$mode = SmartPhoneUtil::getMode();

				switch($mode)
				{
				case 'tablet':
					$template_path = "./template/pc/";
					$sp_mode = false;
					break;
				case 'sp':
				default:
					$template_path = "./template/sp/";
					$sp_mode = true;
					break;
				case 'pc':
					break;
				}
			}
		}
	}
