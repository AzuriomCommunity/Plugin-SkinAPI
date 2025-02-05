@extends('layouts.app')

@section('title', trans('skin-api::messages.title'))

@push('styles')
    <style>
        #skinPreview,
        #capePreview {
            width: 325px;
            image-rendering: crisp-edges; /* Firefox */
            image-rendering: pixelated; /* Chrome/Safari */
        }
    </style>
@endpush

@push('footer-scripts')
    <script>
        document.querySelectorAll('[data-skin-preview]').forEach(function (input) {
            input.addEventListener('change', function () {
                if (!input.files || !input.files[0]) {
                    return;
                }

                const reader = new FileReader();

                reader.onload = function (e) {
                    const preview = document.getElementById(input.dataset.skinPreview);
                    preview.src = e.currentTarget.result;
                    preview.classList.remove('d-none');
                };

                reader.readAsDataURL(input.files[0]);
            });
        })
    </script>
@endpush

@section('content')
    <div class="card shadow">
        <div class="card-body">
            <form action="{{ route('skin-api.update') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <h2>{{ trans('skin-api::messages.title') }}</h2>

                <div class="row gx-3">
                    <div class="@if($capesEnabled) col-md-6 @else col-md-12 @endif mb-3">
                        <label for="skin">{{ trans('skin-api::messages.skin') }}</label>
                        <input type="file" class="form-control @error('skin') is-invalid @enderror" id="skin" name="skin" accept="image/png" data-skin-preview="skinPreview">

                        @error('skin')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror

                        <img src="{{ $skinUrl }}" alt="{{ trans('skin-api::messages.skin') }}" id="skinPreview" class="my-3 img-fluid">

                        @if($hasSkin)
                            <br>
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteSkinModal">
                                <i class="bi bi-trash"></i> {{ trans('messages.actions.delete') }}
                            </button>
                        @endif
                    </div>

                    @if($capesEnabled)
                        <div class="col-md-6 mb-3">
                            <label for="cape">{{ trans('skin-api::messages.cape') }}</label>
                            <input type="file" class="form-control @error('cape') is-invalid @enderror" id="cape" name="cape" accept="image/png" data-skin-preview="capePreview">

                            @error('cape')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror

                            <img src="{{ $capeUrl ?? '#' }}" alt="{{ trans('skin-api::messages.cape') }}" id="capePreview" class="my-3 img-fluid @if(!$capeUrl) d-none @endif">

                            @if($hasCape)
                                <br>
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteCapeModal">
                                    <i class="bi bi-trash"></i> {{ trans('messages.actions.delete') }}
                                </button>
                            @endif
                        </div>
                    @endif
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> {{ trans('messages.actions.save') }}
                </button>
            </form>
        </div>
    </div>

    @if($hasSkin)
        @include('skin-api::profile._skin_modal')
    @endif

    @if($hasCape)
        @include('skin-api::profile._cape_modal')
    @endif
@endsection
