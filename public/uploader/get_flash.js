(function(){
	var version = swfobject.getFlashPlayerVersion();
	if (swfobject.hasFlashPlayerVersion("9")) {
	} else {
		window.location.href = 'uploader/get_flash.html';
	}
})();