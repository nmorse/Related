jQuery(document).ready(function($) {
	
	$('#related-arts-select').change(function() {
		var select = $(this),
				container = $('#related-arts'),
				id = select.val(),
				title = this.options[this.options.selectedIndex].text;
		
		if ($('#related-art-' + id).length == 0) {
			container.prepend('<div class="related-art" id="related-art-' + id + '"><input type="hidden" name="related-arts[]" value="' + id + '"><span class="related-art-title">' + title + '</span><a href="#">Delete</a></div>');
		}
	});
	
	$('.related-art a').live('click', function() {
		var div = $(this).parent();
		
		div.css('background-color', '#ff0000').fadeOut('normal', function() {
			div.remove();
		});
		return false;
	});
	
	$('#related-arts').sortable();

});
