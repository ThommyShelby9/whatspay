<!DOCTYPE html>
<html lang="en">
@include('admin.header')
<body>
@include('admin.loader')
@include('admin.tap')
<!-- page-wrapper Start-->
<div class="page-wrapper compact-wrapper" id="pageWrapper">
  @include('admin.pageheader')                             -->
  <!-- Page Body Start-->
  <div class="page-body-wrapper">
    @include('admin.sidebar')
    <div class="page-body">
      <div class="container-fluid">
        <div class="page-title">
          <div class="row">
            <div class="col-xl-4 col-sm-7 box-col-3">
              <h3>{{$pagetilte}}</h3>
            </div>
          </div>
        </div>
      </div>
      <!-- Container-fluid starts-->
      @section('pagecontent')
      @show
      <!-- Container-fluid Ends-->
    </div>
    <!-- footer start-->
    @include('admin.footer')
  </div>
</div>

@include('admin.js')
</body>
</html>
