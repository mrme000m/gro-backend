@extends('layouts.admin.app')

@section('title', translate('CRM Integration'))

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">{{translate('CRM Integration')}}</h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <form action="{{route('admin.business-settings.web-app.third-party.crm-integration-update')}}" method="post">
                    @csrf
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">
                                <span class="card-header-icon mr-2">
                                    <i class="tio-users"></i>
                                </span>
                                <span>{{translate('CRM Integration Setup')}}</span>
                            </h5>
                        </div>
                        <div class="card-body">
                            @php($crm_integration = \App\Model\BusinessSetting::where('key','crm_integration')->first())
                            @php($crm_data = $crm_integration ? json_decode($crm_integration->value, true) : [
                                'status' => 0, 'provider' => 'salesforce', 'api_key' => '', 'api_secret' => '', 
                                'instance_url' => '', 'sync_customers' => 0, 'sync_leads' => 0, 'sync_orders' => 0
                            ])

                            <div class="row">
                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="toggle-switch d-flex align-items-center mb-3" for="crm_status">
                                            <input type="checkbox" name="status" class="toggle-switch-input"
                                                   value="1" id="crm_status" {{$crm_data['status']==1?'checked':''}}>
                                            <span class="toggle-switch-label">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                            <span class="toggle-switch-content">
                                                <span class="d-block">{{translate('CRM Integration')}} {{translate('Status')}}</span>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="form-label">{{translate('CRM Provider')}}</label>
                                        <select name="provider" class="form-control">
                                            <option value="salesforce" {{$crm_data['provider']=='salesforce'?'selected':''}}>Salesforce</option>
                                            <option value="hubspot" {{$crm_data['provider']=='hubspot'?'selected':''}}>HubSpot</option>
                                            <option value="pipedrive" {{$crm_data['provider']=='pipedrive'?'selected':''}}>Pipedrive</option>
                                            <option value="zoho" {{$crm_data['provider']=='zoho'?'selected':''}}>Zoho CRM</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="form-label">{{translate('Instance URL')}} ({{translate('For Salesforce')}})</label>
                                        <input type="url" name="instance_url" class="form-control" 
                                               placeholder="https://yourinstance.salesforce.com" value="{{$crm_data['instance_url']}}">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="form-label">{{translate('API Key / Client ID')}}</label>
                                        <input type="text" name="api_key" class="form-control" 
                                               placeholder="{{translate('Enter API Key or Client ID')}}" value="{{$crm_data['api_key']}}">
                                    </div>
                                </div>
                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="form-label">{{translate('API Secret / Client Secret')}}</label>
                                        <input type="password" name="api_secret" class="form-control" 
                                               placeholder="{{translate('Enter API Secret or Client Secret')}}" value="{{$crm_data['api_secret']}}">
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
                                                       value="1" id="sync_customers" {{$crm_data['sync_customers']==1?'checked':''}}>
                                                <span class="toggle-switch-label">
                                                    <span class="toggle-switch-indicator"></span>
                                                </span>
                                                <span class="toggle-switch-content">
                                                    <span class="d-block">{{translate('Sync Customers')}}</span>
                                                </span>
                                            </label>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="toggle-switch d-flex align-items-center mb-3" for="sync_leads">
                                                <input type="checkbox" name="sync_leads" class="toggle-switch-input"
                                                       value="1" id="sync_leads" {{$crm_data['sync_leads']==1?'checked':''}}>
                                                <span class="toggle-switch-label">
                                                    <span class="toggle-switch-indicator"></span>
                                                </span>
                                                <span class="toggle-switch-content">
                                                    <span class="d-block">{{translate('Sync Leads')}}</span>
                                                </span>
                                            </label>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="toggle-switch d-flex align-items-center mb-3" for="sync_orders">
                                                <input type="checkbox" name="sync_orders" class="toggle-switch-input"
                                                       value="1" id="sync_orders" {{$crm_data['sync_orders']==1?'checked':''}}>
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
                                    <div class="alert alert-info">
                                        <h6>{{translate('Setup Instructions:')}}</h6>
                                        <ul class="mb-0">
                                            <li><strong>Salesforce:</strong> {{translate('Create a Connected App and get Consumer Key/Secret')}}</li>
                                            <li><strong>HubSpot:</strong> {{translate('Create a Private App and get API Key')}}</li>
                                            <li><strong>Pipedrive:</strong> {{translate('Generate API Token from Settings')}}</li>
                                            <li><strong>Zoho:</strong> {{translate('Create OAuth App and get Client ID/Secret')}}</li>
                                        </ul>
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
