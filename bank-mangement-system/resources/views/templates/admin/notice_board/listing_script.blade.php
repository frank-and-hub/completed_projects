<script type="text/javascript">
$(document).ready(function () {

    var noticeTable = $('#notice-list').DataTable({
        /*processing: true,
        serverSide: true,*/
         pageLength: 20,
         lengthMenu: [10, 20, 40, 50, 100],
        ajax: {
            "url": "{!! route('admin.notice.listing') !!}",
            "type": "POST",
            "data":{'token':$('meta[name="csrf-token"]').attr('content')},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
         "columnDefs": [{
             "render": function (data, type, full, meta) {
                 return meta.row + 1;
             },
             "targets": 0
         }],
        columns: [
            {title: 'S/N'},
            {data: 'title', name: 'title'},
            {data: 'document', name: 'document', searchable: false,
                "render":function(data, type, row) {
                    var documentName = row.document;
                    if ( documentName == 'pdf' ) {
                        return '<img src="'+'{!! url('/asset/notice-board/icon-pdf.png') !!}/'+'" style="width:100px; height:100px"/>';
                    } else {
                        return '<img src="'+'{!! url('/asset/notice-board/icon-image.png') !!}'+'" style="width:100px; height:100px"/>';
                    }
                }
            },
            {data: 'files', name: 'files', searchable: false,
                render: function(data){
                    return htmlDecode(data);
                }},
            {data: 'status', name: 'status'},
            {data: 'created', name: 'created'},
            {data: 'action', name: 'action', searchable: false},
        ],"ordering": false,
    });
    function htmlDecode(data) {
        var txt = document.createElement('textarea');
        txt.innerHTML = data;
        return txt.value
    }

    $(document).on('click','.delete-notice',function(e) {
        var noticeId = $(this).data('id');

        e.preventDefault();
        swal({
            title: "Are you sure?",
            text: "Once deleted, you will not be able to recover!",
            icon: "warning",
            buttons: [
                'No, cancel it!',
                'Yes, I am sure!'
            ],
            dangerMode: true,
        }).then(function(isConfirm) {
            if (isConfirm) {
                console.log("DDDD",noticeId, $(this).data('id'));

                $.ajax({
                    type: "POST",
                    url: "{!! route('admin.notice.delete') !!}",
                    data: {id: noticeId},
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (data) {
                        location.reload();
                       // noticeTable.draw();
                        //duplicate = data;
                    }
                });

            }
        });
    });
    $(document).on('click','.status-notice',function(e) {

        var noticeId = $(this).data('id');



        e.preventDefault();

        swal({

            title: "Are you sure?",

            text: "You Want Change Status!",

            icon: "warning",

            buttons: [

                'No, cancel it!',

                'Yes, I am sure!'

            ],

            dangerMode: true,

        }).then(function(isConfirm) {

            if (isConfirm) {

                //console.log("DDDD",noticeId, $(this).data('id'));



                $.ajax({

                    type: "POST",

                    url: "{!! route('admin.notice.status_change') !!}",

                    data: {id: noticeId},

                    headers: {

                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

                    },

                    success: function (data) {
                           
                        location.reload();

                       // noticeTable.draw();

                        //duplicate = data;

                    }

                });



            }

        });

    });

});
</script>