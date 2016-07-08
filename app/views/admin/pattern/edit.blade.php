@extends('admin.layout')

@section('content')

@if ($errors->has())
<div class="alert alert-danger alert-dismissibl fade in">
    <button type="button" class="close" data-dismiss="alert">
        <span aria-hidden="true">&times;</span>
        <span class="sr-only">Close</span>
    </button>
    @foreach ($errors->all() as $error)
		{{ $error }}		
	@endforeach
</div>
@endif

<div class="row">
	<div class="col-md-12">
		<h3 class="page-title">Pattern Management</h3>
		<ul class="page-breadcrumb breadcrumb">
			<li>
				<i class="fa fa-home"></i>
				<span>Pattern</span>
				<i class="fa fa-angle-right"></i>
			</li>
			<li>
				<span>Edit</span>
			</li>
		</ul>
	</div>
</div>

<div class="portlet box blue">
	<div class="portlet-title">
		<div class="caption">
			<i class="fa fa-pencil-square-o"></i> Edit Pattern
		</div>
	</div>
    <div class="portlet-body">
        <form class="form-horizontal" role="form" method="post" action="{{ URL::route('admin.pattern.store') }}">
        	
        	<input type="hidden" name="pattern_id" value="{{ $pattern->id }}">
        
            @foreach ([
                'name' => 'Title',
                'description' => 'Description',
            ] as $key => $value)
            <div class="form-group">
                <label class="col-sm-2 control-label">{{ Form::label($key, $value) }}</label>
                <div class="col-sm-10">
                    @if ($key == 'description') 
                    	{{ Form::textarea($key, $pattern->{$key}, ['class' => 'form-control']) }}     
                    @else
                        {{ Form::text($key, $pattern->{$key}, ['class' => 'form-control']) }}
                    @endif
                </div>
            </div>
            @endforeach
          
          <div class="form-group">
              <div class="col-sm-offset-2 col-sm-10 text-center">
                  <button class="btn btn-success">
                      <span class="glyphicon glyphicon-ok-circle"></span> Save
                  </button>
                  <a href="{{ URL::route('admin.pattern') }}" class="btn btn-primary">
                      <span class="glyphicon glyphicon-share-alt"></span> Back
                  </a>
              </div>
          </div>
        </form>
    </div>
</div>
@stop

@stop
