var $win = $(window), 
	$actionBar,
	actionBarTop,
	isFixed = 0,
	$benchmarkChampion,
	benchmarkChampionTop,
	isBenchmarkChampionFixed;

function initBenchmark(){
	$actionBar = $('div.actions-bar');
	actionBarTop =  $actionBar.length && $actionBar.offset().top;
	isFixed = 0;
	
	$benchmarkChampion = $('div#compare-champion-div-header'),
	benchmarkChampionTop = $benchmarkChampion.length && $benchmarkChampion.offset().top,
	isBenchmarkChampionFixed = 0;
	
	processScroll();
}

 //Fixation du subnav en fonction du scroll
function processScroll()
{
	var scrollTop = $win.scrollTop();
	
	if ($actionBar.length > 0) {
		if (scrollTop >= actionBarTop - 30 && !isFixed) {
			isFixed = 1;
			$actionBar.addClass('active');
			$actionBar.parent('.champions-handler-container').addClass('active');
		} 
		else if (scrollTop <= actionBarTop - 30 && isFixed) {
			isFixed = 0;
			$actionBar.removeClass('active');
			$actionBar.parent('.champions-handler-container').removeClass('active');
		}
	}else if ($benchmarkChampion.length > 0) {
		if (scrollTop >= benchmarkChampionTop - 5 && !isBenchmarkChampionFixed) {
			isBenchmarkChampionFixed = 1;
			$benchmarkChampion.addClass('active');
			$benchmarkChampion.parent('#champion-comparator').addClass('active');
		} 
		else if (scrollTop <= benchmarkChampionTop - 5 && isBenchmarkChampionFixed) {
			isBenchmarkChampionFixed = 0;
			$actionBar.removeClass('active');
			$benchmarkChampion.removeClass('active');
			$benchmarkChampion.parent('#champion-comparator').removeClass('active');
		}
	}
}

jQuery(function() {
	
	initBenchmark();
	
	$win.on('scroll', processScroll);
});