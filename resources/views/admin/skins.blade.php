@extends('admin.layouts.admin')

@section('title', trans('skin-api::admin.skins.title'))

@section('content')
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">{{ trans('skin-api::admin.skins.title') }}</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('skin-api.admin.skins.update') }}">
                @csrf

                <div class="form-group">
                    <label for="skinSettings">{{ trans('skin-api::admin.skins.settings') }}</label>
                    <!-- Add skin-related settings inputs here -->
                    <p class="text-muted">{{ trans('skin-api::admin.skins.description') }}</p>
                </div>

                <button type="submit" class="btn btn-primary">{{ trans('admin.actions.save') }}</button>
            </form>
        </div>
    </div>
@endsection
