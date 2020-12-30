/**
 * @author Paul Chan / KF Software House 
 * http://www.kfsoft.info
 *
 * Version 0.5
 * Copyright (c) 2010 KF Software House
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/mit-license.php
 *
 */
	
(function($) {

    var _options = null;

	jQuery.fn.MyQRCode = function(options) {
		_options = $.extend({}, $.fn.MyQRCode.defaults, options);

		return this.each(function()
		{
			var codebase = "https://chart.googleapis.com/chart?chs={size}&cht=qr&chl={content}&choe={encoding}";
			var mycode = codebase.replace(/{size}/g, _options.size);
			mycode = mycode.replace(/{content}/g, encodeURI(_options.content));
			mycode = mycode.replace(/{encoding}/g, _options.encoding);
			//$("#genQrCode").remove();
			$(this).append("<img src='"+mycode+"'>");
		});
	}
	
	//default values
	jQuery.fn.MyQRCode.defaults = {
		encoding:"UTF-8",
		content: window.location,
		size:"150x150"
	};
})(jQuery);