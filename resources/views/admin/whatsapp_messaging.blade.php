<!-- File: resources/views/admin/whatsapp_messaging.blade.php -->
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
      <form action="{{ route('admin.whatsapp_messaging.send') }}" method="POST">
        @csrf
        
        <div class="row mb-4">
          <div class="col-md-12">
            <h5>Sélectionner les destinataires</h5>
            
            <div class="mb-3">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="select-all">
                <label class="form-check-label" for="select-all">
                  Sélectionner tous les diffuseurs
                </label>
              </div>
            </div>
            
            <div class="row">
              <div class="col-md-12">
                <div class="dt-ext table-responsive theme-scrollbar">
                  <table class="display" id="recipients-datatable">
                    <thead>
                      <tr>
                        <th width="50">Sélection</th>
                        <th>Diffuseur</th>
                        <th>Email</th>
                        <th>Numéros WhatsApp</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($viewData['influencers'] as $influencer)
                      <tr>
                        <td>
                          <input class="form-check-input recipient-checkbox" type="checkbox" name="recipients[]" value="{{ $influencer->id }}">
                        </td>
                        <td>{{ $influencer->firstname }} {{ $influencer->lastname }}</td>
                        <td>{{ $influencer->email }}</td>
                        <td>{{ $influencer->phone_count }}</td>
                      </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="row mb-4">
          <div class="col-md-12">
            <h5>Message</h5>
            <div class="mb-3">
              <textarea class="form-control" name="message" rows="5" required></textarea>
              <div class="form-text">
                Vous pouvez utiliser les variables suivantes dans votre message : {nom}, {prenom}
              </div>
            </div>
          </div>
        </div>
        
        <div class="row">
          <div class="col-md-12">
            <button type="submit" class="btn btn-primary">Envoyer les messages</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

@push('scripts')
<script>
  $(document).ready(function() {
    // Initialize datatable
    var table = $('#recipients-datatable').DataTable({
      responsive: true,
      dom: '<"top"fl>rt<"bottom"ip>',
      language: {
        search: "_INPUT_",
        searchPlaceholder: "Rechercher..."
      }
    });
    
    // Select all functionality
    $('#select-all').on('change', function() {
      $('.recipient-checkbox').prop('checked', $(this).is(':checked'));
    });
    
    // Update "select all" checkbox state
    $('.recipient-checkbox').on('change', function() {
      var allChecked = $('.recipient-checkbox:checked').length === $('.recipient-checkbox').length;
      $('#select-all').prop('checked', allChecked);
    });
  });
</script>
@endpush
@endsection