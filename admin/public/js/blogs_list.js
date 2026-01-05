console.log("loaded......... = Blogs List");
$(document).ready(function () {
	if ($.fn.dataTable.isDataTable("#blogLists")) {
		table = $("#blogLists").DataTable({
			order: [
				[0, "desc"],
				[1, "desc"],
			],
			pageLength: 20,
			lengthMenu: [
				[5, 10, 20, -1],
				[5, 10, 20, "All"],
			],
			searching: true,
			info: false,
			lengthChange: true,
			language: {
				paginate: {
					previous: '<i class="fas fa-angle-left"></i>',
					next: '<i class="fas fa-angle-right"></i>',
				},
				search: "",
				searchPlaceholder: "Search",
			},
		});
	} else {
		table = $("#blogLists").DataTable({
			serverSide: true,
			processing: true,
			scrollCollapse: true,
			language: {
				paginate: {
					previous: '<i class="fas fa-angle-left"></i>',
					next: '<i class="fas fa-angle-right"></i>',
				},
				search: "",
				searchPlaceholder: "Search",
			},
			language: { search: "", searchPlaceholder: "Search..." },
			paging: true,
			searching: true,
			info: false,
			blengthChange: true,
			stateSave: true,
			order: [],
			pageLength: 10,
			iDisplayLength: 10,
			lengthMenu: [
				[10, 20, -1],
				[10, 20, "All"],
			],
			ajax: {
				url: BASE_URL + "BlogController/loadBlogsAjax",
				type: "POST",
				data: {},
			},
		});
	}
});
