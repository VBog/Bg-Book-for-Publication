jQuery(document).ready( function() {
	if(jQuery('#bg_bpub_book_for_publication').attr("checked") != 'checked') { 
		jQuery('#bg_bpub_nextpage_level').prop('disabled',true);
		jQuery('#bg_bpub_max_level').prop('disabled',true);
	} else {
		jQuery('#bg_bpub_nextpage_level').prop('disabled',false);
		jQuery('#bg_bpub_max_level').prop('disabled',false);
	}		
	jQuery('#bg_bpub_book_for_publication').click( function() {
		if(jQuery(this).attr("checked") != 'checked') { 
			jQuery('#bg_bpub_nextpage_level').prop('disabled',true);
			jQuery('#bg_bpub_max_level').prop('disabled',true);
		} else {
			jQuery('#bg_bpub_nextpage_level').prop('disabled',false);
			jQuery('#bg_bpub_max_level').prop('disabled',false);
		}		
	});
});
