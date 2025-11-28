var user_listing_table = $('#user_listing_table').DataTable({
    processing: true,
    serverSide: true,
    pageLength: 20,
    lengthMenu: [10, 20, 40, 50, 100],
    searching: false,
    lengthChange: false, // Disable the page length change
    fnRowCallback: function (nRow, aData, iDisplayIndex) {
        var oSettings = this.fnSettings();
        $('html, body').stop().animate({
            scrollTop: $('#user_listing_table').offset().top // Update to use the correct table ID
        }, 1000);
        $('td:nth-child(1)', nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
        return nRow;
    },
    ajax: {
        url: `{{ route('user.list') }}`,
        type: 'POST',
        data: function (d) {
            d.searchform = $('form#filter').serializeArray();
        },
        headers: {
            'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')
        }
    },
    columns: [
        { data: 's_no' },
        { data: 'name' },
        { data: 'email' },
        { data: 'created_at' },
        { data: 'status' },
        { data: 'action' },
    ],
    ordering: false,
});

$(user_listing_table.table().container()).removeClass('form-inline');

/*** */