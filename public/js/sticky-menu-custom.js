$(document).ready(function() {
	$('.top').addClass('hidden');
	$.waypoints.settings.scrollThrottle = 10;
	$('body').waypoint(function(event, direction) {
		$('.top').toggleClass('hidden', direction === "up");
	}, {
		offset: '-100%'
	}).find('.ribbon').waypoint(function(event, direction) {
		$(this).parent().toggleClass('sticky', direction === "down");
		event.stopPropagation();
	});
});
