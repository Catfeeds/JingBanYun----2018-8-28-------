function adjustPageLayout() {
	if (!isFullScreen) {
		var cw = $('#contentWrapper').height();
		if (cw < 650) {
			$('#contentWrapper').height(700)
		}
	}
}

function adjustPageLayoutclass() {
	if (!isFullScreen) {
		var cw = $('#contentWrapper').height();
		if (cw < 700) {
			$('#contentWrapper').height(700)
		}
	}
}