@extends('layouts.app')

@section('content')
    <search :countries='@json($countries)' show-route="{{route('show')}}" ></search>
@endsection
