@extends('layouts.admin.app')

@section('title', translate('Google Analytics Configuration'))

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">{{translate('Google Analytics Configuration')}}</h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <form action="{{route('admin.business-settings.web-app.third-party.google-analytics-update')}}" method="post">
                    @csrf
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">
                                <span class="card-header-icon mr-2">
                                    <i class="tio-chart-donut-2"></i>
                                </span>
                                <span>{{translate('Google Analytics Setup')}}</span>
                            </h5>
                        </div>
                        <div class="card-body">
                            @php($google_analytics = \App\Model\BusinessSetting::where('key','google_analytics')->first())
                            @php($google_analytics_data = $google_analytics ? json_decode($google_analytics->value, true) : ['status' => 0, 'tracking_id' => '', 'measurement_id' => ''])

                            <div class="row">
                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="toggle-switch d-flex align-items-center mb-3" for="google_analytics_status">
                                            <input type="checkbox" name="status" class="toggle-switch-input"
                                                   value="1" id="google_analytics_status" {{$google_analytics_data['status']==1?'checked':''}}>
                                            <span class="toggle-switch-label">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                            <span class="toggle-switch-content">
                                                <span class="d-block">{{translate('Google Analytics')}} {{translate('Status')}}</span>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="form-label">{{translate('Google Analytics Tracking ID')}} (GA4)</label>
                                        <input type="text" name="tracking_id" class="form-control" 
                                               placeholder="G-XXXXXXXXXX" value="{{$google_analytics_data['tracking_id']}}">
                                        <small class="form-text text-muted">{{translate('Enter your Google Analytics 4 Tracking ID (starts with G-)')}}</small>
                                    </div>
                                </div>
                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="form-label">{{translate('Measurement ID')}} ({{translate('Optional')}})</label>
                                        <input type="text" name="measurement_id" class="form-control" 
                                               placeholder="G-XXXXXXXXXX" value="{{$google_analytics_data['measurement_id']}}">
                                        <small class="form-text text-muted">{{translate('Additional measurement ID if needed')}}</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <h6>{{translate('Setup Instructions:')}}</h6>
                                        <ol class="mb-0">
                                            <li>{{translate('Go to Google Analytics and create a new property')}}</li>
                                            <li>{{translate('Copy your Tracking ID (starts with G-)')}}</li>
                                            <li>{{translate('Paste it in the field above and enable the status')}}</li>
                                            <li>{{translate('Save the settings to start tracking')}}</li>
                                        </ol>
                                        <hr>
                                        <p class="mb-0">
                                            <strong>{{translate('Note:')}}</strong> 
                                            {{translate('Google Analytics will track page views, user interactions, and conversion events on your website.')}}
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
