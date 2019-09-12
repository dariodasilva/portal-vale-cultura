$(document).ready(function(){

	// Blur images on mouse over
	//$(".portfolio a").hover( function(){ 
		//$(this).children("img").animate({ opacity: 0.5 }, "fast"); 
	//}, function(){ 
	//	$(this).children("img").animate({ opacity: 1.0 }, "slow"); 
	//}); 
	
	


	// Initialize prettyPhoto plugin
	$(".all a").prettyPhoto({
		theme:'dark_rounded', 
		autoplay_slideshow: false, 
		overlay_gallery: false, 
		show_title: false
	});

	// Clone portfolio items to get a second collection for Quicksand plugin
	var $portfolioClone = $(".portfolio").clone();
	
	// Attempt to call Quicksand on every click event handler
	$(".filters a, .filter a").click(function(e){
		
		$(".filters li, .filter li").removeClass("current");	
		
		// Get the class attribute value of the clicked link
		var $filtersClass = $(this).parent().attr("class");

		if ( $filtersClass == "all" ) {
			var $filtersedPortfolio = $portfolioClone.find("li");
		} else {
			var $filtersedPortfolio = $portfolioClone.find("li[data-type~=" + $filtersClass + "]");
		}
		
		// Call quicksand
		$(".portfolio").quicksand( $filtersedPortfolio, { 
			duration: 800, 
			easing: 'easeInOutQuad' 
		}, function(){
			
			// Blur newly cloned portfolio items on mouse over and apply prettyPhoto
			//$(".portfolio a").hover( function(){ 
				//$(this).children("img").animate({ opacity: 0.75 }, "fast"); 
			//}, function(){ 
				//$(this).children("img").animate({ opacity: 1.0 }, "slow"); 
			//}); 
			
			$(".all a").prettyPhoto({
				theme:'dark_rounded', 
				autoplay_slideshow: false, 
				overlay_gallery: false, 
				show_title: false
			});
		});


		$(this).parent().addClass("current");

		// Prevent the browser jump to the link anchor
		e.preventDefault();
	})
	
	// This filter is later used as the selector for which grid items to show.
			  var filter = '';
			  var handler;

			  // Prepare layout options.
			  var options = {
				autoResize: true, // This will auto-update the layout when the browser window is resized.
				container: $('#main'), // Optional, used for some extra CSS styling
				offset:15, // Optional, the distance between grid items
				itemWidth: 210 // Optional, the width of a grid item
			  };

			  // This function filters the grid when a change is made.
			  var refresh = function() {
				// This hides all grid items ("inactive" is a CSS class that sets opacity to 0).
				$('#tiles li').addClass('inactive');

				// Create a new layout selector with our filter.
				handler = $(filter);

				// This shows the items we want visible.
				handler.removeClass("inactive");

				// This updates the layout.
				handler.wookmark(options);
			  }

			  /**
			   * This function checks all filter options to see which ones are active.
			   * If they have changed, it also calls a refresh (see above).
			   */
			  var updateFilters = function() {
				var oldFilter = filter;
				filter = '';
				var filters = [];

				// Collect filter list.
				var lis = $('#filters li, #filters-phone li');
				var i=0, length=lis.length, li;
				for(; i<length; i++) {
				  li = $(lis[i]);
				  if(li.hasClass('active')) {
					filters.push('#tiles li.'+li.attr('data-filter'));
				  }
				}

				// If no filters active, set default to show all.
				if(filters.length == 0) {
				  filters.push('#tiles li');
				}

				// Finalize our filter selector for jQuery.
				filter = filters.join(', ');

				// If the filter has changed, update the layout.
				if(oldFilter != filter) {
				  refresh();
				}
			  };

			  /**
			   * When a filter is clicked, toggle it's active state and refresh.
			   */
			  var onClickFilter = function(event) {
				var item = $(event.currentTarget);
				item.toggleClass('active');
				updateFilters();
			  }

			  // Capture filter click events.
			  $('#filters li, #filters-phone li').click(onClickFilter);

			  // Do initial update (shows all items).
			  updateFilters();
});