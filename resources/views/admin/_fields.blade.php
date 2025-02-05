<div class="row gx-3">
    <div class="mb-3 col-md-4">
        <label class="form-label" for="widthInput">{{ trans('skin-api::admin.fields.width') }}</label>
        <div class="input-group @error('width') has-validation @enderror">
            <input type="number" min="0" class="form-control @error('width') is-invalid @enderror" id="widthInput" name="width" value="{{ old('width', $width) }}" required>
            <span class="input-group-text">px</span>

            @error('width')
            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
        </div>
    </div>

    <div class="mb-3 col-md-4">
        <label class="form-label" for="heightInput">{{ trans('skin-api::admin.fields.height') }}</label>
        <div class="input-group @error('height') has-validation @enderror">
            <input type="number" min="0" class="form-control @error('height') is-invalid @enderror" id="heightInput" name="height" value="{{ old('height', $height) }}" required>
            <span class="input-group-text">px</span>

            @error('height')
            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
        </div>
    </div>

    <div class="mb-3 col-md-4">
        <label class="form-label" for="scaleInput">{{ trans('skin-api::admin.fields.scale') }}</label>
        <input type="number" min="0" class="form-control @error('scale') is-invalid @enderror" id="scaleInput" name="scale" value="{{ old('scale', $scale) }}" required>

        @error('scale')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>
</div>
