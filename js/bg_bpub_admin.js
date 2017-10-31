jQuery(document).ready( function() {
	if(jQuery('#bg_bpub_the_book').attr("checked") != 'checked') { 
		jQuery('#bg_bpub_nextpage_level').prop('disabled',true);
		jQuery('#bg_bpub_toc_level').prop('disabled',true);
	} else {
		jQuery('#bg_bpub_nextpage_level').prop('disabled',false);
		jQuery('#bg_bpub_toc_level').prop('disabled',false);
	}		
	jQuery('#bg_bpub_the_book').click( function() {
		if(jQuery(this).attr("checked") != 'checked') { 
			jQuery('#bg_bpub_nextpage_level').prop('disabled',true);
			jQuery('#bg_bpub_toc_level').prop('disabled',true);
		} else {
			jQuery('#bg_bpub_nextpage_level').prop('disabled',false);
			jQuery('#bg_bpub_toc_level').prop('disabled',false);
		}		
	});
});
