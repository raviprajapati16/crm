$("#view_products").on("change", function () {
  console.log($(this).val());
  table.column(6).search($(this).val()).draw();
});

$("#view_source").on("change", function () {
  console.log($(this).val());
  table.column(10).search($(this).val()).draw();

});
$("#view_status").on("change", function () {
  console.log($(this).val());
  var selectedStatuses = $(this).val();
  table.column(9).search(selectedStatuses ? selectedStatuses.join('|') : '', true, false).draw();
  if (selectedStatuses == null || selectedStatuses == '') {
    // If filterValue is not found, just reload the table
    table.draw();
  }
});

$("#view_assigned").on("change", function () {
  $search = $(this).val();
  console.log($(this).val());
  table.column(13).search($(this).val()).draw();
  if (search == null || search == '') {
    // If filterValue is not found, just reload the table
    table.draw();
  }
});

$("#countries_filter").on("change", function () {
  console.log($(this).val());
  var selectedCountry = $(this).val();
  table.column(14).search(selectedCountry ? '^' + selectedCountry + '$' : '', true, false).draw();
  if (selectedCountry == null || selectedCountry == '' || selectedCountry == []) {
    // If filterValue is not found, just reload the table
    table.draw();
  }
});
$("#states_filter").on("change", function () {
  console.log($(this).val());
  var selectedState = $(this).val();
  table.column(15).search(selectedState ? '^' + selectedState + '$' : '', true, false).draw();
  if (selectedState == null || selectedState == '' || selectedState == []) {
    // If filterValue is not found, just reload the table
    table.draw();
  }
});
$("#cities_filter").on("change", function () {
  console.log($(this).val());
  var selectedCity = $(this).val();
  table.column(16).search(selectedCity ? '^' + selectedCity + '$' : '', true, false).draw();
  if (selectedCity == null || selectedCity == '' || selectedCity == []) {
    // If filterValue is not found, just reload the table
    table.draw();
  }
});


$("#followup_date").on("change", function () {
  var inputValue = $(this).val();

  var parts = inputValue.split("-");
  var day = parts[0];
  var month = parts[1];
  var year = parts[2];

  // Reformat the date to match the table format: yyyy-mm-dd
  var formattedDate = year + '-' + month + '-' + day;

  // Log the formatted date to check
  console.log(formattedDate);

  // Perform the search and redraw the table
  table.column(18).search(formattedDate).draw();
  if (inputValue == null || inputValue == '') {
    // If filterValue is not found, just reload the table
    table.draw();
  }
});

$("#from_date").on("change", function () {
  var fromDate = $("#from_date").val();
  var toDate = $("#to_date").val();
  var dateFilter = $("#date_by").val();

  // Check if both from date and to date are selected
  if (fromDate && toDate && dateFilter) {
    applyDateFilter(dateFilter, fromDate, toDate);
  }
});

$("#to_date").on("change", function () {
  var fromDate = $("#from_date").val();
  var toDate = $("#to_date").val();
  var dateFilter = $("#date_by").val();

  // Check if both from date and to date are selected
  if (fromDate && toDate && dateFilter) {
    applyDateFilter(dateFilter, fromDate, toDate);
  }
});

function applyDateFilter(dateFilter, fromDate, toDate) {
  var fromParts = fromDate.split("-");
  var toParts = toDate.split("-");
  var fromDay = fromParts[0];
  var fromMonth = fromParts[1];
  var fromYear = fromParts[2];
  var toDay = toParts[0];
  var toMonth = toParts[1];
  var toYear = toParts[2];

  var formattedFromDate = fromYear + '-' + fromMonth + '-' + fromDay;
  var formattedToDate = toYear + '-' + toMonth + '-' + toDay;

  if (dateFilter === 'dateadded') {
    // Filter based on Date Added
    table.column(19).search(formattedFromDate + '|' + formattedToDate, true, false).draw();
  } else if (dateFilter === 'lastcontact') {
    console.log(formattedFromDate + '|' + formattedToDate);
    // Filter based on Last Contacted
    table.column(20).search(formattedFromDate + '|' + formattedToDate, true, false).draw();
  } else if (dateFilter == null || dateFilter == '') {
    // If filterValue is not found, just reload the table
    table.draw();
  }
}

// $("#custom_view").on("change", function () {
//   var filterValue = $(this).val();
//   console.log(filterValue);

//   // Determine the column index based on the selected filter value
//   if (filterValue === 'lost') {
//     columnIndex = 23;
//     searchValue = 1;
//   } else if (filterValue === 'junk') {
//     columnIndex = 22; // Index of the 'junk' column
//     searchValue = 1;

//   } else if (filterValue === 'public') {
//     columnIndex = 24; // Index of the 'public' column
//     searchValue = 1;

//   }
//   else if (filterValue === 'not_assigned') {
//     columnIndex = 13; // Index of the 'public' column
//     searchValue = 0;

//   }
//   table.column(columnIndex).search(searchValue).draw();

// });

$("#custom_view").on("change", function () {
  var filterValue = $(this).val();
  console.log(filterValue);

  // Determine the column index and search value based on the selected filter value
  var columnIndex = -1;
  var searchValue = '';

  if (filterValue === 'lost') {
    columnIndex = 22;
    searchValue = 1;
  } else if (filterValue === 'junk') {
    columnIndex = 23; // Index of the 'junk' column
    searchValue = 1;
  } else if (filterValue === 'public') {
    columnIndex = 24; // Index of the 'public' column
    searchValue = 1;
  } else if (filterValue === 'not_assigned') {
    columnIndex = 13; // Index of the 'assigned' column
    searchValue = 0;
  } else if (filterValue === 'contacted_today') {
    columnIndex = 20;
    searchValue = getDateFormatted();
  } else if (filterValue === 'created_today') {
    columnIndex = 19;
    searchValue = getDateFormatted();
  } else if (filterValue === 'today_leads') {
    columnIndex = 18;
    searchValue = getDateFormatted();
  } else if (filterValue === 'lapsed_lead') {
    columnIndex = 17;
    searchValue = '>= ' + getDateFormattedThirtyDaysAgo();
  } else if (filterValue == null || filterValue == '') {
    // If filterValue is not found, just reload the table
    table.draw();
  }

  // Apply the filter based on the selected value
  table.column(columnIndex).search(searchValue).draw();
});
function getDateFormattedThirtyDaysAgo() {
  var today = new Date();
  var thirtyDaysAgo = new Date(today.getTime() - 30 * 24 * 60 * 60 * 1000); // Calculate 30 days ago
  var dd = String(thirtyDaysAgo.getDate()).padStart(2, '0');
  var mm = String(thirtyDaysAgo.getMonth() + 1).padStart(2, '0'); // January is 0!
  var yyyy = thirtyDaysAgo.getFullYear();

  return yyyy + '-' + mm + '-' + dd;
}

// Function to get today's date in yyyy-mm-dd format
function getDateFormatted() {
  var today = new Date();
  var dd = String(today.getDate()).padStart(2, '0');
  var mm = String(today.getMonth() + 1).padStart(2, '0'); // January is 0!
  var yyyy = today.getFullYear();

  return yyyy + '-' + mm + '-' + dd;
}
$(document).on('change', '#mass_select_all', function () {

  var to, rows, checked;
  to = $(this).data('to-table');

  rows = $('.table-' + to).find('tbody tr');
  checked = $(this).prop('checked');
  // console.log(rows);
  $.each(rows, function () {
    $(this).find('input').prop('checked', checked);
    console.log($(this).find('input').prop('checked', checked));
  });
});





