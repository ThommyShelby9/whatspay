<!-- https://laravel.com/docs/11.x/blade#including-subviews -->

@extends('admin.layout')

@section('pagecontent')
    @include('alert')

    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <h4></h4>
                        <span></span>
                        <a href="/admin/task/new" class="btn btn-success">Nouvelle demande</a>
                    </div>
                    <div class="card-body">

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
