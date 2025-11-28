@extends('admin.layout.index')
@section('content')
@section('admin-title', 'Meal Plans')

<div class="container-fluid pt-3 bg">
    <div class="page-content-wrapper">
        <div class="page-title-row justify-content-between">
            <h4 class="mb-3 ms-3">Meal Plans</h4>
            <a href="" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMealsPlanModal">
                <img src="{{ asset('assets/images/add-circle.svg') }}" alt="plus-icon"> Add Meal Plans
            </a>
        </div>
        <div class="white-body-card ">
            <div class="filter-row align-items-center mb-3 d-flex">
                <form action="{{ route('admin.mealsPlanIndex') }}" method="GET" class="d-flex align-items-center">
                    <div class="tp-search-input me-2" style="flex-grow: 1;">
                        <input type="text" class="form-control" placeholder="Search by title&description" name="search" value="{{ request('search') }}">
                        <span class="input-icon">
                            <img src="{{ asset('assets/images/search.svg') }}" alt="Search">
                        </span>
                    </div>
                    <div class="col-md-4">
                        <select class="form-select" name="meal_type">
                            <option value="">All Meal Types</option>
                            <option value="Breakfast" {{ request('meal_type') == 'Breakfast' ? 'selected' : '' }}>Breakfast</option>
                            <option value="Lunch" {{ request('meal_type') == 'Lunch' ? 'selected' : '' }}>Lunch</option>
                            <option value="Dinner" {{ request('meal_type') == 'Dinner' ? 'selected' : '' }}>Dinner</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-outline-primary search-btn mx-2">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="window.location.href='{{ route('admin.mealsPlanIndex') }}'">
                        <i class="fas fa-times"></i> Reset
                    </button>

                    <!-- Hidden field to maintain the current limit -->
                    <input type="hidden" name="limit" value="{{ request('limit', 10) }}">
                </form>

                <div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th style="width:5%">S. No.</th>
                            <th style="width:20%">Meal&nbsp;&nbsp;Title</th>
                            <th style="width:20%">Meal&nbsp;&nbsp;Type</th>
                            <th style="width:40%">Description</th>
                            <th style="width:15%">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($mealPlans as $singleInfo)
                            <tr>
                                <td>{{ ($mealPlans->currentPage() - 1) * $mealPlans->perPage() + $loop->iteration }}</td>
                                <td>{{ $singleInfo->title }}</td>
                                <td>
                                    @php
                                        $types = [1 => 'Breakfast', 2 => 'Lunch', 3 => 'Dinner'];
                                    @endphp
                                    {{ $types[$singleInfo->type] ?? 'Unknown' }}
                                </td>
                                <td>{{ $singleInfo->description }}</td>
                                <td>
                                    <div style="display: flex; gap: 6px; align-items: center;">
                                        {{-- <a href="javascript:void(0)" class="view-mealsPlan" data-id="{{ $singleInfo->id }}">
                                            <img src="{{ asset('assets/images/viewbtn.svg') }}" alt="View" title="View">
                                        </a> --}}
                                        <a href="javascript:void(0)" class="edit-mealsPlan" data-id="{{ $singleInfo->id }}">
                                            <img src="{{ asset('assets/images/edittbtn.svg') }}" alt="Edit" title="Edit">
                                        </a>
                                        <a href="javascript:void(0)" class="delete-mealsPlan" data-id="{{ $singleInfo->id }}">
                                            <img src="{{ asset('assets/images/delete-circle-btn.svg') }}" alt="Delete" title="Delete">
                                        </a>
                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center"><strong>No data found</strong></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
              <!-- Pagination Section -->
              <div class="table-result mt-3">
                <select id="paginationLimit" class="form-select me-3" style="width:70px;">
                    <option value="10" {{ request('limit', 10) == 10 ? 'selected' : '' }}>10</option>
                    <option value="15" {{ request('limit') == 15 ? 'selected' : '' }}>15</option>
                    <option value="20" {{ request('limit') == 20 ? 'selected' : '' }}>20</option>
                </select>
                    <p class="mb-0">
                    @if ($mealPlans->total() > 0)
                        Showing {{ $mealPlans->firstItem() }} to {{ $mealPlans->lastItem() }} of {{ $mealPlans->total() }} entries
                    @else
                        No entries found
                    @endif
                </p>
                <nav class="ms-auto">
                    {{ $mealPlans->links('pagination.custom') }}
                </nav>
            </div>

        </div>
    </div>
</div>

<!-- Add MealsPlan Modal -->
<!-- Add MealsPlan Modal -->
<div class="modal fade" id="addMealsPlanModal" tabindex="-1" aria-labelledby="addMealsPlanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="addMealsPlanModalLabel">Add Meal</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addMealsPlanForm" action="{{ route('admin.mealsPlanSave') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <!-- Meal Type -->
                        <div class="col-md-12 mb-4">
                            <label class="text-14 font-400">Meal Type<span style="color:red;">*</span></label>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="type" id="breakfast" value="1" checked required>
                                    <label class="form-check-label" for="breakfast">Breakfast</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="type" id="lunch" value="2" required>
                                    <label class="form-check-label" for="lunch">Lunch</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="type" id="dinner" value="3" required>
                                    <label class="form-check-label" for="dinner">Dinner</label>
                                </div>
                            </div>
                        </div>

                        <!-- Diet Preference -->
                        <div class="col-md-12 mb-3">
                            <label class="text-14 font-400">Diet Preference<span style="color:red;">*</span></label>
                            <div>
                                
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="diet_preference" value="2" id="Vegetarian" checked required>
                                    <label class="form-check-label" for="Vegetarian">Veg</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="diet_preference" value="3" id="NonVegetarian" required>
                                    <label class="form-check-label" for="NonVegetarian">Non-Veg</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="diet_preference" value="4" id="Keto" required>
                                    <label class="form-check-label" for="Keto">Keto</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="diet_preference" value="1" id="Vegan" required>
                                    <label class="form-check-label" for="Vegan">Vegan</label>
                                </div>
                            </div>
                        </div>

                        <!-- Meal Title -->
                        <div class="mb-3 col-md-12">
                            <label for="title" class="form-label">Meal Title<span style="color:red;">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" placeholder="Enter meal title" maxlength="50" required>
                        </div>

                        <!-- Description -->
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="description" class="form-label">Description<span style="color:red;">*</span></label>
                                <textarea class="form-control" id="description" name="description" rows="5"
                                    placeholder="Enter meal description" maxlength="1000" required></textarea>
                            </div>
                        </div>

                        <!-- Image Upload -->
                        <div class="mb-3 col-md-12">
                            <div id="addImagePreviewContainer" style="position: relative; display: none;">
                                <img id="addImagePreview" src="#" alt="Image Preview" class="img-thumbnail" style="width: 100px; height: 100px; object-fit: cover;">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="image" class="form-label">Image<span style="color:red;">*</span></label>
                                <input class="form-control form-control-lg" id="image" name="image" type="file" accept=".jpg,.jpeg,.png" onchange="validateImageSize(this); previewAddImage(this);" required>
                                <small class="text-muted">Please upload a 120x120 pixel image for best view.</small>
                                <div id="imageError" class="text-danger mt-1" style="display: none;"></div>
                            </div>
                        </div>

                        <!-- Ingredients Section -->
                        <div class="col-md-12">
                            <div id="ingredientsContainer">
                                <div class="ingredient-row mb-2">
                                    <div class="row mb-1">
                                        <div class="col-md-5"><strong>Ingredients<span style="color:red;">*</span></strong></div>
                                        <div class="col-md-5"><strong>Quantity (with unit)<span style="color:red;">*</span></strong></div>
                                        <div class="col-md-2"></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-5">
                                            <input type="text" name="ingredients[0][name]" class="form-control" placeholder="Enter ingredient" maxlength="100" required>
                                        </div>
                                        <div class="col-md-5">
                                            <input type="text" name="ingredients[0][quantity]" class="form-control" placeholder="Enter quantity" maxlength="50" required>
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" id="addMoreIngredients" class="btn btn-outline-primary">Add+</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="ingredientsError" class="text-danger mt-1" style="display: none;"></div>
                        </div>

                        <!-- Nutrition Information -->
                        <p class="text-16 font-500 mb-1 mt-3">Nutrition's (/100gm)</p>
                        <div class="mb-3 col-md-6">
                            <label for="proteins" class="form-label">Protein (g)<span style="color:red;">*</span></label>
                            <input type="number" class="form-control" id="proteins" name="proteins" placeholder="Enter protein" min="0" max="100"  step="0.001" required>
                            <div id="proteinError" class="text-danger mt-1" style="display: none;"></div>
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="carbohydrates" class="form-label">Carbohydrates (g)<span style="color:red;">*</span></label>
                            <input type="number" class="form-control" id="carbohydrates" name="carbohydrates" placeholder="Enter carbohydrate" min="0" max="100" step="0.001"  required>
                            <div id="carbohydratesError" class="text-danger mt-1" style="display: none;"></div>
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="fat" class="form-label">Fat (g)<span style="color:red;">*</span></label>
                            <input type="number" class="form-control" id="fat" name="fat" placeholder="Enter fat" min="0" max="100" step="0.001" required>
                            <div id="fatError" class="text-danger mt-1" style="display: none;"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Meal Plan Modal -->
<div class="modal fade" id="editMealPlansModal" tabindex="-1" aria-labelledby="editMealPlansModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="editMealPlansModalLabel">Edit Meal</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editMealsPlanForm" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <!-- Type -->
                        <div class="col-md-12 mb-4">
                            <label class="text-14 font-400">Meal Type<span style="color:red;">*</span></label>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="type" value="1" id="editTypeBreakfast">
                                    <label class="form-check-label" for="editTypeBreakfast">Breakfast</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="type" value="2" id="editTypeLunch">
                                    <label class="form-check-label" for="editTypeLunch">Lunch</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="type" value="3" id="editTypeDinner">
                                    <label class="form-check-label" for="editTypeDinner">Dinner</label>
                                </div>
                            </div>
                        </div>

                        <!-- Diet Preference -->
                        <div class="col-md-12 mb-3">
                            <label class="text-14 font-400">Diet Preference<span style="color:red;">*</span></label>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="diet_preference" value="2" id="editVegetarian">
                                    <label class="form-check-label" for="editVegetarian">Veg</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="diet_preference" value="3" id="editNonVegetarian">
                                    <label class="form-check-label" for="editNonVegetarian">Non-Veg</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="diet_preference" value="4" id="editKeto">
                                    <label class="form-check-label" for="editKeto">Keto</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="diet_preference" value="1" id="editVegan">
                                    <label class="form-check-label" for="editVegan">Vegan</label>
                                </div>
                            </div>
                        </div>

                        <!-- Title -->
                        <div class="mb-3 col-md-12">
                            <label class="form-label">Meal Title<span style="color:red;">*</span></label>
                            <input type="text" class="form-control" id="editMealTitle" name="title" placeholder="Enter meal title" maxlength="50">
                        </div>

                        <!-- Description -->
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Description<span style="color:red;">*</span></label>
                                <textarea class="form-control" id="editMealDescription" name="description" rows="3" placeholder="Enter description" maxlength="1000"></textarea>
                            </div>
                        </div>

                        <div class="mb-3 col-md-12">
                            <div id="imagePreviewContainer" style="position: relative;">
                                <img id="editCurrentImage" src="" alt="Current Image"  style="width: 100px; height: 100px; object-fit: cover;display: none;"  class="img-thumbnail">
                                <img id="editImagePreview" src="#" alt="New Image Preview" class="img-thumbnail" style="width: 100px; height: 100px; object-fit: cover;display: none;">
                            </div>
                        </div>
                        <!-- Image -->
                        <div class="col-md-12 uploa">
                            <div class="mb-3">
                                <label class="form-label">Image</label>
                                <input class="form-control form-control-lg" id="editMealImage" name="image" type="file" accept=".jpg,.jpeg,.png" onchange="validateImageSize(this); previewEditImage(this);">
                                <small class="text-muted">Please upload a 120x120 pixel image for best view.</small>
                               <div id="imageError" class="text-danger mt-1" style="display: none;"></div>
                                <div id="currentImagePreview" class="mt-2">
                                    {{-- <img id="editCurrentImage" src="" alt="Current Image" style="max-width: 100px; display: none;"> --}}
                                    {{-- <p id="currentImageName" class="mb-0"></p> --}}
                                </div>
                            </div>
                        </div>

                        <!-- Ingredients -->
                        <div class="col-md-12">
                            {{-- <p class="text-16 font-500 mb-1">Ingredients</p> --}}
                            <div id="editIngredientsContainer"></div>
                            {{-- <button type="button" id="editAddMoreIngredients" class="btn btn-outline-primary w-100 mt-2">Add More Ingredients</button> --}}
                        </div>

                        <!-- Nutrition -->
                        <p class="text-16 font-500 mb-1 mt-3">Nutrition's (/100gm)</p>
                        <div class="mb-3 col-md-6">
                            <label class="form-label">Protein<span style="color:red;">*</span></label>
                            <input type="number" class="form-control" id="editProteins" name="proteins" placeholder="Enter protein"  step="0.001" min="0" max="100">
                        </div>
                        <div class="mb-3 col-md-6">
                            <label class="form-label">Carbohydrates<span style="color:red;">*</span></label>
                            <input type="number" class="form-control" id="editCarbohydrates" name="carbohydrates" placeholder="Enter carbohydrates" step="0.001" min="0" max="100">
                        </div>
                        <div class="mb-3 col-md-12">
                            <label for="fat" class="form-label">Fat<span style="color:red;">*</span></label>
                            <input type="number" class="form-control" id="editFat" name="fat" placeholder="Enter fat"  step="0.001" min="0" max="100">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <div class="text-center w-100">
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteMealsPlanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <input type="hidden" id="deleteMealsPlanId">
                <h5 class="mb-3">Are you sure you want to delete this meal plan?</h5>
                <button type="button" class="btn btn-outline-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-outline-primary" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <h5 class="mb-3" id="successMessage"></h5>
                <button type="button" class="btn btn-primary" id="successModalOk">OK</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>

    $(document).ready(function() {
        // Handle pagination limit change
        $('#paginationLimit').on('change', function() {
            let limit = $(this).val();
            let url = new URL(window.location.href);
            url.searchParams.set('limit', limit);
            url.searchParams.set('page', 1);

            const search = $('input[name="search"]').val();
            if (search) {
                url.searchParams.set('search', search);
            }
            window.location.href = url.toString();
        });

      // Replace your current addMoreIngredients click handler with this:
  // Add mode - limit to 25 ingredients and hide button when limit reached
$(document).off('click', '#addMoreIngredients').on('click', '#addMoreIngredients', function() {
    const container = $('#ingredientsContainer');
    const currentCount = container.find('.ingredient-row').length;

    if (currentCount >= 25) {
        $(this).hide();
        return;
    }

    const newRow = `
        <div class="ingredient-row mb-2">
            <div class="row">
                <div class="col-md-5">
                    <input type="text" name="ingredients[${currentCount}][name]" class="form-control" placeholder="Enter ingredient" maxlength="100" required>
                </div>
                <div class="col-md-5">
                    <input type="text" name="ingredients[${currentCount}][quantity]" class="form-control" placeholder="Enter quantity" maxlength="50" required>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-outline-primary remove-ingredient">-</button>
                </div>
            </div>
        </div>
    `;
    container.append(newRow);

    if (container.find('.ingredient-row').length >= 25) {
        $(this).hide();
    }
});

// Update the reset function to show the add button again
$('#addMealsPlanModal').on('hidden.bs.modal', function () {
    console.log(222222);
    $('#addMealsPlanForm')[0].reset();
    $('#addMealsPlanForm').find('.is-invalid').removeClass('is-invalid');
    $('#addMealsPlanForm').find('.invalid-feedback').remove();
    $('#addImagePreview').attr('src', '#');
    $('#addImagePreviewContainer').hide();
    $('#ingredientsContainer').html(`
        <div class="ingredient-row mb-2">
            <div class="row mb-1">
                <div class="col-md-5"><strong>Ingredients<span style="color:red;">*</span></strong></div>
                <div class="col-md-5"><strong>Quantity (with unit)<span style="color:red;">*</span></strong></div>
                <div class="col-md-2"></div>
            </div>
            <div class="row">
                <div class="col-md-5">
                    <input type="text" name="ingredients[0][name]" class="form-control" placeholder="Enter ingredient" maxlength="100">
                </div>
                <div class="col-md-5">
                    <input type="text" name="ingredients[0][quantity]" class="form-control" placeholder="Enter quantity" maxlength="50">
                </div>
                <div class="col-md-2">
                    <button type="button" id="addMoreIngredients" class="btn btn-outline-primary">Add+</button>
                </div>
            </div>
        </div>
    `);
});

// Also handle the case when ingredients are removed
$(document).on('click', '.remove-ingredient', function() {
    $(this).closest('.ingredient-row').remove();
    reindexIngredients();
    if ($('#ingredientsContainer').find('.ingredient-row').length < 25) {
        $('#addMoreIngredients').show();
    }
});

// Helper function to reindex ingredients after removal
function reindexIngredients() {
    $('#ingredientsContainer .ingredient-row').each(function(index) {
        $(this).find('input[name^="ingredients"]').each(function() {
            const name = $(this).attr('name').replace(/\[\d+\]/, `[${index}]`);
            $(this).attr('name', name);
        });
    });
}

        // Handle edit button click
        $(document).on('click', '.edit-mealsPlan', function() {



            // Reset the form
            console.log(3333);
            $('#addMealsPlanForm')[0].reset();
            $('#editMealPlansModal').find('.is-invalid').removeClass('is-invalid');
            $('#editMealPlansModal').find('.invalid-feedback').remove();
            $('#addImagePreview').attr('src', '#');
            $('#addImagePreviewContainer').hide();
            $('#editIngredientsContainer').empty(); 







            var mealsPlanId = $(this).data('id');

            $.ajax({
                url: "{{ route('admin.mealsPlanEdit', '') }}/" + mealsPlanId,
                type: 'GET',
                success: function(response) {
                    // Set form action URL
                    $('#editMealsPlanForm').attr('action', "{{ route('admin.mealsPlanUpdate', '') }}/" + mealsPlanId);

                    // Fill basic form fields
                    $('#editMealTitle').val(response.title);
                    $('#editMealDescription').val(response.description);
                    $('#editProteins').val(response.proteins);
                    $('#editCarbohydrates').val(response.carbohydrates);
                    $('#editFat').val(response.fat);

                    // Set type radio button
                    $(`input[name="type"][value="${response.type}"]`).prop('checked', true);

                    // Set diet preference radio button
                    $(`input[name="diet_preference"][value="${response.diet_preference}"]`).prop('checked', true);

                    // Handle image preview
                    if (response.image) {
                        $('#editCurrentImage').attr('src', "{{ asset('') }}/" + response.image).show();
                        $('#currentImageName').text(response.image.split('/').pop());
                    } else {
                        $('#editCurrentImage').hide();
                        $('#currentImageName').text('No image uploaded');
                    }

                        //  if (response.image) {
                        //     var imageUrl = "{{ asset('') }}" + response.image;
                        //     $('#existingImage').attr('src', imageUrl);
                        //     $('#imagePreviewContainer').show();
                        // } else {
                        //     $('#existingImage').attr('src', '');
                        //     $('#imagePreviewContainer').hide();
                        // }

                    // Handle ingredients - clear existing first
                    $('#editIngredientsContainer').empty();

                    // Add header row for ingredients
                    $('#editIngredientsContainer').append(`
                        <div class="ingredient-row mb-2">
                            <div class="row mb-1">
                                <div class="col-md-6"><strong>Ingredients<span style="color:red;">*</span></strong></div>
                                <div class="col-md-5"><strong>Quantity (with unit)<span style="color:red;">*</span></strong></div>
                                <div class="col-md-1"></div>
                            </div>
                        </div>
                    `);

                    // Add ingredients rows
                    if (response.ingredients && response.ingredients.length > 0) {
                        response.ingredients.forEach(function(ingredient, index) {
                            addEditIngredientRow(ingredient.name, ingredient.quantity, index);
                        });

                        if (response.ingredients.length >= 25) {
                            $('.add-edit-ingredient').hide();
                        }
                    } else {
                        // Add at least one empty row if no ingredients exist
                        addEditIngredientRow('', '', 0);
                    }

                    $('#editMealPlansModal').modal('show');
                },
                error: function(xhr) {
                    alert('An error occurred while fetching meal plan data.');
                }
            });
        });

       // Helper function to add ingredient row in edit form
       function addEditIngredientRow(name = '', quantity = '', index = null) {
            if (index === null) {
                index = $('#editIngredientsContainer .ingredient-row').length;
            }

            const showAddButton = (index < 24); // Show add button only if we have less than 25 ingredients

            const row = `
                <div class="ingredient-row mb-2" data-index="${index}">
                    <div class="row">
                        <div class="col-md-5">
                            <input type="text" name="ingredients[${index}][name]" value="${escapeHtml(name)}" class="form-control" placeholder="Enter ingredient" maxlength="100">
                        </div>
                        <div class="col-md-5">
                            <input type="text" name="ingredients[${index}][quantity]" value="${escapeHtml(quantity)}" class="form-control" placeholder="Enter quantity" maxlength="50">
                        </div>
                        <div class="col-md-2">
                            ${index > 0 ? '<button type="button" class="btn btn-outline-danger remove-edit-ingredient">-</button>' :
                            (showAddButton ? '<button type="button" class="btn btn-outline-primary add-edit-ingredient">Add+</button>' : '')}
                        </div>
                    </div>
                </div>
            `;
            $('#editIngredientsContainer').append(row);
        }

// Helper function to escape HTML (for security)
        function escapeHtml(unsafe) {
            if (unsafe === undefined || unsafe === null) return '';
            return unsafe.toString()
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        // Add more ingredients in edit form
        $(document).on('click', '.add-edit-ingredient', function() {
            const container = $('#editIngredientsContainer');
            const currentCount = container.find('.ingredient-row').length - 1; // Subtract 1 for header row

            if (currentCount >= 25) {
                $('.add-edit-ingredient').hide();
                return;
            }

            const index = container.find('.ingredient-row').length;
            addEditIngredientRow('', '', index);

            if (container.find('.ingredient-row').length - 1 >= 25) {
                $('.add-edit-ingredient').hide();
            }
        });


        // Remove ingredient row in edit form
        $(document).on('click', '.remove-edit-ingredient', function() {
            $(this).closest('.ingredient-row').remove();
            reindexEditIngredients();
            if ($('#editIngredientsContainer').find('.ingredient-row').length - 1 < 25) { // Subtract 1 for header row
                $('.add-edit-ingredient').show();
            }
        });

        // Function to reindex ingredients after deletion
        function reindexEditIngredients() {
            $('#editIngredientsContainer .ingredient-row').each(function(newIndex) {
                $(this).attr('data-index', newIndex);
                $(this).find('input[name^="ingredients["]').each(function() {
                    const name = $(this).attr('name').replace(/\[\d+\]/, `[${newIndex}]`);
                    $(this).attr('name', name);
                });
            });
        }

        // Initialize edit form with existing ingredients
        function initializeEditIngredients(ingredients) {
            $('#editIngredientsContainer').empty();
            if (ingredients && ingredients.length > 0) {
                ingredients.forEach((ingredient, index) => {
                    addEditIngredientRow(ingredient.name, ingredient.quantity, index);
                });
            } else {
                // Add at least one empty row if no ingredients exist
                addEditIngredientRow('', '', 0);
            }
        }
        // Helper function to reindex ingredients in edit form
        function reindexEditIngredients() {
            // Skip the first row (header) and reindex the rest
            $('#editIngredientsContainer .ingredient-row').not(':first').each(function(index) {
                $(this).find('input[name^="ingredients"]').each(function() {
                    const name = $(this).attr('name');
                    const newName = name.replace(/\[\d+\]/, `[${index}]`);
                    $(this).attr('name', newName);
                });

                // Show/hide remove button based on index
                const removeBtn = $(this).find('.remove-edit-ingredient');
                if (index === 0) {
                    removeBtn.hide();
                } else {
                    removeBtn.show();
                }
            });
        }

        // Handle success modal OK button click
        $(document).on('click', '#successModalOk', function() {
            $('#successModal').modal('hide');
            location.reload();
        });

        // Handle add form submission
        $('#addMealsPlanForm').submit(function(e) {
            e.preventDefault();

            // Clear previous errors
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();

            // Basic client-side validation
            let isValid = true;

            // Validate meal title
            if ($('#title').val().trim() === '') {
                $('#title').addClass('is-invalid');
                $('#title').after('<div class="invalid-feedback">Meal title is required</div>');
                isValid = false;
            }

            // Validate description
            if ($('#description').val().trim() === '') {
                $('#description').addClass('is-invalid');
                $('#description').after('<div class="invalid-feedback">Meal description is required</div>');
                isValid = false;
            }

            // Validate image
            if ($('#image').get(0).files.length === 0) {
                $('#image').addClass('is-invalid');
                $('#image').after('<div class="invalid-feedback">Image is required</div>');
                isValid = false;
            }

            // Validate proteins
            if ($('#proteins').val().trim() === '') {
                $('#proteins').addClass('is-invalid');
                $('#proteins').after('<div class="invalid-feedback">Protein is required</div>');
                isValid = false;
            }

            // Validate carbohydrates
            if ($('#carbohydrates').val().trim() === '') {
                $('#carbohydrates').addClass('is-invalid');
                $('#carbohydrates').after('<div class="invalid-feedback">Carbohydrate is required</div>');
                isValid = false;
            }

            if ($('#fat').val().trim() === '') {
                $('#fat').addClass('is-invalid');
                $('#fat').after('<div class="invalid-feedback">Fat is required</div>');
                isValid = false;
            }


            // Validate ingredients
            $('input[name^="ingredients"]').each(function() {
                if ($(this).val().trim() === '') {
                    const fieldName = $(this).attr('name').includes('[name]') ? 'Ingredient' : 'Quantity';
                    $(this).addClass('is-invalid');
                    $(this).after(`<div class="invalid-feedback">${fieldName} is required</div>`);
                    isValid = false;
                }
            });

            if (!isValid) return false;

            // Create FormData object to handle file upload
            let formData = new FormData(this);

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#addMealsPlanModal').modal('hide');
                    $('#successMessage').text('A new meal plan added successfully!');
                    $('#successModal').modal('show');
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        // Validation errors from server
                        const errors = xhr.responseJSON.errors;
                        const fieldLabels = {
                            'title': 'Meal Title',
                            'description': 'Description',
                            'image': 'Image',
                            'proteins': 'Proteins',
                            'carbohydrates': 'Carbohydrates',
                            'type': 'Meal Type',
                            'diet_preference': 'Diet Preference'
                        };

                        for (const field in errors) {
                            // Handle array fields (ingredients)
                            if (field.includes('ingredients')) {
                                const match = field.match(/ingredients\.(\d+)\.(\w+)/);
                                if (match) {
                                    const index = match[1];
                                    const subfield = match[2];
                                    const input = $(`input[name="ingredients[${index}][${subfield}]"]`);
                                    const fieldName = subfield === 'name' ? 'Ingredient Name' : 'Ingredient Quantity';
                                    input.addClass('is-invalid');
                                    input.after(`<div class="invalid-feedback">${fieldName} ${errors[field][0]}</div>`);
                                }
                            } else {
                                // Regular fields
                                const input = $(`[name="${field}"]`);
                                const label = fieldLabels[field] || field;
                                input.addClass('is-invalid');
                                input.after(`<div class="invalid-feedback">${label} ${errors[field][0]}</div>`);
                            }
                        }
                    } else {
                        // Other errors
                        alert('An error occurred. Please try again.');
                    }
                }
            });
        });

        // Handle edit form submission
        $('#editMealsPlanForm').submit(function(e) {
            e.preventDefault();

            // Clear previous errors
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();

            // Basic client-side validation
            let isValid = true;

            // Validate meal title
            if ($('#editMealTitle').val().trim() === '') {
                $('#editMealTitle').addClass('is-invalid');
                $('#editMealTitle').after('<div class="invalid-feedback">Meal title is required</div>');
                isValid = false;
            }

            // Validate description
            if ($('#editMealDescription').val().trim() === '') {
                $('#editMealDescription').addClass('is-invalid');
                $('#editMealDescription').after('<div class="invalid-feedback">Description is required</div>');
                isValid = false;
            }

            // Validate meal type
            if (!$('input[name="type"]:checked').val()) {
                $('input[name="type"]').addClass('is-invalid');
                $('input[name="type"]').first().closest('div').after('<div class="invalid-feedback">Meal type is required</div>');
                isValid = false;
            }

            // Validate diet preference
            if (!$('input[name="diet_preference"]:checked').val()) {
                $('input[name="diet_preference"]').addClass('is-invalid');
                $('input[name="diet_preference"]').first().closest('div').after('<div class="invalid-feedback">Diet preference is required</div>');
                isValid = false;
            }

            // Validate proteins
            if ($('#editProteins').val().trim() === '') {
                $('#editProteins').addClass('is-invalid');
                $('#editProteins').after('<div class="invalid-feedback">Protein is required</div>');
                isValid = false;
            }

            // Validate carbohydrates
            if ($('#editCarbohydrates').val().trim() === '') {
                $('#editCarbohydrates').addClass('is-invalid');
                $('#editCarbohydrates').after('<div class="invalid-feedback">Carbohydrate is required</div>');
                isValid = false;
            }

            if ($('#editFat').val().trim() === '') {
                $('#editFat').addClass('is-invalid');
                $('#editFat').after('<div class="invalid-feedback">Fat is required</div>');
                isValid = false;
            }

            // Validate ingredients
            $('#editIngredientsContainer input[name^="ingredients"]').each(function() {
                if ($(this).val().trim() === '') {
                    const fieldName = $(this).attr('name').includes('[name]') ? 'Ingredient name' : 'Quantity';
                    $(this).addClass('is-invalid');
                    $(this).after(`<div class="invalid-feedback">${fieldName} is required</div>`);
                    isValid = false;
                }
            });

            if (!isValid) return false;

            // Create FormData object to handle file upload
            let formData = new FormData(this);

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#editMealPlansModal').modal('hide');
                    $('#successMessage').text('Meal plan updated successfully!');
                    $('#successModal').modal('show');
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        // Validation errors from server
                        const errors = xhr.responseJSON.errors;
                        const fieldLabels = {
                            'title': 'Meal Title',
                            'description': 'Description',
                            'image': 'Image',
                            'proteins': 'Proteins',
                            'carbohydrates': 'Carbohydrates',
                            'type': 'Meal Type',
                            'diet_preference': 'Diet Preference'
                        };

                        for (const field in errors) {
                            // Handle array fields (ingredients)
                            if (field.includes('ingredients')) {
                                const match = field.match(/ingredients\.(\d+)\.(\w+)/);
                                if (match) {
                                    const index = match[1];
                                    const subfield = match[2];
                                    const input = $(`input[name="ingredients[${index}][${subfield}]"]`);
                                    const fieldName = subfield === 'name' ? 'Ingredient Name' : 'Ingredient Quantity';
                                    input.addClass('is-invalid');
                                    input.after(`<div class="invalid-feedback">${fieldName} ${errors[field][0]}</div>`);
                                }
                            } else {
                                // Regular fields
                                const input = $(`[name="${field}"]`);
                                const label = fieldLabels[field] || field;
                                input.addClass('is-invalid');
                                input.after(`<div class="invalid-feedback">${label} ${errors[field][0]}</div>`);
                            }
                        }
                    } else {
                        // Other errors
                        alert('An error occurred. Please try again.');
                    }
                }
            });
        });

        // Handle delete button click
        $(document).on('click', '.delete-mealsPlan', function() {
            var mealsPlanId = $(this).data('id');
            $('#deleteMealsPlanId').val(mealsPlanId);
            $('#deleteMealsPlanModal').modal('show');
        });

        // Handle confirm delete
        $('#confirmDelete').click(function() {
            var mealsPlanId = $('#deleteMealsPlanId').val();

            $.ajax({
                url: "{{ route('admin.mealsPlanDelete', '') }}/" + mealsPlanId,
                type: 'DELETE',
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    $('#deleteMealsPlanModal').modal('hide');
                    $('#successMessage').text('Meal plan deleted successfully!');
                    $('#successModal').modal('show');
                },
                error: function(xhr) {
                    alert('An error occurred while deleting the meal plan.');
                }
            });
        });

    });
    $('#addMealsPlanModal').on('show.bs.modal', function () {
        $('#addMealsPlanForm')[0].reset(); // Reset form fields
        $('#addMealsPlanForm').find('.is-invalid').removeClass('is-invalid'); // Remove error classes
        $('#addMealsPlanForm').find('.invalid-feedback').remove(); // Remove error messages
        $('#imageError').text('').hide(); // Clear and hide custom image error
    });

        function previewAddImage(input) {
            const previewContainer = document.getElementById('addImagePreviewContainer');
            const preview = document.getElementById('addImagePreview');
            const removeBtn = document.getElementById('removeAddImageBtn');

            if (input.files && input.files[0]) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    preview.src = e.target.result;
                    previewContainer.style.display = 'block';
                }

                reader.readAsDataURL(input.files[0]);
            }

            // Remove image handler
            removeBtn.onclick = function() {
                input.value = ''; // Clear the file input
                preview.src = '#';
                previewContainer.style.display = 'none';
            };
        }

        // For Edit Form
        function previewEditImage(input) {
    const preview = document.getElementById('editImagePreview');
    const currentImage = document.getElementById('editCurrentImage');
    const removeBtn = document.getElementById('removeImageBtn');
    const deleteImageInput = document.getElementById('deleteImage');

    if (input.files && input.files[0]) {
        const reader = new FileReader();

        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';       // Show new image
            currentImage.style.display = 'none';   // Hide old image
            deleteImageInput.value = '1';          // Mark for deletion
            removeBtn.style.display = 'inline-block';
        };

        reader.readAsDataURL(input.files[0]);
    }

    removeBtn.onclick = function() {
        input.value = '';                         // Clear file input
        preview.src = '#';
        preview.style.display = 'none';           // Hide new image

        if (currentImage.src) {
            currentImage.style.display = 'block'; // Show old image
        }

        removeBtn.style.display = 'none';
        deleteImageInput.value = '0';             // Keep old image
    };
}
$(document).on('click', '.view-mealsPlan', function() {
    var mealsPlanId = $(this).data('id');

    $.ajax({
        url: "{{ route('admin.mealsPlanEdit', '') }}/" + mealsPlanId,
        type: 'GET',
        success: function(response) {
            // Set meal type text
            let mealType = '';
            switch(response.type) {
                case 1: mealType = 'Breakfast'; break;
                case 2: mealType = 'Lunch'; break;
                case 3: mealType = 'Dinner'; break;
                default: mealType = 'Unknown';
            }
            $('#viewMealType').text(mealType);

            // Set diet preference text
            let dietPreference = '';
            switch(response.diet_preference) {
                case 1: dietPreference = 'Veg'; break;
                case 2: dietPreference = 'Non-Veg'; break;
                case 3: dietPreference = 'Keto'; break;
                case 4: dietPreference = 'Vegan'; break;
                default: dietPreference = 'Unknown';
            }
            $('#viewDietPreference').text(dietPreference);

            // Set basic fields
            $('#viewMealTitle').text(response.title || 'N/A');
            $('#viewMealDescription').text(response.description || 'N/A');
            $('#viewProteins').text(response.proteins || 'N/A');
            $('#viewCarbohydrates').text(response.carbohydrates || 'N/A');
            $('#viewFat').text(response.fat || 'N/A');

            // Handle image
            if (response.image) {
                $('#viewMealImage').attr('src', "{{ asset('') }}/" + response.image).show();
            } else {
                $('#viewMealImage').hide();
            }

            // Handle ingredients
            $('#viewIngredientsContainer').empty();

            if (response.ingredients && response.ingredients.length > 0) {
                const ingredientsList = $('<ul class="list-group"></ul>');

                response.ingredients.forEach(function(ingredient) {
                    ingredientsList.append(`
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>${ingredient.name || 'N/A'}</span>
                            <span class="badge bg-primary rounded-pill">${ingredient.quantity || 'N/A'}</span>
                        </li>
                    `);
                });

                $('#viewIngredientsContainer').append(ingredientsList);
            } else {
                $('#viewIngredientsContainer').html('<div class="alert alert-info">No ingredients added</div>');
            }

            $('#viewMealPlansModal').modal('show');
        },
        error: function(xhr) {
            alert('An error occurred while fetching meal plan data.');
        }
    });
});
    function validateImageSize(input) {
        const file = input.files[0];
        const errorDiv = input.closest('.mb-3').querySelector('#imageError');
        const previewContainer = document.getElementById('addImagePreviewContainer');
        const previewImage = document.getElementById('addImagePreview');


        if (file && file.size > 2 * 1024 * 1024) {
            errorDiv.textContent = "Image size should be less than 2MB.";
            errorDiv.style.display = "block";
            input.value = ""; // clear the file input

            // Hide the preview image
            if (previewContainer && previewImage) {
                previewContainer.style.display = "none";
                previewImage.src = "#";
            }
        } else {
            errorDiv.textContent = "";
            errorDiv.style.display = "none";
        }
    }
function limitDigits(input) {
    if (input.value.length > 10) {
        input.value = input.value.slice(0, 10);
    }
}
function isDigit(event) {
    // Allow only digit keys (0–9)
    const charCode = event.which ? event.which : event.keyCode;
    return charCode >= 48 && charCode <= 57; // Only 0-9
}
$('#editMealPlansModal').on('show.bs.modal', function () {
    // Reset file input
    $('#editMealImage').val('');

    // Hide current and new image previews
    $('#editImagePreview').hide().attr('src', '#');

    // Reset image error message
    $('#imageError').hide().text('');
});
</script>
@endpush
