@extends('layouts.settings.default')
@push('css_lib')
    <!-- iCheck -->
    <link rel="stylesheet" href="{{asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css')}}">
    <!-- select2 -->
    <link rel="stylesheet" href="{{asset('vendor/select2/css/select2.min.css')}}">
    <link rel="stylesheet" href="{{asset('vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css')}}">
    <!-- bootstrap wysihtml5 - text editor -->
    <link rel="stylesheet" href="{{asset('vendor/summernote/summernote-bs4.min.css')}}">
    {{--dropzone--}}
    <link rel="stylesheet" href="{{asset('vendor/dropzone/min/dropzone.min.css')}}">
@endpush
@section('settings_title',trans('lang.app_setting_notifications'))
@section('settings_content')
    @include('flash::message')
    @include('adminlte-templates::common.errors')
    <div class="clearfix"></div>
    <div class="card shadow-sm">
        <div class="card-header">
            <ul class="nav nav-tabs d-flex flex-row align-items-start card-header-tabs">
                <li class="nav-item">
                    <a class="nav-link active" href="{!! url()->current() !!}"><i class="fas fa-cog mr-2"></i>{{trans('lang.app_setting_'.$tab)}}</a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            {!! Form::open(['url' => ['settings/update'], 'method' => 'patch', 'enctype' => 'multipart/form-data']) !!}
            <div class="row">
                <h5 class="col-12 pb-4"><i class="mr-3 fas fa-bell"></i>{!! trans('lang.app_setting_notifications') !!}</h5>
                <!-- 'Boolean enable_notifications Field' -->
                <div class="form-group row col-12">
                    {!! Form::label('enable_notifications', trans("lang.app_setting_enable_notifications"), ['class' => 'col-2 control-label text-right']) !!}
                    {!! Form::hidden('enable_notifications', 0, ['id' => "hidden_enable_notifications"]) !!}
                    <div class="col-10 icheck-{{ setting('theme_color') }}">
                        {!! Form::checkbox('enable_notifications', 1, setting('enable_notifications', false)) !!}
                        <label for="enable_notifications">{!! trans("lang.app_setting_enable_notifications_help") !!}</label>
                    </div>
                </div>

                <!-- firebase_api_key Field -->
                <div class="form-group row col-6">
                    {!! Form::label('firebase_api_key', trans('lang.app_setting_firebase_api_key'), ['class' => 'col-4 control-label text-right']) !!}
                    <div class="col-8">
                        {!! Form::text('firebase_api_key', setting('firebase_api_key'), ['class' => 'form-control', 'placeholder' => trans('lang.app_setting_firebase_api_key_placeholder')]) !!}
                        <div class="form-text text-muted">
                            {!! trans('lang.app_setting_firebase_api_key_help') !!}
                        </div>
                    </div>
                </div>

                <!-- firebase_auth_domain Field -->
                <div class="form-group row col-6">
                    {!! Form::label('firebase_auth_domain', trans('lang.app_setting_firebase_auth_domain'), ['class' => 'col-4 control-label text-right']) !!}
                    <div class="col-8">
                        {!! Form::text('firebase_auth_domain', setting('firebase_auth_domain'), ['class' => 'form-control', 'placeholder' => trans('lang.app_setting_firebase_auth_domain_placeholder')]) !!}
                        <div class="form-text text-muted">
                            {!! trans('lang.app_setting_firebase_auth_domain_help') !!}
                        </div>
                    </div>
                </div>

                <!-- firebase_database_url Field -->
                <div class="form-group row col-6">
                    {!! Form::label('firebase_database_url', trans('lang.app_setting_firebase_database_url'), ['class' => 'col-4 control-label text-right']) !!}
                    <div class="col-8">
                        {!! Form::text('firebase_database_url', setting('firebase_database_url'), ['class' => 'form-control', 'placeholder' => trans('lang.app_setting_firebase_database_url_placeholder')]) !!}
                        <div class="form-text text-muted">
                            {!! trans('lang.app_setting_firebase_database_url_help') !!}
                        </div>
                    </div>
                </div>

                <!-- firebase_project_id Field -->
                <div class="form-group row col-6">
                    {!! Form::label('firebase_project_id', trans('lang.app_setting_firebase_project_id'), ['class' => 'col-4 control-label text-right']) !!}
                    <div class="col-8">
                        {!! Form::text('firebase_project_id', setting('firebase_project_id'), ['class' => 'form-control', 'placeholder' => trans('lang.app_setting_firebase_project_id_placeholder')]) !!}
                        <div class="form-text text-muted">
                            {!! trans('lang.app_setting_firebase_project_id_help') !!}
                        </div>
                    </div>
                </div>

                <!-- firebase_storage_bucket Field -->
                <div class="form-group row col-6">
                    {!! Form::label('firebase_storage_bucket', trans('lang.app_setting_firebase_storage_bucket'), ['class' => 'col-4 control-label text-right']) !!}
                    <div class="col-8">
                        {!! Form::text('firebase_storage_bucket', setting('firebase_storage_bucket'), ['class' => 'form-control', 'placeholder' => trans('lang.app_setting_firebase_storage_bucket_placeholder')]) !!}
                        <div class="form-text text-muted">
                            {!! trans('lang.app_setting_firebase_storage_bucket_help') !!}
                        </div>
                    </div>
                </div>

                <!-- firebase_messaging_sender_id Field -->
                <div class="form-group row col-6">
                    {!! Form::label('firebase_messaging_sender_id', trans('lang.app_setting_firebase_messaging_sender_id'), ['class' => 'col-4 control-label text-right']) !!}
                    <div class="col-8">
                        {!! Form::text('firebase_messaging_sender_id', setting('firebase_messaging_sender_id'), ['class' => 'form-control', 'placeholder' => trans('lang.app_setting_firebase_messaging_sender_id_placeholder')]) !!}
                        <div class="form-text text-muted">
                            {!! trans('lang.app_setting_firebase_messaging_sender_id_help') !!}
                        </div>
                    </div>
                </div>

                <!-- Upload Firebase JSON File -->
                <!-- New Field for JSON File -->
                <div class="form-group row col-6">
                    {!! Form::label('firebase_credentials', trans('lang.app_setting_firebase_credentials'), ['class' => 'col-4 control-label text-right']) !!}
                    <div class="col-8">
                        {!! Form::file('firebase_credentials', ['class' => 'form-control']) !!}
                        <div class="form-text text-muted">
                            {!! trans('lang.app_setting_firebase_credentials_help') !!}
                        </div>
                    </div>
                </div>

                <!-- Submit Field -->
                <div class="form-group mt-4 col-12 text-right">
                    <button type="submit" class="btn bg-{{ setting('theme_color') }} mx-md-3 my-lg-0 my-xl-0 my-md-0 my-2">
                        <i class="fas fa-save"></i> {{ trans('lang.save') }} {{ trans('lang.app_setting_notification') }}
                    </button>
                    <a href="{!! route('users.index') !!}" class="btn btn-default"><i class="fas fa-undo"></i> {{ trans('lang.cancel') }}</a>
                </div>
            </div>
            {!! Form::close() !!}
            <div class="clearfix"></div>
        </div>
    </div>
    @include('layouts.media_modal', ['collection' => null])
@endsection

@push('scripts_lib')
    <!-- iCheck -->

    <!-- select2 -->
    <script src="{{asset('vendor/select2/js/select2.full.min.js')}}"></script>
    <!-- AdminLTE dashboard demo (This is only for demo purposes) -->
    <script src="{{asset('vendor/summernote/summernote.min.js')}}"></script>
    {{--dropzone--}}
    <script src="{{asset('vendor/dropzone/min/dropzone.min.js')}}"></script>
    <script type="text/javascript">
        Dropzone.autoDiscover = false;
        var dropzoneFields = [];
    </script>
@endpush
