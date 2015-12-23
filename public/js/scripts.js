$(document).ready(function() {

	$('.pagination').jqPagination({
		link_string	: '/?page={page_number}',
		max_page	: 1,
		paged		: function(page) {
			get_files(page);
		}
	});
	
	get_cuckoo_infos();
	get_storage_info();
	
	$('[data-toggle="tooltip"]').tooltip();
	
});