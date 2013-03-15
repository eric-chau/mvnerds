jQuery(function() {	
	$('.tooltip-anchor').tooltip();
	
	initBenchmark();
	
	$win.on('scroll', processScroll);
	
	var initialPath = location.pathname;
	
	if (typeof history.pushState === "function") {
		history.pushState(location.pathname, null, null);
		window.onpopstate = function () {
			console.log('first');
			if (initialPath == location.pathname) {
				initialPath = null;
				return;
			}else {
				var championsPageUrl = Routing.generate('pmri_create', {_locale: locale});
				if (document.referrer == championsPageUrl) {
					location.href=championsPageUrl;
				} else {
					history.back();
				}
			}
		};
	}
});