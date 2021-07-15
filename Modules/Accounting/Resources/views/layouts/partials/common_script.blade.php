<script type="text/javascript">
	$(document).ready( function () {

    	account_type_table = $('#account_type_table').DataTable({
	        processing: true,
	        serverSide: true,
	        ajax: '{{action("\Modules\Accounting\Http\Controllers\DoubleEntryAccountCategoryController@getData")}}',
	        // columnDefs: [
	        //     {
	        //         //targets: [0, 5, 6, 7],
	        //         orderable: false,
	        //         searchable: false,
	        //     },
	        // ],
	        "order": [[ 5, "desc" ]],
	        columns: [	 
                { data: 'id', name: 'id' },    	 
	            { data: 'type', name: 'account_type.type' },
	            { data: 'category_code', name: 'category_code' },
	            { data: 'category_name', name: 'category_name' },
	            { data: 'sort_order', name: 'sort_order' },	            
	            { data: 'action', name: 'action', orderable:false },
	        ],
	        fnDrawCallback: function(oSettings) {
	            __currency_convert_recursively($('#account_type_table'));
	        },
		});
		
		
		account_table = $('#account_table').DataTable({
	        processing: true,
	        serverSide: true,
	        ajax: '{{action("\Modules\Accounting\Http\Controllers\DoubleEntryAccountController@getData")}}',	        
	        "order": [[ 5, "desc" ]],
			"columnDefs": [
                { className: "text-right text-bold", "targets": [6] },
                { className: "text-center", "targets": [4,7] },
                ] , 
	        columns: [	 
				{ data: 'action', name: 'action', orderable:false }, 
                { data: 'account_code', name: 'account_code' },
				{ data: 'account_no', name: 'account_no' },  
	            { data: 'account_name', name: 'account_name' },
				{ data: 'account_type', name: 'account_type.type' },
	            { data: 'category_name', name: 'category.category_name' },
	            { data: 'balance', name: 'balance'},	
	            { data: 'status', name: 'status', orderable:false },
	        ],
	        fnDrawCallback: function(oSettings) {
	            __currency_convert_recursively($('#account_table'));
	        },
		});
		
		$(document).on('change', '#account_type', function() {
			get_categories();
		});

		


	


		function get_categories() {
			$('#account_category').html();
			var type = $('#account_type').val();
			$.ajax({
				method: 'POST',
				url: '/accounting/account/categories-by-type',
				dataType: 'html',
				data: { type_id: type },
				success: function(result) {
					// console.log(result)
					if (result) {
						$('#account_category').html(result);
						$("#is_bank_div").html(null);
					}

				},
			});
		}

		
	});

</script>