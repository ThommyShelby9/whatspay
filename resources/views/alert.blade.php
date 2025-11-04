
@isset($alert)
@if ( $alert["message"] != "" )
<br>
<div class="alert alert-{{$alert["type"]}} alert-dismissible fade show" role="alert">
    <span class="fw-semi-bold">{{$alert["message"]}}</span>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<br>
@endif
@endisset

@if ($errors->any())
<div class="alert alert-danger" role="alert">
    <h3 class="alert-heading">Une erreur est survenue</h3>
    <p>Merci de revoir les donn&eacute;es saisies. Voici les erreurs constat&eacute;es : </p>
    <hr />
    <p class="mb-0">
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
    </p>
</div>
@endif
