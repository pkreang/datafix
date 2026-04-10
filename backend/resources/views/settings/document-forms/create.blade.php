@extends('layouts.app')

@section('title', __('common.add') . ' ' . __('common.document_forms'))

@section('content')
    @include('settings.document-forms._form', ['inlineToolbar' => true])
@endsection
