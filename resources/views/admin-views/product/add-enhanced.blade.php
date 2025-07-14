@extends('layouts.admin.app')

@section('title', translate('Add new product'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="{{asset('public/assets/admin/css/tags-input.min.css')}}" rel="stylesheet">
    <link href="{{asset('public/assets/admin/css/ux-standards.css')}}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
    <style>
        /* Enhanced Form Styles */
        .form-step {
            display: none;
        }
        .form-step.active {
            display: block;
        }

        /* Step Indicator */
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            background: white;
            border-radius: 8px;
            padding: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .step {
            flex: 1;
            text-align: center;
            padding: 0.5rem;
            border-bottom: 3px solid #dee2e6;
            position: relative;
            transition: all 0.3s ease;
        }
        .step.active {
            border-bottom-color: #107980;
            color: #107980;
        }
        .step.completed {
            border-bottom-color: #28a745;
            color: #28a745;
        }
        .step-number {
            display: inline-block;
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            background: #dee2e6;
            color: #6c757d;
            line-height: 2rem;
            margin-bottom: 0.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .step.active .step-number {
            background: #107980;
            color: white;
        }
        .step.completed .step-number {
            background: #28a745;
            color: white;
        }
        .step-title {
            font-size: 0.875rem;
            font-weight: 500;
        }

        /* Enhanced Form Groups */
        .form-group-enhanced {
            margin-bottom: 1.5rem;
        }
        .form-label-enhanced {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
        }
        .form-label-enhanced.required::after {
            content: ' *';
            color: #dc3545;
        }
        .form-control-enhanced {
            display: block;
            width: 100%;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            line-height: 1.5;
            color: #495057;
            background-color: #fff;
            border: 2px solid #ced4da;
            border-radius: 0.375rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        .form-control-enhanced:focus {
            border-color: #107980;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(16, 121, 128, 0.25);
        }
        .form-help-text {
            font-size: 0.75rem;
            color: #6c757d;
            margin-top: 0.25rem;
        }

        /* Override existing form styles */
        .form-control-enhanced.js-select2-custom {
            border: 2px solid #ced4da !important;
        }
        .form-control-enhanced.js-select2-custom:focus {
            border-color: #107980 !important;
            box-shadow: 0 0 0 0.2rem rgba(16, 121, 128, 0.25) !important;
        }

        /* Card styling improvements */
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        .card-header {
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }

        /* Image Upload */
        .image-preview {
            border: 2px dashed #ced4da;
            border-radius: 0.5rem;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }
        .image-preview:hover {
            border-color: #107980;
            background: #e8f4f5;
        }
        .image-preview.dragover {
            border-color: #107980;
            background: #e8f4f5;
        }
        .image-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        .image-item {
            position: relative;
            border-radius: 0.375rem;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .image-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }
        .image-item .remove-image {
            position: absolute;
            top: 5px;
            right: 5px;
            width: 25px;
            height: 25px;
            padding: 0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(220, 53, 69, 0.9);
            border: none;
            color: white;
        }

        /* Price Calculator */
        .price-calculator {
            background: #f8f9fa;
            border-radius: 0.375rem;
            padding: 1rem;
            margin-top: 1rem;
            border: 1px solid #dee2e6;
        }

        /* Form Navigation */
        .form-navigation {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid #dee2e6;
            background: white;
            border-radius: 0 0 8px 8px;
            padding: 1rem 1.5rem;
        }

        /* Auto-save indicator */
        .auto-save-status {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 1050;
            background: #28a745;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .auto-save-status.show {
            opacity: 1;
        }

        /* Button Enhancements */
        .btn-enhanced {
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            border-radius: 0.375rem;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        .btn-primary-enhanced {
            background: #107980;
            border-color: #107980;
            color: white;
        }
        .btn-primary-enhanced:hover {
            background: #0d6066;
            border-color: #0d6066;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(16, 121, 128, 0.3);
        }
        .btn-secondary-enhanced {
            background: white;
            border-color: #ced4da;
            color: #495057;
        }
        .btn-secondary-enhanced:hover {
            background: #f8f9fa;
            border-color: #adb5bd;
        }
        .btn-success-enhanced {
            background: #28a745;
            border-color: #28a745;
            color: white;
        }
        .btn-success-enhanced:hover {
            background: #218838;
            border-color: #1e7e34;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
        }
    </style>
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header mb-4">
            <div class="d-flex align-items-center justify-content-between">
                <h1 class="page-header-title">
                    <span class="page-header-icon">
                        <img src="{{asset('public/assets/admin/img/add-product.png')}}" class="w--24" alt="">
                    </span>
                    <span>{{translate('Add New Product')}}</span>
                </h1>
            </div>
        </div>

        <!-- Auto-save Status (Fixed Position) -->
        <div class="auto-save-status" id="autoSaveStatus">
            <i class="fas fa-save"></i> <span id="autoSaveText">Auto-save enabled</span>
        </div>

        <!-- Enhanced Form Container -->
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <!-- Step Indicator -->
                <div class="step-indicator">
                    <div class="step active" data-step="1">
                        <div class="step-number">1</div>
                        <div class="step-title">{{translate('Basic Information')}}</div>
                    </div>
                    <div class="step" data-step="2">
                        <div class="step-number">2</div>
                        <div class="step-title">{{translate('Category & Details')}}</div>
                    </div>
                    <div class="step" data-step="3">
                        <div class="step-number">3</div>
                        <div class="step-title">{{translate('Images & Media')}}</div>
                    </div>
                    <div class="step" data-step="4">
                        <div class="step-number">4</div>
                        <div class="step-title">{{translate('Pricing & Stock')}}</div>
                    </div>
                    <div class="step" data-step="5">
                        <div class="step-number">5</div>
                        <div class="step-title">{{translate('Review & Submit')}}</div>
                    </div>
                </div>

                <!-- Form Content Area -->
                <div class="p-4">
                    <!-- Enhanced Form -->
                    <form action="javascript:" method="post" id="product_form_enhanced"
                          enctype="multipart/form-data"
                          data-auto-save="true"
                          data-validate="true"
                          role="form"
                          aria-label="Add new product form">
                        @csrf
                        @php($data = Helpers::get_business_settings('language'))
                        @php($default_lang = Helpers::get_default_language())

                        <!-- Step 1: Basic Information -->
                        <div class="form-step active" id="step-1">
                            <div class="row">
                                <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <i class="fas fa-info-circle text-primary"></i>
                                    {{translate('Product Information')}}
                                </h5>
                                <small class="text-muted">{{translate('Enter the basic details of your product')}}</small>
                            </div>
                            <div class="card-body">
                                @if($data && array_key_exists('code', $data[0]))
                                    <ul class="nav nav-tabs mb-4" role="tablist">
                                        @foreach($data as $lang)
                                            <li class="nav-item" role="presentation">
                                                <a class="nav-link lang_link {{$lang['default'] == true ? 'active':''}}"
                                                   href="#"
                                                   id="{{$lang['code']}}-link"
                                                   role="tab"
                                                   aria-controls="{{$lang['code']}}-form"
                                                   aria-selected="{{$lang['default'] == true ? 'true' : 'false'}}">
                                                    {{Helpers::get_language_name($lang['code']).'('.strtoupper($lang['code']).')'}}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>

                                    @foreach($data as $lang)
                                        <div class="{{$lang['default'] == false ? 'd-none':''}} lang_form"
                                             id="{{$lang['code']}}-form"
                                             role="tabpanel"
                                             aria-labelledby="{{$lang['code']}}-link">

                                            <div class="form-group-enhanced">
                                                <label class="form-label-enhanced required" for="{{$lang['code']}}_name">
                                                    {{translate('Product Name')}} ({{strtoupper($lang['code'])}})
                                                </label>
                                                <input type="text"
                                                       name="name[]"
                                                       id="{{$lang['code']}}_name"
                                                       class="form-control-enhanced"
                                                       placeholder="{{translate('Enter product name')}}"
                                                       data-validate="required|minlength:3|maxlength:100"
                                                       {{$lang['status'] == true ? 'required' : ''}}
                                                       aria-describedby="{{$lang['code']}}_name_help"
                                                       autocomplete="off">
                                                <div class="form-help-text" id="{{$lang['code']}}_name_help">
                                                    {{translate('Enter a clear, descriptive name for your product (3-100 characters)')}}
                                                </div>
                                            </div>

                                            <input type="hidden" name="lang[]" value="{{$lang['code']}}">

                                            <div class="form-group-enhanced">
                                                <label class="form-label-enhanced" for="{{$lang['code']}}_description">
                                                    {{translate('Short Description')}} ({{strtoupper($lang['code'])}})
                                                </label>
                                                <textarea name="description[]"
                                                          class="form-control-enhanced summernote"
                                                          id="{{$lang['code']}}_description"
                                                          rows="4"
                                                          data-validate="maxlength:500"
                                                          aria-describedby="{{$lang['code']}}_description_help"
                                                          placeholder="{{translate('Enter a brief description of your product')}}"></textarea>
                                                <div class="form-help-text" id="{{$lang['code']}}_description_help">
                                                    {{translate('Provide a brief description that highlights key features (max 500 characters)')}}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div id="{{$default_lang}}-form">
                                        <div class="form-group-enhanced">
                                            <label class="form-label-enhanced required" for="product_name">
                                                {{translate('Product Name')}} (EN)
                                            </label>
                                            <input type="text"
                                                   name="name[]"
                                                   id="product_name"
                                                   class="form-control-enhanced"
                                                   placeholder="{{translate('Enter product name')}}"
                                                   data-validate="required|minlength:3|maxlength:100"
                                                   required
                                                   aria-describedby="product_name_help"
                                                   autocomplete="off">
                                            <div class="form-help-text" id="product_name_help">
                                                {{translate('Enter a clear, descriptive name for your product (3-100 characters)')}}
                                            </div>
                                        </div>

                                        <input type="hidden" name="lang[]" value="en">

                                        <div class="form-group-enhanced">
                                            <label class="form-label-enhanced" for="product_description">
                                                {{translate('Short Description')}} (EN)
                                            </label>
                                            <textarea name="description[]"
                                                      class="form-control-enhanced summernote"
                                                      id="product_description"
                                                      rows="4"
                                                      data-validate="maxlength:500"
                                                      aria-describedby="product_description_help"
                                                      placeholder="{{translate('Enter a brief description of your product')}}"></textarea>
                                            <div class="form-help-text" id="product_description_help">
                                                {{translate('Provide a brief description that highlights key features (max 500 characters)')}}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <i class="fas fa-lightbulb text-warning"></i>
                                    {{translate('Tips')}}
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-info-circle"></i> {{translate('Product Name Tips')}}</h6>
                                    <ul class="mb-0 small">
                                        <li>{{translate('Use clear, descriptive names')}}</li>
                                        <li>{{translate('Include key features or benefits')}}</li>
                                        <li>{{translate('Avoid special characters')}}</li>
                                        <li>{{translate('Keep it under 100 characters')}}</li>
                                    </ul>
                                </div>

                                <div class="alert alert-warning">
                                    <h6><i class="fas fa-save"></i> {{translate('Auto-Save')}}</h6>
                                    <p class="mb-0 small">{{translate('Your progress is automatically saved as you type. You can safely leave and return to continue editing.')}}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 2: Category & Details -->
            <div class="form-step" id="step-2">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <i class="fas fa-tags text-primary"></i>
                                    {{translate('Category & Product Details')}}
                                </h5>
                                <small class="text-muted">{{translate('Categorize your product and set important details')}}</small>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group-enhanced">
                                            <label class="form-label-enhanced required" for="category_id">
                                                {{translate('Category')}}
                                            </label>
                                            <select name="category_id"
                                                    id="category_id"
                                                    class="form-control-enhanced js-select2-custom"
                                                    data-validate="required"
                                                    required
                                                    aria-describedby="category_help"
                                                    onchange="getRequest('{{url('/')}}/admin/product/get-categories?parent_id='+this.value,'sub-categories')">
                                                <option value="">{{translate('Select Category')}}</option>
                                                @foreach($categories as $category)
                                                    <option value="{{$category['id']}}">{{$category['name']}}</option>
                                                @endforeach
                                            </select>
                                            <div class="form-help-text" id="category_help">
                                                {{translate('Choose the main category that best describes your product')}}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group-enhanced">
                                            <label class="form-label-enhanced" for="sub_category_id">
                                                {{translate('Sub Category')}}
                                            </label>
                                            <select name="sub_category_id"
                                                    id="sub-categories"
                                                    class="form-control-enhanced js-select2-custom"
                                                    aria-describedby="sub_category_help"
                                                    onchange="getRequest('{{url('/')}}/admin/product/get-categories?parent_id='+this.value,'sub-sub-categories')">
                                                <option value="">{{translate('Select Sub Category')}}</option>
                                            </select>
                                            <div class="form-help-text" id="sub_category_help">
                                                {{translate('Optional: Choose a more specific sub-category')}}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group-enhanced">
                                            <label class="form-label-enhanced required" for="unit">
                                                {{translate('Unit')}}
                                            </label>
                                            <select name="unit"
                                                    id="unit"
                                                    class="form-control-enhanced js-select2-custom"
                                                    data-validate="required"
                                                    required
                                                    aria-describedby="unit_help">
                                                <option value="">{{translate('Select Unit')}}</option>
                                                <option value="kg">{{translate('Kilogram (kg)')}}</option>
                                                <option value="gm">{{translate('Gram (gm)')}}</option>
                                                <option value="ltr">{{translate('Liter (ltr)')}}</option>
                                                <option value="pc">{{translate('Piece (pc)')}}</option>
                                                <option value="ml">{{translate('Milliliter (ml)')}}</option>
                                            </select>
                                            <div class="form-help-text" id="unit_help">
                                                {{translate('Select the unit of measurement for this product')}}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group-enhanced">
                                            <label class="form-label-enhanced required" for="capacity">
                                                {{translate('Capacity/Weight')}}
                                            </label>
                                            <input type="number"
                                                   name="capacity"
                                                   id="capacity"
                                                   class="form-control-enhanced"
                                                   min="0"
                                                   step="0.01"
                                                   value="1"
                                                   data-validate="required|min:0.01"
                                                   required
                                                   aria-describedby="capacity_help"
                                                   placeholder="{{translate('e.g., 500')}}">
                                            <div class="form-help-text" id="capacity_help">
                                                {{translate('Enter the quantity/weight per unit (e.g., 500ml, 2kg)')}}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group-enhanced">
                                            <label class="form-label-enhanced required" for="maximum_order_quantity">
                                                {{translate('Maximum Order Quantity')}}
                                            </label>
                                            <input type="number"
                                                   name="maximum_order_quantity"
                                                   id="maximum_order_quantity"
                                                   class="form-control-enhanced"
                                                   min="1"
                                                   step="1"
                                                   value="10"
                                                   data-validate="required|min:1"
                                                   required
                                                   aria-describedby="max_order_help"
                                                   placeholder="{{translate('e.g., 5')}}">
                                            <div class="form-help-text" id="max_order_help">
                                                {{translate('Maximum quantity a customer can order at once')}}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group-enhanced">
                                            <label class="form-label-enhanced" for="tags">
                                                {{translate('Product Tags')}}
                                            </label>
                                            <input type="text"
                                                   name="tags"
                                                   id="tags"
                                                   class="form-control-enhanced"
                                                   data-role="tagsinput"
                                                   aria-describedby="tags_help"
                                                   placeholder="{{translate('Enter tags separated by commas')}}">
                                            <div class="form-help-text" id="tags_help">
                                                {{translate('Add relevant tags to help customers find your product (e.g., organic, fresh, local)')}}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-12">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div>
                                                        <h6 class="mb-1">{{translate('Product Visibility')}}</h6>
                                                        <small class="text-muted">{{translate('Control whether this product appears in your store')}}</small>
                                                    </div>
                                                    <label class="toggle-switch">
                                                        <input type="checkbox"
                                                               class="toggle-switch-input"
                                                               name="status"
                                                               value="1"
                                                               checked
                                                               aria-describedby="visibility_help">
                                                        <span class="toggle-switch-label">
                                                            <span class="toggle-switch-indicator"></span>
                                                        </span>
                                                    </label>
                                                </div>
                                                <div class="form-help-text mt-2" id="visibility_help">
                                                    {{translate('When disabled, this product will be hidden from customers but remain in your inventory')}}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <i class="fas fa-lightbulb text-warning"></i>
                                    {{translate('Category Tips')}}
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-info-circle"></i> {{translate('Categorization Tips')}}</h6>
                                    <ul class="mb-0 small">
                                        <li>{{translate('Choose the most specific category possible')}}</li>
                                        <li>{{translate('Proper categorization improves discoverability')}}</li>
                                        <li>{{translate('Use tags for additional searchability')}}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 3: Images & Media -->
            <div class="form-step" id="step-3">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <i class="fas fa-images text-primary"></i>
                                    {{translate('Product Images')}}
                                </h5>
                                <small class="text-muted">{{translate('Upload high-quality images of your product')}}</small>
                            </div>
                            <div class="card-body">
                                <div class="image-upload-section">
                                    <div class="image-preview" id="imagePreview">
                                        <div class="upload-placeholder">
                                            <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                            <h5>{{translate('Drag & Drop Images Here')}}</h5>
                                            <p class="text-muted">{{translate('or click to browse files')}}</p>
                                            <button type="button" class="btn btn-primary-enhanced" onclick="document.getElementById('imageInput').click()">
                                                <i class="fas fa-plus"></i> {{translate('Choose Images')}}
                                            </button>
                                            <input type="file"
                                                   id="imageInput"
                                                   name="images[]"
                                                   multiple
                                                   accept="image/*"
                                                   data-validate="file-type:jpg,jpeg,png,webp|file-size:5"
                                                   style="display: none;"
                                                   aria-describedby="image_help">
                                        </div>
                                        <div class="image-grid" id="imageGrid" style="display: none;"></div>
                                    </div>
                                    <div class="form-help-text mt-3" id="image_help">
                                        {{translate('Upload up to 4 images. Recommended size: 800x800px. Max file size: 5MB each. Supported formats: JPG, PNG, WebP')}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <i class="fas fa-lightbulb text-warning"></i>
                                    {{translate('Image Tips')}}
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-camera"></i> {{translate('Photo Guidelines')}}</h6>
                                    <ul class="mb-0 small">
                                        <li>{{translate('Use high-resolution images (800x800px or larger)')}}</li>
                                        <li>{{translate('Show product from multiple angles')}}</li>
                                        <li>{{translate('Use good lighting and clean backgrounds')}}</li>
                                        <li>{{translate('First image will be the main product image')}}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 4: Pricing & Stock -->
            <div class="form-step" id="step-4">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <i class="fas fa-dollar-sign text-primary"></i>
                                    {{translate('Pricing & Inventory')}}
                                </h5>
                                <small class="text-muted">{{translate('Set your product pricing and stock levels')}}</small>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group-enhanced">
                                            <label class="form-label-enhanced required" for="price">
                                                {{translate('Unit Price')}}
                                            </label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">{{Helpers::currency_symbol()}}</span>
                                                </div>
                                                <input type="number"
                                                       name="price"
                                                       id="price"
                                                       class="form-control-enhanced"
                                                       min="0"
                                                       step="0.01"
                                                       data-validate="required|min:0.01"
                                                       required
                                                       aria-describedby="price_help"
                                                       placeholder="0.00"
                                                       onchange="calculatePricing()">
                                            </div>
                                            <div class="form-help-text" id="price_help">
                                                {{translate('Set the selling price per unit')}}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group-enhanced">
                                            <label class="form-label-enhanced" for="total_stock">
                                                {{translate('Stock Quantity')}}
                                            </label>
                                            <input type="number"
                                                   name="total_stock"
                                                   id="total_stock"
                                                   class="form-control-enhanced"
                                                   min="0"
                                                   value="0"
                                                   data-validate="min:0"
                                                   aria-describedby="stock_help"
                                                   placeholder="0">
                                            <div class="form-help-text" id="stock_help">
                                                {{translate('Current available stock quantity')}}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group-enhanced">
                                            <label class="form-label-enhanced" for="discount_type">
                                                {{translate('Discount Type')}}
                                            </label>
                                            <select name="discount_type"
                                                    id="discount_type"
                                                    class="form-control-enhanced"
                                                    onchange="updateDiscountSymbol(); calculatePricing();"
                                                    aria-describedby="discount_type_help">
                                                <option value="percent">{{translate('Percentage (%)')}}</option>
                                                <option value="amount">{{translate('Fixed Amount')}}</option>
                                            </select>
                                            <div class="form-help-text" id="discount_type_help">
                                                {{translate('Choose how to calculate the discount')}}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group-enhanced">
                                            <label class="form-label-enhanced" for="discount">
                                                {{translate('Discount')}} <span id="discount_symbol">(%)</span>
                                            </label>
                                            <input type="number"
                                                   name="discount"
                                                   id="discount"
                                                   class="form-control-enhanced"
                                                   min="0"
                                                   value="0"
                                                   step="0.01"
                                                   data-validate="min:0"
                                                   aria-describedby="discount_help"
                                                   placeholder="0"
                                                   onchange="calculatePricing()">
                                            <div class="form-help-text" id="discount_help">
                                                {{translate('Optional discount amount or percentage')}}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Price Calculator -->
                                <div class="price-calculator">
                                    <h6><i class="fas fa-calculator"></i> {{translate('Price Summary')}}</h6>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="text-center">
                                                <small class="text-muted">{{translate('Original Price')}}</small>
                                                <div class="h5" id="originalPrice">{{Helpers::currency_symbol()}}0.00</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="text-center">
                                                <small class="text-muted">{{translate('Discount')}}</small>
                                                <div class="h5 text-warning" id="discountAmount">{{Helpers::currency_symbol()}}0.00</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="text-center">
                                                <small class="text-muted">{{translate('Final Price')}}</small>
                                                <div class="h5 text-success" id="finalPrice">{{Helpers::currency_symbol()}}0.00</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <i class="fas fa-lightbulb text-warning"></i>
                                    {{translate('Pricing Tips')}}
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-tag"></i> {{translate('Pricing Strategy')}}</h6>
                                    <ul class="mb-0 small">
                                        <li>{{translate('Research competitor pricing')}}</li>
                                        <li>{{translate('Consider your costs and desired profit margin')}}</li>
                                        <li>{{translate('Use discounts strategically')}}</li>
                                        <li>{{translate('Monitor and adjust based on sales')}}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 5: Review & Submit -->
            <div class="form-step" id="step-5">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <i class="fas fa-check-circle text-success"></i>
                                    {{translate('Review Your Product')}}
                                </h5>
                                <small class="text-muted">{{translate('Please review all information before submitting')}}</small>
                            </div>
                            <div class="card-body">
                                <div id="productReview">
                                    <!-- Review content will be populated by JavaScript -->
                                </div>

                                <div class="alert alert-success mt-4">
                                    <h6><i class="fas fa-info-circle"></i> {{translate('Ready to Submit?')}}</h6>
                                    <p class="mb-0">{{translate('Once you submit, your product will be added to your inventory. You can always edit it later from the products list.')}}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

                        <!-- Form Navigation -->
                        <div class="form-navigation">
                            <button type="button" class="btn btn-secondary-enhanced" id="prevBtn" onclick="changeStep(-1)" disabled>
                                <i class="fas fa-arrow-left"></i> {{translate('Previous')}}
                            </button>
                            <button type="button" class="btn btn-primary-enhanced" id="nextBtn" onclick="changeStep(1)">
                                {{translate('Next')}} <i class="fas fa-arrow-right"></i>
                            </button>
                            <button type="submit" class="btn btn-success-enhanced" id="submitBtn" style="display: none;">
                                <i class="fas fa-check"></i> {{translate('Create Product')}}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <script src="{{asset('public/assets/admin/js/auto-save.js')}}"></script>
    <script src="{{asset('public/assets/admin/js/form-validation.js')}}"></script>
    <script src="{{asset('public/assets/admin/js/accessibility.js')}}"></script>
    <script src="{{asset('public/assets/admin/js/tags-input.min.js')}}"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>

    <script>
        let currentStep = 1;
        const totalSteps = 5;
        let uploadedImages = []; // Global variable for uploaded images

        // Initialize enhanced form
        $(document).ready(function() {
            // Show auto-save status initially
            $('#autoSaveStatus').addClass('show');

            // Initialize auto-save
            $('#product_form_enhanced').autoSave({
                saveInterval: 3000,
                showIndicator: false // We'll handle our own indicator
            });

            // Initialize validation
            $('#product_form_enhanced').validate();

            // Initialize summernote
            $('.summernote').summernote({
                height: 150,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['insert', ['link']],
                    ['view', ['codeview']]
                ]
            });

            // Language tab switching
            $('.lang_link').click(function(e) {
                e.preventDefault();
                let lang = $(this).attr('id').replace('-link', '');

                $('.lang_link').removeClass('active');
                $(this).addClass('active');

                $('.lang_form').addClass('d-none');
                $('#' + lang + '-form').removeClass('d-none');
            });

            // Auto-save status updates
            $('#product_form_enhanced').on('autosaved', function(e) {
                $('#autoSaveStatus').removeClass('show');
                $('#autoSaveText').html('<i class="fas fa-check"></i> Saved at ' + new Date().toLocaleTimeString());
                $('#autoSaveStatus').addClass('show');

                // Hide after 3 seconds
                setTimeout(function() {
                    $('#autoSaveStatus').removeClass('show');
                }, 3000);
            });

            // Show saving indicator on form changes
            $('#product_form_enhanced').on('input change', function() {
                $('#autoSaveText').html('<i class="fas fa-spinner fa-spin"></i> Saving...');
                $('#autoSaveStatus').addClass('show');
            });

            // Initialize image upload
            initializeImageUpload();

            // Initialize tags input
            $('#tags').tagsInput({
                'height': '40px',
                'width': '100%',
                'defaultText': 'Add tag',
                'removeWithBackspace': true,
                'delimiter': [',']
            });
        });

        function initializeImageUpload() {
            const imageInput = document.getElementById('imageInput');
            const imagePreview = document.getElementById('imagePreview');
            const imageGrid = document.getElementById('imageGrid');
            let uploadedImages = [];

            // File input change handler
            imageInput.addEventListener('change', function(e) {
                handleFiles(e.target.files);
            });

            // Drag and drop handlers
            imagePreview.addEventListener('dragover', function(e) {
                e.preventDefault();
                imagePreview.classList.add('dragover');
            });

            imagePreview.addEventListener('dragleave', function(e) {
                e.preventDefault();
                imagePreview.classList.remove('dragover');
            });

            imagePreview.addEventListener('drop', function(e) {
                e.preventDefault();
                imagePreview.classList.remove('dragover');
                handleFiles(e.dataTransfer.files);
            });

            function handleFiles(files) {
                if (uploadedImages.length + files.length > 4) {
                    alert('Maximum 4 images allowed');
                    return;
                }

                Array.from(files).forEach(file => {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            addImageToGrid(e.target.result, file);
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }

            function addImageToGrid(src, file) {
                uploadedImages.push(file);

                const imageItem = document.createElement('div');
                imageItem.className = 'image-item';
                imageItem.innerHTML = `
                    <img src="${src}" alt="Product image" class="img-thumbnail">
                    <button type="button" class="btn btn-danger btn-sm remove-image" onclick="removeImage(this)">
                        <i class="fas fa-times"></i>
                    </button>
                `;

                imageGrid.appendChild(imageItem);

                if (uploadedImages.length > 0) {
                    document.querySelector('.upload-placeholder').style.display = 'none';
                    imageGrid.style.display = 'grid';
                }
            }

            window.removeImage = function(button) {
                const imageItem = button.parentElement;
                const index = Array.from(imageGrid.children).indexOf(imageItem);

                uploadedImages.splice(index, 1);
                imageItem.remove();

                if (uploadedImages.length === 0) {
                    document.querySelector('.upload-placeholder').style.display = 'block';
                    imageGrid.style.display = 'none';
                }
            };
        }

        function updateDiscountSymbol() {
            const discountType = document.getElementById('discount_type').value;
            const symbol = discountType === 'percent' ? '%' : '{{Helpers::currency_symbol()}}';
            document.getElementById('discount_symbol').textContent = `(${symbol})`;
        }

        function calculatePricing() {
            const price = parseFloat(document.getElementById('price').value) || 0;
            const discountType = document.getElementById('discount_type').value;
            const discount = parseFloat(document.getElementById('discount').value) || 0;

            let discountAmount = 0;
            if (discountType === 'percent') {
                discountAmount = (price * discount) / 100;
            } else {
                discountAmount = discount;
            }

            const finalPrice = Math.max(0, price - discountAmount);

            document.getElementById('originalPrice').textContent = '{{Helpers::currency_symbol()}}' + price.toFixed(2);
            document.getElementById('discountAmount').textContent = '{{Helpers::currency_symbol()}}' + discountAmount.toFixed(2);
            document.getElementById('finalPrice').textContent = '{{Helpers::currency_symbol()}}' + finalPrice.toFixed(2);
        }

        function changeStep(direction) {
            const newStep = currentStep + direction;

            if (newStep < 1 || newStep > totalSteps) return;

            // Validate current step before proceeding
            if (direction > 0 && !validateCurrentStep()) {
                return;
            }

            // Hide current step
            $('#step-' + currentStep).removeClass('active');
            $('.step[data-step="' + currentStep + '"]').removeClass('active').addClass('completed');

            // Show new step
            currentStep = newStep;
            $('#step-' + currentStep).addClass('active');
            $('.step[data-step="' + currentStep + '"]').addClass('active');

            // Special handling for review step
            if (currentStep === 5) {
                populateReviewStep();
            }

            // Update navigation buttons
            updateNavigationButtons();

            // Announce step change for screen readers
            window.accessibilityManager.announce(`Step ${currentStep} of ${totalSteps}: ${$('.step[data-step="' + currentStep + '"] .step-title').text()}`);
        }

        function populateReviewStep() {
            const formData = new FormData(document.getElementById('product_form_enhanced'));
            const reviewContainer = document.getElementById('productReview');

            let reviewHTML = '<div class="row">';

            // Basic Information
            reviewHTML += `
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6><i class="fas fa-info-circle"></i> {{translate('Basic Information')}}</h6>
                        </div>
                        <div class="card-body">
                            <p><strong>{{translate('Name')}}:</strong> ${formData.get('name[]') || 'Not specified'}</p>
                            <p><strong>{{translate('Description')}}:</strong> ${formData.get('description[]') ? 'Provided' : 'Not provided'}</p>
                        </div>
                    </div>
                </div>
            `;

            // Category & Details
            const categorySelect = document.getElementById('category_id');
            const categoryText = categorySelect.options[categorySelect.selectedIndex]?.text || 'Not selected';

            reviewHTML += `
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6><i class="fas fa-tags"></i> {{translate('Category & Details')}}</h6>
                        </div>
                        <div class="card-body">
                            <p><strong>{{translate('Category')}}:</strong> ${categoryText}</p>
                            <p><strong>{{translate('Unit')}}:</strong> ${formData.get('unit') || 'Not selected'}</p>
                            <p><strong>{{translate('Capacity')}}:</strong> ${formData.get('capacity') || '0'}</p>
                            <p><strong>{{translate('Max Order Qty')}}:</strong> ${formData.get('maximum_order_quantity') || '0'}</p>
                        </div>
                    </div>
                </div>
            `;

            // Pricing
            const price = formData.get('price') || '0';
            const discount = formData.get('discount') || '0';
            const discountType = formData.get('discount_type') || 'percent';

            reviewHTML += `
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6><i class="fas fa-dollar-sign"></i> {{translate('Pricing')}}</h6>
                        </div>
                        <div class="card-body">
                            <p><strong>{{translate('Price')}}:</strong> {{Helpers::currency_symbol()}}${price}</p>
                            <p><strong>{{translate('Discount')}}:</strong> ${discount}${discountType === 'percent' ? '%' : ' {{Helpers::currency_symbol()}}'}</p>
                            <p><strong>{{translate('Stock')}}:</strong> ${formData.get('total_stock') || '0'} units</p>
                        </div>
                    </div>
                </div>
            `;

            // Images
            reviewHTML += `
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6><i class="fas fa-images"></i> {{translate('Images')}}</h6>
                        </div>
                        <div class="card-body">
                            <p><strong>{{translate('Images uploaded')}}:</strong> ${uploadedImages.length} of 4</p>
                            ${uploadedImages.length === 0 ? '<p class="text-warning">{{translate("No images uploaded")}}</p>' : ''}
                        </div>
                    </div>
                </div>
            `;

            reviewHTML += '</div>';
            reviewContainer.innerHTML = reviewHTML;
        }

        function validateCurrentStep() {
            // Basic validation for current step
            const currentStepElement = $('#step-' + currentStep);
            const requiredFields = currentStepElement.find('[required], [data-validate*="required"]');

            let isValid = true;
            requiredFields.each(function() {
                if (!this.value.trim()) {
                    isValid = false;
                    $(this).focus();
                    return false;
                }
            });

            return isValid;
        }

        function updateNavigationButtons() {
            $('#prevBtn').prop('disabled', currentStep === 1);

            if (currentStep === totalSteps) {
                $('#nextBtn').hide();
                $('#submitBtn').show();
            } else {
                $('#nextBtn').show();
                $('#submitBtn').hide();
            }
        }

        // Form submission
        $('#product_form_enhanced').on('submit', function(e) {
            e.preventDefault();

            if (!window.formValidator.validate('product_form_enhanced')) {
                return;
            }

            // Show loading state
            $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Creating...');

            // Submit form (implement actual submission logic)
            setTimeout(() => {
                window.accessibilityManager.announce('Product created successfully');
                // Redirect or show success message
            }, 2000);
        });
    </script>
@endpush
