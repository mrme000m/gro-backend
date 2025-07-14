@extends('layouts.admin.app')

@section('title', translate('Multi Vendor Configuration'))

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">{{translate('Multi Vendor Configuration')}}</h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <form action="{{route('admin.business-settings.advanced.multi-vendor-update')}}" method="post">
                    @csrf
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">
                                <span class="card-header-icon mr-2">
                                    <i class="tio-shop"></i>
                                </span>
                                <span>{{translate('Multi Vendor Setup')}}</span>
                            </h5>
                        </div>
                        <div class="card-body">
                            @php($multi_vendor = \App\Model\BusinessSetting::where('key','multi_vendor')->first())
                            @php($multi_vendor_data = $multi_vendor ? json_decode($multi_vendor->value, true) : ['status' => 0, 'commission_rate' => 10, 'vendor_approval' => 1, 'auto_approval' => 0])

                            <div class="row">
                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="toggle-switch d-flex align-items-center mb-3" for="multi_vendor_status">
                                            <input type="checkbox" name="status" class="toggle-switch-input"
                                                   value="1" id="multi_vendor_status" {{$multi_vendor_data['status']==1?'checked':''}}>
                                            <span class="toggle-switch-label">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                            <span class="toggle-switch-content">
                                                <span class="d-block">{{translate('Multi Vendor')}} {{translate('Status')}}</span>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="form-label">{{translate('Commission Rate')}} (%)</label>
                                        <input type="number" name="commission_rate" class="form-control" 
                                               placeholder="10" value="{{$multi_vendor_data['commission_rate']}}" min="0" max="100" step="0.01">
                                        <small class="form-text text-muted">{{translate('Default commission rate for vendors')}}</small>
                                    </div>
                                </div>
                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="toggle-switch d-flex align-items-center mb-3" for="vendor_approval">
                                            <input type="checkbox" name="vendor_approval" class="toggle-switch-input"
                                                   value="1" id="vendor_approval" {{$multi_vendor_data['vendor_approval']==1?'checked':''}}>
                                            <span class="toggle-switch-label">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                            <span class="toggle-switch-content">
                                                <span class="d-block">{{translate('Vendor Approval Required')}}</span>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="toggle-switch d-flex align-items-center mb-3" for="auto_approval">
                                            <input type="checkbox" name="auto_approval" class="toggle-switch-input"
                                                   value="1" id="auto_approval" {{$multi_vendor_data['auto_approval']==1?'checked':''}}>
                                            <span class="toggle-switch-label">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                            <span class="toggle-switch-content">
                                                <span class="d-block">{{translate('Auto Approve Products')}}</span>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="alert alert-warning">
                                        <h6>{{translate('Coming Soon')}}</h6>
                                        <p class="mb-0">
                                            {{translate('Multi vendor functionality is currently under development. You can configure the settings now, but the actual vendor management features will be available in a future update.')}}
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
