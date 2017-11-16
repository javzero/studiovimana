@extends('layouts.vadmin.main')
@section('title', 'VADmin | Nueva etiqueta')

@section('styles')
@endsection

@section('header')
	@component('vadmin.components.header')
		@slot('breadcrums')
			<li class="breadcrumb-item"><a href="{{ url('vadmin')}}">Inicio</a></li>
			<li class="breadcrumb-item"><a href="{{ route('categories.index')}}">Etiquetas del Catálogo</a></li>
			<li class="breadcrumb-item active">Nueva Etiqueta</li>
		@endslot
		@slot('actions')
			<div class="list-actions">
				<h1>Nueva Etiqueta de Catálogo</h1>
			</div>
		@endslot
	@endcomponent
@endsection

@section('content')
	<div class="inner-wrapper">
		{!! Form::open(['route' => 'cat_tags.store', 'method' => 'POST', 'files' => true, 'class' => 'row big-form mw450', 'data-parsley-validate' => '']) !!}	
			@include('vadmin.portfolio.tags.form')
			<div class="form-actions right">
				<a href="{{ route('tags.index')}}">
					<button type="button" class="btn btnRed">
						<i class="icon-cross2"></i> Cancelar
					</button>
				</a>
				<button type="submit" class="btn btnGreen">
					<i class="icon-check2"></i> Guardar
				</button>
			</div>
		{!! Form::close() !!}
	</div>  
@endsection

@section('scripts')
	<script type="text/javascript" src="{{ asset('plugins/validation/parsley.min.js') }}" ></script>
	<script type="text/javascript" src="{{ asset('plugins/validation/es/parsley-es.min.js') }}" ></script>
@endsection

@section('custom_js')
	<script>
		$('.CatalogTagsLi').addClass('open');
		$('.CatalogTagsNew').addClass('active');
	</script>
@endsection

