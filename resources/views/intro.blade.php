@extends('layouts.master')

@section('content')

<div class="layout-boxed">
    <div class="container-fluid main-content">
      <div class="row widget-container fluid-height">
        <div class="col-md-12">
          <div class="widget-content padded text-center">
              <h1 class="text-danger">No input parameters specified</h1>
              <p>&nbsp;</p>
              <p>Please check our <a href="https://github.com/pericles-project/MICE/wiki" target="_blank">Quick Start Guide</a> to read instructions on how to use the tool.</p>
              <p>&nbsp;</p>
              <p>You can also view the Test Cases we have prepared to understand how the tool works: </p>
              <a href="{{ url('') }}?case=1" class="btn btn-primary">Test Case 1</a>
              <a href="{{ url('') }}?case=2" class="btn btn-primary">Test Case 2</a>
              <a href="{{ url('') }}?case=3" class="btn btn-primary">Test Case 3</a>
              <p>&nbsp;</p>
          </div>
        </div>
      </div>
</div>
@endsection
