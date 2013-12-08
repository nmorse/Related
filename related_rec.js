jQuery(document).ready(function($) {
	
	$('#related-recs-select').change(function() {
		var select = $(this),
				container = $('#related-recs'),
				id = select.val(),
				title = this.options[this.options.selectedIndex].text;
		
		if ($('#related-rec-' + id).length == 0) {
			container.prepend('<div class="related-rec" id="related-rec-' + id + '"><input type="hidden" name="related-recs[]" value="' + id + '"><span class="related-rec-title">' + title + '</span><a href="#">Delete</a></div>');
		}
	});
	
	$('.related-rec a').live('click', function() {
		var div = $(this).parent();
		
		div.css('background-color', '#ff0000').fadeOut('normal', function() {
			div.remove();
		});
		return false;
	});
	
	$('#related-recs').sortable();		
	
});
