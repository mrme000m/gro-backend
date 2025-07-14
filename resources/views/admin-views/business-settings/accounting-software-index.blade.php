@extends('layouts.admin.app')

@section('title', translate('Accounting Software Integration'))

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">{{translate('Accounting Software Integration')}}</h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <form action="{{route('admin.business-settings.web-app.third-party.accounting-software-update')}}" method="post">
                    @csrf
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">
                                <span class="card-header-icon mr-2">
                                    <i class="tio-calculator"></i>
                                </span>
                                <span>{{translate('Accounting Software Setup')}}</span>
                            </h5>
                        </div>
                        <div class="card-body">
                            @php($accounting_software = \App\Model\BusinessSetting::where('key','accounting_software')->first())
                            @php($accounting_data = $accounting_software ? json_decode($accounting_software->value, true) : [
                                'status' => 0, 'provider' => 'quickbooks', 'client_id' => '', 'client_secret' => '', 
                                'sandbox_mode' => 1, 'sync_customers' => 0, 'sync_products' => 0, 'sync_orders' => 0
                            ])

                            <div class="row">
                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="toggle-switch d-flex align-items-center mb-3" for="accounting_status">
                                            <input type="checkbox" name="status" class="toggle-switch-input"
                                                   value="1" id="accounting_status" {{$accounting_data['status']==1?'checked':''}}>
                                            <span class="toggle-switch-label">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                            <span class="toggle-switch-content">
                                                <span class="d-block">{{translate('Accounting Integration')}} {{translate('Status')}}</span>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="form-label">{{translate('Accounting Provider')}}</label>
                                        <select name="provider" class="form-control">
                                            <option value="quickbooks" {{$accounting_data['provider']=='quickbooks'?'selected':''}}>QuickBooks Online</option>
                                            <option value="xero" {{$accounting_data['provider']=='xero'?'selected':''}}>Xero</option>
                                            <option value="sage" {{$accounting_data['provider']=='sage'?'selected':''}}>Sage Business Cloud</option>
                                            <option value="freshbooks" {{$accounting_data['provider']=='freshbooks'?'selected':''}}>FreshBooks</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="toggle-switch d-flex align-items-center mb-3" for="sandbox_mode">
                                            <input type="checkbox" name="sandbox_mode" class="toggle-switch-input"
                                                   value="1" id="sandbox_mode" {{$accounting_data['sandbox_mode']==1?'checked':''}}>
                                            <span class="toggle-switch-label">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                            <span class="toggle-switch-content">
                                                <span class="d-block">{{translate('Sandbox Mode')}}</span>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="form-label">{{translate('Client ID')}}</label>
                                        <input type="text" name="client_id" class="form-control" 
                                               placeholder="{{translate('Enter Client ID')}}" value="{{$accounting_data['client_id']}}">
                                    </div>
                                </div>
                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="form-label">{{translate('Client Secret')}}</label>
                                        <input type="password" name="client_secret" class="form-control" 
                                               placeholder="{{translate('Enter Client Secret')}}" value="{{$accounting_data['client_secret']}}">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <h6>{{translate('Sync Options')}}</h6>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label class="toggle-switch d-flex align-items-center mb-3" for="sync_customers">
                                                <input type="checkbox" name="sync_customers" class="toggle-switch-input"
                                                       value="1" id="sync_customers" {{$accounting_data['sync_customers']==1?'checked':''}}>
                                                <span class="toggle-switch-label">
                                                    <span class="toggle-switch-indicator"></span>
                                                </span>
                                                <span class="toggle-switch-content">
                                                    <span class="d-block">{{translate('Sync Customers')}}</span>
                                                </span>
                                            </label>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="toggle-switch d-flex align-items-center mb-3" for="sync_products">
                                                <input type="checkbox" name="sync_products" class="toggle-switch-input"
                                                       value="1" id="sync_products" {{$accounting_data['sync_products']==1?'checked':''}}>
                                                <span class="toggle-switch-label">
                                                    <span class="toggle-switch-indicator"></span>
                                                </span>
                                                <span class="toggle-switch-content">
                                                    <span class="d-block">{{translate('Sync Products')}}</span>
                                                </span>
                                            </label>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="toggle-switch d-flex align-items-center mb-3" for="sync_orders">
                                                <input type="checkbox" name="sync_orders" class="toggle-switch-input"
                                                       value="1" id="sync_orders" {{$accounting_data['sync_orders']==1?'checked':''}}>
                                                <span class="toggle-switch-label">
                                                    <span class="toggle-switch-indicator"></span>
                                                </span>
                                                <span class="toggle-switch-content">
                                                    <span class="d-block">{{translate('Sync Orders')}}</span>
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="alert alert-warning">
                                        <h6>{{translate('Coming Soon')}}</h6>
                                        <p class="mb-0">
                                            {{translate('This integration is currently under development. You can configure the settings now, but the actual synchronization functionality will be available in a future update.')}}
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
