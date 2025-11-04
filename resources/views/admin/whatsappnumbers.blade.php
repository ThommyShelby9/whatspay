

<!-- https://laravel.com/docs/11.x/blade#including-subviews -->

@extends('admin.layout')

@section('pagecontent')
@include('alert')

<div class="container-fluid">

  <div class="card">
    <div class="card-header pb-0 card-no-border">
      <h4>{{$pagetilte}}</h4>
      <span>{{$pagecardtilte}}</span>
    </div>
    <div class="card-body">
      <br>
      <button class="btn btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#whatsappNumberModal" data-whatever="@getbootstrap">Ajouter num&eacute;ro</button>
      <br><br>
      <div class="dt-ext table-responsive theme-scrollbar">
        <table class="display" id="items_datatable">
          <thead>
          <tr>
            <th>Numero</th>
            <th>Statut</th>
            <th></th>
          </tr>
          </thead>
          <tbody>
          @foreach($viewData["phones"] as $item)
          <tr>
            <td>{{$item->phone}}</td>
            <td>
              @foreach($viewData["statuses"] as $status)
                @if($status["label"] == $item->status)
                  <span class="badge badge-{{$status["badge"]}}">{{$item->status}}</span>
                @endif
              @endforeach
            </td>
            <td>
              <ul class="action">
                <li class="edit"> <a href=""><i class="icon-pencil-alt"></i></a></li>
                <li class="delete"><a href="""><i class="icon-trash"></i></a></li>
              </ul>
            </td>
          </tr>
          @endforeach
          </tbody>
          <tfoot>
          <tr>
            <th>Numero</th>
            <th>Statut</th>
            <th></th>
          </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>


</div>


<div class="modal fade" id="whatsappNumberModal" tabindex="-1" role="dialog" aria-labelledby="whatsappNumberModal" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-toggle-wrapper social-profile text-start dark-sign-up">
        <h3 class="modal-header justify-content-center border-0 txt-dark">AJOUT DE NUMERO WHATSAPP</h3>
        <div class="modal-body">
          <div class="col-md-12">
            <div class="form-group">
              <div class="input-group">
                <select class="form-select" id="phonecountry" name="phonecountry">
                  @foreach($viewData["countries"] as $item)
                  <option value="{{$item->id}}"
                          @if($item->id == $viewData["bjId"]) selected @endif
                    >{{$item->name}} {{$item->emoji}} ({{$item->phone_code}})</option>
                  @endforeach
                </select>
                <input class="form-control" type="number" id="phone" name="phone" required="" placeholder="0197******">
                <button class="btn btn-primary" id="whatsappNumberGenerateCodeButton">Envoyer code</button>
              </div>
            </div>
          </div>
          <br>
          <div class="col-md-12" id="codeValidationDiv" style="display: none">
            <div class="form-group">
              <div class="input-group">
                <input class="form-control" type="text" id="code" name="code" required="" placeholder="Code">
                <button class="btn btn-secondary" id="whatsappNumberValidateCodeButton">Valider code</button>
              </div>
            </div>
          </div>
          <input type="hidden" id="whatsappNumberId" name="whatsappNumberId" value="">
        </div>
      </div>
    </div>
  </div>
</div>



<input type="hidden" name="countriesJson" id="countriesJson" value="{{ $viewData["countriesJson"] }}">
@endsection
