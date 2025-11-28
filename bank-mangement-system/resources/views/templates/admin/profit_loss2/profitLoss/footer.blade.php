
<div class="col-md-6 d-flex justify-content-between ">
    <h4 class="text_upper">Interbranch transactions </h4>
</div>
<div class="row  d-flex align-items-center">
<div class="col-md-6">
    <div class="card">
      <div class="">
        <table id="total_liability" class="table datatable-show-all">
            <thead>
                <tr>
                  <th class="text_upper">Total Liabilities</th>
                  <th>&#X20B9;{{$libalityMainAmount + $expenseAmount  }}</th>                
                </tr>
            </thead>
        </table>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card">
      <div class="">
        <table id="total_assets" class="table datatable-show-all">
          <thead>
            <tr>
              <th class="text_upper">Total Assets[E]</th>
              <th>&#X20B9;{{$AssetMainAmount}}</th>              
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
</div>
