@extends('admin.layouts.admin')

@section('title', trans('skin-api::admin.skins'))

@push('styles')
    <style>
        #skinPreview  {
            width: 325px;
            image-rendering: crisp-edges; /* Firefox */
            image-rendering: pixelated; /* Chrome/Safari */
        }
    </style>
@endpush

@section('content')
    <div class="card shadow mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">{{ trans('skin-api::admin.api.title') }}</h5>
        </div>
        <div class="card-body">
            <p>{{ trans('skin-api::admin.api.info') }}</p>
            <a href="https://market.azuriom.com/resources/18" class="btn btn-primary" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-journals"></i> {{ trans('admin.nav.documentation') }}
            </a>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <form method="POST" action="{{ route('skin-api.admin.skins.update') }}" enctype="multipart/form-data">
                @csrf

                @include('skin-api::admin._fields')

                <div class="row gx-3">
                    <div class="mb-3 col-md-6">
                        <label class="form-label" for="skinInput">{{ trans('skin-api::admin.fields.default') }}</label>
                        <input type="file" class="form-control @error('skin') is-invalid @enderror" id="skinInput" name="skin" accept="image/png" data-image-preview="skinPreview">

                        @error('skin')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror

                        <img src="{{ $defaultSkin }}" class="mt-2 img-fluid rounded img-preview" alt="Skin" id="skinPreview">
                    </div>

                    <div class="mb-3 col-md-6">
                        <label class="form-label" for="notFoundSelect">{{ trans('skin-api::admin.not_found.name') }}</label>
                        <select class="form-select @error('not_found') is-invalid @enderror" id="notFoundSelect" name="not_found_handling" required>
                            <option value="default_skin" @selected($notFound === 'default_skin')>
                                {{ trans('skin-api::admin.not_found.default_skin') }}
                            </option>
                            <option value="404_status" @selected($notFound === '404_status')>
                                {{ trans('skin-api::admin.not_found.404') }}
                            </option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> {{ trans('messages.actions.save') }}
                </button>
            </form>
        </div>
    </div>
@endsection
