$(document).ready(function() {
	$('.customtooltip_bottom').tooltipster({
		position: 'bottom',
		animation: 'fade',
	    delay: 200,
	    theme: 'af-tooltip-theme',
	    touchDevices: false,
	    trigger: 'hover'
	});
	$('.customtooltip_top').tooltipster({
		position: 'top',
		animation: 'fade',
	    delay: 200,
	    theme: 'af-tooltip-theme',
	    touchDevices: false,
	    trigger: 'hover'
	});
	$('.customtooltip_right').tooltipster({
		position: 'right',
		animation: 'fade',
	    delay: 200,
	    theme: 'af-tooltip-theme',
	    touchDevices: false,
	    trigger: 'hover'
	});
});