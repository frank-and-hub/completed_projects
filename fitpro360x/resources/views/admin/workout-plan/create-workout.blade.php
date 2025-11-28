@extends('admin.layout.index')
@section('content')
@section('admin-title', 'Workout-plan')

    <div class="container-fluid">
        <div class="pate-content-wrapper">
            <div class="page-title-row ">
                <h5>Create Workout</h5>
            </div>
            <div class="m-card-min-hight">
                <div class="row create-workout-form">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="exampleInputTitle" class="form-label">Title</label>
                            <div class="position-relative">
                                <input type="text" class="form-control form-control-lg" placeholder="Title">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="exampleInputDuration" class="form-label">Duration (Weeks)</label>
                            <div class="position-relative">
                                <input type="text" class="form-control form-control-lg" placeholder="Enter">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="exampleInputDuration" class="form-label">Goal</label>
                            <div class="position-relative">
                                <input type="text" class="form-control form-control-lg" placeholder="Enter">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="" class="text-14 font-400">Location</label>
                        <div class="pt-2">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="inlineRadioOptions" id="inlineRadio1"
                                    value="option1 checked">
                                <label class="form-check-label" for="inlineRadio1">Home</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="inlineRadioOptions" id="inlineRadio2"
                                    value="option2">
                                <label class="form-check-label" for="inlineRadio2">Gym</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="exampleFormControlTextarea1" class="form-label">Description</label>
                            <textarea class="form-control" id="exampleFormControlTextarea1" rows="3"
                                placeholder="Enter"></textarea>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="">Create Exercises</label>
                    <div class="create-exercise-section">
                        <p class="text-14 font-500">Week 1</p>
                        <hr style="  margin: 1rem -20px; color: #cccccc; border: 0; border-top: var(--bs-border-width) solid; opacity: .25;">
                        <div class="pink-bg mb-3">
                            <p class="text-14 font-500">Day 1</p>
                            <hr style="  margin: 1rem -10px; color: #cccccc; border: 0; border-top: var(--bs-border-width) solid; opacity: .25;">
                            <div class="filter-row-search justify-content-between">
                                <div class="mb-3 headerserarch ">
                                    <input type="text" class="form-control" id="exampleInputEmail1"
                                        placeholder="Search">
                                    <i class="inputicon"><img src="images/search.svg"></i>
                                </div>
                                <div>
                                    <span>Rest Day</span>
                                    <label class="switch me-2">
                                        <input type="checkbox">
                                        <span class="slider">
                                        </span>
                                    </label>
                                    <a href="" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">Add Exercise</a>
                                </div>
                            </div>

                            <div class="table-responsive mt-2">
                                <table class="table align-middle">
                                    <thead>
                                        <tr>
                                            <th>S.No.</th>
                                            <th>Exercise</th>
                                            <th>Reps</th>
                                            <th>Sets</th>
                                            <th>Rest time</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>1.</td>
                                            <td>Hamstring Curls</td>
                                            <td>8</td>
                                            <td>4</td>
                                            <td>10-15 mins</td>
                                            <td>
                                                <a href="#"><img src="images/viewbtn.svg"></a>
                                                <a href="#"><img src="images/edittbtn.svg"></a>
                                                <a href="#"><img src="images/deletebtn.svg"></a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>2.</td>

                                            <td>RDL (DB)</td>
                                            <td>6</td>
                                            <td>8</td>
                                            <td>00-05 mins</td>
                                            <td>
                                                <a href="#"><img src="images/viewbtn.svg"></a>
                                                <a href="#"><img src="images/edittbtn.svg"></a>
                                                <a href="#"><img src="images/deletebtn.svg"></a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>3.</td>

                                            <td>Cable Kick Backs</td>
                                            <td>3</td>
                                            <td>6</td>
                                            <td>05-10 mins</td>
                                            <td>
                                                <a href="#"><img src="images/viewbtn.svg"></a>
                                                <a href="#"><img src="images/edittbtn.svg"></a>
                                                <a href="#"><img src="images/deletebtn.svg"></a>
                                            </td>
                                        </tr>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="">
                        <a href="#" class="btn btn-outline-primary w-100">Add More Exercise</a>
                    </div>
                    </div>

                </div>
             
                    <div class="create-exercise-section mb-3">
                        <p class="text-14 font-500">Week 2</p>
                        <hr style="  margin: 1rem -20px; color: #cccccc; border: 0; border-top: var(--bs-border-width) solid; opacity: .25;">
                        <div class="pink-bg mb-2">
                            <p class="text-14 font-500">Day 2</p>
                            <hr style="  margin: 1rem -10px; color: #cccccc; border: 0; border-top: var(--bs-border-width) solid; opacity: .25;">
                            <div class="filter-row-search justify-content-between">
                                <div class="mb-3 headerserarch ">
                                    <input type="text" class="form-control" id="exampleInputEmail1"
                                        placeholder="Search">
                                    <i class="inputicon"><img src="images/search.svg"></i>
                                </div>
                                <div>
                                    <span>Rest Day</span>
                                    <label class="switch me-2">
                                        <input type="checkbox">
                                        <span class="slider">
                                        </span>
                                    </label>
                                    <a href="" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">Add Exercise</a>
                                </div>
                            </div>

                            <div class="table-responsive mt-2">
                                <table class="table align-middle">
                                    <thead>
                                        <tr>
                                            <th>S.No.</th>
                                            <th>Exercise</th>
                                            <th>Reps</th>
                                            <th>Sets</th>
                                            <th>Rest time</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>1.</td>
                                            <td>Hamstring Curls</td>
                                            <td>8</td>
                                            <td>4</td>
                                            <td>10-15 mins</td>
                                            <td>
                                                <a href="#"><img src="images/viewbtn.svg"></a>
                                                <a href="#"><img src="images/edittbtn.svg"></a>
                                                <a href="#"><img src="images/deletebtn.svg"></a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>2.</td>

                                            <td>RDL (DB)</td>
                                            <td>6</td>
                                            <td>8</td>
                                            <td>00-05 mins</td>
                                            <td>
                                                <a href="#"><img src="images/viewbtn.svg"></a>
                                                <a href="#"><img src="images/edittbtn.svg"></a>
                                                <a href="#"><img src="images/deletebtn.svg"></a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>3.</td>

                                            <td>Cable Kick Backs</td>
                                            <td>3</td>
                                            <td>6</td>
                                            <td>05-10 mins</td>
                                            <td>
                                                <a href="#"><img src="images/viewbtn.svg"></a>
                                                <a href="#"><img src="images/edittbtn.svg"></a>
                                                <a href="#"><img src="images/deletebtn.svg"></a>
                                            </td>
                                        </tr>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="pink-bg mb-3">
                            <p class="text-14 font-500">Day 2</p>
                            <hr style="  margin: 1rem -10px; color: #cccccc; border: 0; border-top: var(--bs-border-width) solid; opacity: .25;">
                            <div class="filter-row-search justify-content-between">
                                <div class="mb-3 headerserarch ">
                                    <input type="text" class="form-control" id="exampleInputEmail1"
                                        placeholder="Search">
                                    <i class="inputicon"><img src="images/search.svg"></i>
                                </div>
                                <div>
                                    <span>Rest Day</span>
                                    <label class="switch me-2">
                                        <input type="checkbox">
                                        <span class="slider">
                                        </span>
                                    </label>
                                    <a href="" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">Add Exercise</a>
                                </div>
                            </div>
                            <div class="text-center mt-2">
                            <p class="text-14 text-grey font-500">Rest Day Enabled</p>
                        </div>
                        </div>
                        <div class="">
                        <a href="#" class="btn btn-outline-primary w-100">Add More Exercise</a>
                    </div>
                    </div>
                 <div class="mb-3">
                    <a href="#" class="btn btn-primary w-100 ">Add New Week</a>
                 </div>
                 <div class="text-end mb-3">
                    <a href="" class="btn btn-outline-primary btn-sm me-2">Cancel</a>
                <a href="" class="btn btn-primary btn-sm" >Next</a>
                </div>
               
            </div>
        </div>
    </div>


<!-- --MODAL-- -->
  <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="exampleModalLabel">Add Exercise</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div class="row">
            <div class="mb-3 col-md-12">
                <label for="exampleInputExercise" class="form-label">Select Exercise</label>
                <select class="form-select" aria-label="Default select example">
                <option selected>Select</option>
                <option value="1">One</option>
                <option value="2">Two</option>
                <option value="3">Three</option>
              </select>
              </div>
              
              <div class="mb-3 col-md-6">
                <label for="exampleInputReps" class="form-label">Reps</label>
                  <input type="text" class="form-control " placeholder="Reps">
              </div>
              <div class="mb-3 col-md-6">
                <label for="exampleInputSets" class="form-label">Sets</label>
                  <input type="text" class="form-control " placeholder="Sets">
              </div>
              <div class="mb-3 col-md-12">
                <label for="exampleInputResttime" class="form-label">Rest Time (Seconds)</label>
                  <input type="text" class="form-control " placeholder="Enter">
              </div>
            </div>
        </div>
        <div class="modal-footer ">
            <div class="text-center">
          <button type="button" class="btn btn-primary ">Save</button>
        </div>
        </div>
      </div>
    </div>
  </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/custom.js"></script>
</body>

</html>
@endsection