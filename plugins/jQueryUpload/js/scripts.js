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
	
    $('.table-responsive').on('show.bs.dropdown', function () {
        $('.table-responsive').css( "overflow-y", "auto" );
		$('.table-responsive td').css( "overflow", "auto" );
    });

    $('.table-responsive').on('hide.bs.dropdown', function () {
        $('.table-responsive').css( "overflow-y", "hidden" );
		$('.table-responsive td').css( "overflow", "hidden" );
    })   
	
});