@extends('layouts.app')

@section('title', '| PHP Configuration')

@section('content')
	@php
		echo phpinfo();
	@endphp
@endsection                                                       