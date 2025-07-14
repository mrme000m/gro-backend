@extends('layouts.admin.app')

@section('title', translate('Facebook Pixel Configuration'))

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">{{translate('Facebook Pixel Configuration')}}</h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <form action="{{route('admin.business-settings.web-app.third-party.facebook-pixel-update')}}" method="post">
                    @csrf
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">
                                <span class="card-header-icon mr-2">
                                    <i class="tio-facebook"></i>
                                </span>
                                <span>{{translate('Facebook Pixel Setup')}}</span>
                            </h5>
                        </div>
                        <div class="card-body">
                            @php($facebook_pixel = \App\Model\BusinessSetting::where('key','facebook_pixel')->first())
                            @php($facebook_pixel_data = $facebook_pixel ? json_decode($facebook_pixel->value, true) : ['status' => 0, 'pixel_id' => '', 'access_token' => ''])

                            <div class="row">
                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="toggle-switch d-flex align-items-center mb-3" for="facebook_pixel_status">
                                            <input type="checkbox" name="status" class="toggle-switch-input"
                                                   value="1" id="facebook_pixel_status" {{$facebook_pixel_data['status']==1?'checked':''}}>
                                            <span class="toggle-switch-label">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                            <span class="toggle-switch-content">
                                                <span class="d-block">{{translate('Facebook Pixel')}} {{translate('Status')}}</span>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="form-label">{{translate('Facebook Pixel ID')}} <span class="text-danger">*</span></label>
                                        <input type="text" name="pixel_id" class="form-control" 
                                               placeholder="123456789012345" value="{{$facebook_pixel_data['pixel_id']}}">
                                        <small class="form-text text-muted">{{translate('Enter your Facebook Pixel ID (15-16 digits)')}}</small>
                                    </div>
                                </div>
                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="form-label">{{translate('Access Token')}} ({{translate('Optional')}})</label>
                                        <input type="text" name="access_token" class="form-control" 
                                               placeholder="EAAxxxxxxxxxxxxx" value="{{$facebook_pixel_data['access_token']}}">
                                        <small class="form-text text-muted">{{translate('For advanced tracking and conversions API')}}</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <h6>{{translate('Setup Instructions:')}}</h6>
                                        <ol class="mb-0">
                                            <li>{{translate('Go to Facebook Business Manager → Events Manager')}}</li>
                                            <li>{{translate('Create a new Pixel or select existing one')}}</li>
                                            <li>{{translate('Copy your Pixel ID (15-16 digit number)')}}</li>
                                            <li>{{translate('Paste it in the field above and enable the status')}}</li>
                                            <li>{{translate('Save the settings to start tracking Facebook events')}}</li>
                                        </ol>
                                        <hr>
                                        <p class="mb-0">
                                            <strong>{{translate('Events Tracked:')}}</strong> 
                                            {{translate('Page views, purchases, add to cart, view content, and other conversion events.')}}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="alert alert-warning">
                                        <h6>{{translate('Privacy Notice:')}}</h6>
                                        <p class="mb-0">
                                            {{translate('Make sure your privacy policy includes information about Facebook Pixel tracking and obtain proper user consent where required by law.')}}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="btn-toolbar justify-content-end">
                                <button type="submit" class="btn btn-primary">{{translate('Save Configuration')}}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script_2')

@endpush
