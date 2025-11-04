

<!-- https://laravel.com/docs/11.x/blade#including-subviews -->

@extends('admin.layout')

@section('pagecontent')
@include('alert')

<div class="container-fluid">
  <div class="row">
    <div class="col-sm-12">
      <div class="card">
        <div class="card-header pb-0">
          <h4>{{$viewData["title"]}}</h4>
          <span>{{$viewData["subtitle"]}}</span>
        </div>
        <div class="card-body">

          <div class="form theme-form">
            <form class="theme-form" id="task_form" method="post" enctype="multipart/form-data">
            <div class="row">
              <div class="col">
                <div class="mb-3">
                  <label>Libell&eacute;</label>
                  <input class="form-control" id="name" name="name" type="text" placeholder="Libelle de la tache">
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col">
                <div class="mb-3">
                  <label>Description</label>
                  <textarea class="form-control" id="description" name="description" rows="3" placeholder="Description de la tache"></textarea>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-sm-4">
                <div class="mb-3">
                  <label>Budget</label>
                  <input class="form-control" type="number" id="budget" name="budget" placeholder="Budget previsionnel">
                </div>
              </div>
              <div class="col-sm-4">
                <div class="mb-3">
                  <label>Date de debut</label>
                  <input class="datepicker-here form-control" type="text" id="startdate" name="startdate" data-language="en">
                </div>
              </div>
              <div class="col-sm-4">
                <div class="mb-3">
                  <label>Date de fin</label>
                  <input class="datepicker-here form-control" type="text" id="enddate" name="enddate" data-language="en">
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col">
                <div class="mb-3">
                <div class="card-wrapper border rounded-3 h-100 checkbox-checked">
                  <br> <h6 class="sub-title">Quelles sont les categories de publication qui correspondent a cette demande?</h6><br>
                  @foreach($viewData["categories"] as $item)
                  <div class="payment-wrapper">
                    <div class="payment-first">
                      <div class="form-check checkbox checkbox-success">
                        <input class="form-check-input" id="c_{{$item->id}}" name="c_{{$item->id}}" type="checkbox">
                        <label class="form-check-label mb-0" for="c_{{$item->id}}">{{$item->name}} </label>
                      </div>
                    </div>
                  </div>
                  @endforeach
                </div>
                </div>
              </div>
            </div>
            <input type="hidden" name="taskfiles" id="taskfiles" value="">
            </form>
            <div class="row">
              <div class="col">
                <div class="mb-3">
                  <div id="dropZoneDiv"></div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col">
                <div class="text-end"><button class="btn btn-success me-3" type="button" id="task_submit_Button" name="task_submit_Button">Enregistrer</button><a class="btn btn-danger" href="/admin/tasks">Fermer</a></div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

@endsection
