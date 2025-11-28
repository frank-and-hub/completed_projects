@extends('layout.master')
@section('content')
@section('title', __('PocketProperty | Property'))
    <div class="content-wrapper ">
        <div class="page-header">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">Properties</a></li>
                    <li class="breadcrumb-item active" aria-current="page">List</li>
                </ol>
            </nav>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="property-header">
                    <div class="property-count" style="flex: 1">
                        Property <span class="badge bg-secondary" id="dataCount">{{count($dataTable)}}</span>
                    </div>
                    <div class="header-right-section">
                        {{-- <div class="input-group align-items-center" style="border: 1px solid #DEDEDE;border-radius: 3rem;">
                            <input type="search" class="form-control" name="search" placeholder="Search for property..." style="width:12rem;border:none;"/>
                            <span class="search-icon px-2"><i class="fas fa-search"></i></span>
                        </div> --}}

                         <div class="input-group align-items-center" style="border: 1px solid #DEDEDE; border-radius: 3rem;">
                            <input type="search" class="form-control" id="searchInput" name="search" placeholder="Search..." style="width:12rem;border:none;" />
                            <span class="search-icon px-2" id="searchIcon" style="cursor:pointer;">
                                <i class="fas fa-search"></i>
                            </span>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 my-4" id="propertyContainer">
        </div>
    </div>

    @push('custom-script')
        <script type="text/javascript">

            DATA_TABLE_PROPERTIES = `{{ route('property.dataTable',[$adminId]) }}`;

            $('input[name=search]').on('keyup', function () {
                var searchQuery = $('input[name=search]').val();
                var encodedSearchQuery = encodeURIComponent(searchQuery);
                $.post(DATA_TABLE_PROPERTIES,
                    { search: encodedSearchQuery },
                    function (response) {
                        generatePropertyCards(response?.data);
                        $('#dataCount').html(response?.data.length);
                    }, 'json'
                ).fail(function (xhr, status, error) {
                    console.error('Request failed. Status:', xhr.status, 'Error:', error);
                });
            });

            $('input[name=search]').keyup();

            function generatePropertyCards(properties) {
                const container = document.getElementById("propertyContainer");
                let html = "";
                if(properties.length === 0){
                    html += `<div class="col-12 text-center w-25 align-middle" ><img src="{{asset('/assets/admin/images/no_data_found.svg')}}" style="width: 30rem;"/></div>`;
                }else{
                    properties.forEach((property) => {
                        const address = propertyAddress(suburb = property?.suburb, town = property?.town, province = property?.province, country = property?.country);
                        const userName = property?.admin ? property?.admin?.name : null ;
                        const price = R_price(property?.financials?.price);
                        const mainImage = findMainImage(property?.media) ? `{!!Storage::url('${findMainImage(property?.media)}')!!}`: null;
                        const detail_web_route = `${window.location.href}/view/${property.id}`;
                        const hasContractOrAlreadySend = (property.sent_properties.length > 0) || (property.contract.length > 0);
                        const statusChecked = property.status ? 'checked' : '';
                        const demyImage = `{{asset("/assets/admin/images/header_banner.png")}}`;
                        const ui_web_route = `{{url('property-detail?property_id=${property?.id}&updateKey=internal')}}`;
                        const totalMatchedCount = property.sent_properties.length;
                        html += `<div class="col-sm-1 col-md-4 col-lg-4 mb-3">
                                    <div class="property-card">
                                        <div class="property-image">
                                            <img
                                            loading="lazy"
                                            src="${mainImage ?? demyImage}" alt="${truncateTitle(property?.title, 100)}"
                                            onerror="this.onerror=null; this.src='${demyImage}';" loading="lazy"
                                            >
                                            <span class="property-check-box property-status text-capitalize bg-${property.status?`success`:`secondary`}">
                                                ${property.status?`active`:`inactive`}
                                            </span>
                                            <div class="dropdown more-options">
                                                <span class="btn" style="padding:0.5rem;" type="button" data-toggle="dropdown"><i class="fas fa-ellipsis-v"></i>
                                                <span class="caret"></span></span>
                                                <ul class="dropdown-menu" role="menu" aria-labelledby="menu1">
                                                    <li role="presentation" class="dropdown-menu-item menu-divider">
                                                       <a href="${ui_web_route}" target="_blank" class="d-flex justify-content-start align-items-center w-100" >
                                                            <i class="fa fa-image edit-icon p-2" style="font-size:14px; "></i>
                                                             <p class="text-capitalize mb-0 font-weight-bold">
                                                                View
                                                            </p>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="property-details">
                                            <span class="property-type">${property?.propertyType}</span>
                                            <h5 class="property-title text-capitalize py-1 text-truncate">${property?.title}</h5>
                                            <p class="property-location mt-1 text-capitalize"><i class="fas fa-map-marker-alt"></i> ${address ?? ''}</p>
                                            <div class="property-features">
                                                <span class="feature"><i class="fas fa-bed"></i> ${property?.bedrooms} Bedroom</span>
                                                <span class="feature"><i class="fas fa-bath"></i> ${property?.bathrooms} Bathroom</span>
                                            </div>
                                            <p class="property-location mt-1 text-capitalize"><i class="fas fa-user"></i> agency / agent / landlord : ${userName ?? ``}</p>
                                            <p class="property-location mt-1 text-capitalize"><i class="fas fa-link"></i> Total Matched Tenants : ${totalMatchedCount ?? ``}</p>
                                            <div class="property-matched">
                                            </div>
                                            <div class="property-price mt-2">
                                                <span class="price">${price}</span>
                                                <a href="${detail_web_route}" class="read-more font-weight-bold">View Details <i class="fas fa-arrow-right"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>`;
                    });
                }
                container.innerHTML = html;
            }

            $(document).on('change', '.changeStatusProperty', function () {
                // alert(1);
                var $this = $(this);
                var previousStatus = $this.prop('checked');
                var dataStatus = ($this.is(':checked')) ? 'unblock' : 'block';
                var dataId = $this.data('id');
                var dataTable = $this.data('datatable');
                Swal.fire({
                    title: `Are you sure?`,
                    text: 'You want to ' + dataStatus + ' Property!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    allowOutsideClick: false,
                    confirmButtonText: 'Yes, ' + dataStatus + ' it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        statusAjaxCall(STATUS_UPDATE_ROUTE, dataId, dataStatus, dataTable);
                    } else if (result.dismiss === Swal.DismissReason.cancel) {
                        $this.prop('checked', (previousStatus == false));
                    }
                });
            });

            $(document).on('change', '.changeContractProperty', function () {
                var $this = $(this);
                var previousStatus = $this.prop('checked');
                var dataStatus = ($this.is(':checked')) ? 'unblock' : 'block';
                var dataId = $this.data('id');
                var dataTable = $this.data('datatable');
                Swal.fire({
                    title: `Are you sure?`,
                    text: `You want to ${dataStatus} Property! this property has a contract attached to it.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    allowOutsideClick: false,
                    confirmButtonText: 'Yes, ' + dataStatus + ' it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        statusAjaxCall(STATUS_UPDATE_ROUTE_CONTRACT, dataId, dataStatus, dataTable);
                    } else if (result.dismiss === Swal.DismissReason.cancel) {
                        $this.prop('checked', (previousStatus == false));
                    }
                });
            });

            function statusAjaxCall(url, dataId, dataStatus, dataTable) {
                $.post(url, {
                    'dataId': dataId,
                    'datastatus': dataStatus
                }, function (response) {
                    if (response.status) {
                        if (response.type == 1) {
                            Swal.fire(
                                'Unblock!', response.msg, 'success'
                            );
                        } else {
                            Swal.fire(
                                'Block', response.msg, 'success'
                            );
                        }
                    } else {
                        Swal.fire(
                            'Oops !', response.msg, 'error'
                        );
                        if (previousStatus == false) {
                            $this.prop('checked', true);
                        } else {
                            $this.prop('checked', false);
                        }
                    }
                    $('input[name=search]').keyup();
                    $('#' + dataTable).DataTable().ajax.reload();
                }, 'JSON').fail(function (xhr, status, error) {
                    Swal.fire(
                        'Error',
                        'Status process encountered an error. Your file is safe :)',
                        'error'
                    );
                });
            }
        </script>
    @endpush
@endsection
