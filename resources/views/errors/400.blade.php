@extends('layouts.master')

@section('content')
<div class="container-fluid main-content">
    <div class="row widget-container fluid-height">
      <div class="col-md-12">
        <div class="widget-content padded text-center">
            <h1 class="text-danger">Bad request</h1>
            <p>{{ $message or "" }}</p>
        </div>
      </div>
    </div>
</div>
@endsection
