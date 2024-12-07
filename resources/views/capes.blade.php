@extends('layouts.app')

@section('title', trans('skin-api::messages.cape.title'))

@section('content')
    <div class="container content">
        <div class="card shadow-sm">
            <div class="card-header">
                <h2 class="card-header-title">{{ trans('skin-api::messages.cape.management') }}</h2>
            </div>
            <div class="card-body">
                @if($hasCape)
                    <div class="text-center mb-4 cape-preview">
                        <h4>{{ trans('skin-api::messages.cape.current') }}</h4>
                        <img src="{{ $capeUrl }}" 
                             alt="Current cape" 
                             class="img-fluid"
                             width="384"
                             height="192"
                             style="image-rendering: pixelated; background-color: #f8f9fa; padding: 10px; border: 1px solid #dee2e6;">
                    </div>
                @endif

                <form action="{{ route('skin-api.capes.upload') }}" 
                      method="POST" 
                      enctype="multipart/form-data">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label" for="capeInput">
                            {{ trans('skin-api::messages.cape.upload.title') }}
                        </label>
                        <input type="file" 
                               class="form-control @error('cape') is-invalid @enderror" 
                               id="capeInput" 
                               name="cape" 
                               accept="image/png">
                        
                        @error('cape')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                        
                        <div class="form-text">
                            {{ trans('skin-api::messages.cape.upload.requirements', ['width' => setting('skin.cape_width', 64), 'height' => setting('skin.cape_height', 32)]) }}
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-upload"></i> {{ trans('skin-api::messages.cape.upload.submit') }}
                    </button>
                </form>

                @if($hasCape)
                    <form action="{{ route('skin-api.capes.delete') }}" 
                          method="POST" 
                          class="mt-3">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash"></i> {{ trans('skin-api::messages.cape.delete.submit') }}
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('capeInput').addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name || '{{ trans('messages.actions.choose-file') }}';
            e.target.nextElementSibling.textContent = fileName;
        });
    </script>
@endpush
