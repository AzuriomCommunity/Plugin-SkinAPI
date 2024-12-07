@extends('admin.layouts.admin')

@section('title', 'Skin API configuration')

@section('content')
    <div class="card shadow mb-4">
        <div class="card-body">
            <div>
                API : <br>
                <div><code>GET {{ url('/api/skin-api/skins/{user_id}') }}</code> --> <img src="{{plugin_asset('skin-api', 'img/steve.png')}}" alt=""></div>
                <div><code>GET {{ url('/api/skin-api/avatars/combo/{user_id}') }}</code> (only from 64x64 skins) --> <img src="{{plugin_asset('skin-api', 'img/combo_steve.png')}}" alt=""></div>
                <div><code>GET {{ url('/api/skin-api/avatars/face/{user_id}') }}</code> (only from 64x64 skins) --> <img src="{{plugin_asset('skin-api', 'img/face_steve.png')}}" alt=""></div>
                <div>
                    <code>POST {{ url('/api/skin-api/skins/update') }}</code><br>
                    The POST route require 2 parameters : <br>
                    <code>{ "access_token" : "XXXX", "skin" : "IMAGE.PNG" }</code>
                </div>
                The user, if connected, can update his skin if he navigates to <code>{{ route('skin-api.home') }}</code>
            </div>

            <form method="POST">
                @csrf

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="widthInput">{{ trans('skin-api::admin.fields.height') }}</label>
                        <input type="text" class="form-control @error('width') is-invalid @enderror" id="widthInput" name="width" value="{{ old('width', $width) }}">
                        
                        @error('width')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="form-group col-md-4">
                        <label for="heightInput">{{ trans('skin-api::admin.fields.width') }}</label>
                        <input type="text" class="form-control @error('height') is-invalid @enderror" id="heightInput" name="height" value="{{ old('height', $height) }}">
                        
                        @error('height')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="form-group col-md-4">
                        <label for="scaleInput">{{ trans('skin-api::admin.fields.scale') }}</label>
                        <input type="text" class="form-control @error('scale') is-invalid @enderror" id="scaleInput" name="scale" value="{{ old('scale', $scale) }}">
                        
                        @error('scale')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>
                    
                    <div class="form-group col-md-4">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="showNavIcon" name="show_nav_icon" value="1" {{ old('show_nav_icon', $show_nav_icon) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="showNavIcon">Show Skin Icon in Navigation</label>
                        </div>

                        @error('show_nav_icon')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="form-group col-md-4">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="showInProfile" name="show_in_profile" value="1" {{ old('show_in_profile', $show_in_profile) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="showInProfile">Show Skin Management in Profile</label>
                        </div>

                        @error('show_in_profile')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="form-group col-md-12">
                        <label for="navigationIcon">Navigation Icon</label>
                        <input type="text" class="form-control @error('navigation_icon') is-invalid @enderror" id="navigationIcon" name="navigation_icon" value="{{ old('navigation_icon', $navigation_icon) }}">
                        <small class="form-text text-muted">Enter a Bootstrap icon class (e.g., bi bi-images). You can find icons at <a href="https://icons.getbootstrap.com/" target="_blank">Bootstrap Icons</a><br>Leave empty to hide the navigation icon</small>

                        @error('navigation_icon')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="form-group col-md-12">
                        <label for="notFoundBehavior">When User Not Found</label>
                        <select class="form-control @error('not_found_behavior') is-invalid @enderror" id="notFoundBehavior" name="not_found_behavior">
                            <option value="default_skin" {{ old('not_found_behavior', $not_found_behavior) === 'default_skin' ? 'selected' : '' }}>Return Default Steve Skin</option>
                            <option value="error_message" {{ old('not_found_behavior', $not_found_behavior) === 'error_message' ? 'selected' : '' }}>Return User Not Found Error</option>
                        </select>
                        <small class="form-text text-muted">Choose what happens when a requested user doesn't exist</small>

                        @error('not_found_behavior')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> {{ trans('messages.actions.save') }}
                </button>
            </form>
        </div>
    </div>
@endsection
