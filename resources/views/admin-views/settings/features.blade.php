@extends('layouts.admin.modern-app')

@section('title', 'Feature Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-toggle-on text-primary"></i>
                        Feature Management
                    </h4>
                    <div>
                        <button type="button" class="btn btn-warning btn-sm" onclick="resetFeatures()">
                            <i class="fas fa-undo"></i> Reset to Default
                        </button>
                        <button type="button" class="btn btn-success btn-sm" onclick="saveFeatures()">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Feature Management:</strong> Enable or disable features to customize your admin dashboard. 
                        Disabled features will be hidden from the navigation and interface, but all code remains intact.
                        Core features (Dashboard, Authentication, Settings) cannot be disabled.
                    </div>

                    <form id="featuresForm" method="POST" action="{{ route('admin.settings.features.update') }}">
                        @csrf
                        
                        <div class="row">
                            @foreach($features as $categoryKey => $category)
                                @if(is_array($category))
                                    <div class="col-lg-6 col-md-12 mb-4">
                                        <div class="feature-category">
                                            <h5 class="category-title">
                                                <i class="fas fa-{{ getCategoryIcon($categoryKey) }}"></i>
                                                {{ ucwords(str_replace('_', ' ', $categoryKey)) }}
                                                @if($categoryKey !== 'core')
                                                    <div class="float-right">
                                                        <label class="switch">
                                                            <input type="checkbox" 
                                                                   name="features[{{ $categoryKey }}.enabled]" 
                                                                   value="1"
                                                                   {{ isset($category['enabled']) && $category['enabled'] ? 'checked' : '' }}
                                                                   onchange="toggleCategory('{{ $categoryKey }}', this.checked)">
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                @endif
                                            </h5>
                                            
                                            <div class="feature-items" id="category-{{ $categoryKey }}">
                                                @foreach($category as $featureKey => $featureValue)
                                                    @if($featureKey !== 'enabled' && is_bool($featureValue))
                                                        <div class="feature-item">
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <label class="feature-label">
                                                                    {{ ucwords(str_replace('_', ' ', $featureKey)) }}
                                                                </label>
                                                                <label class="switch">
                                                                    <input type="checkbox" 
                                                                           name="features[{{ $categoryKey }}.{{ $featureKey }}]" 
                                                                           value="1"
                                                                           {{ $featureValue ? 'checked' : '' }}
                                                                           {{ $categoryKey === 'core' ? 'disabled' : '' }}
                                                                           class="feature-checkbox"
                                                                           data-category="{{ $categoryKey }}">
                                                                    <span class="slider round {{ $categoryKey === 'core' ? 'disabled' : '' }}"></span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.feature-category {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 1rem;
    height: 100%;
}

.category-title {
    color: #495057;
    border-bottom: 2px solid #dee2e6;
    padding-bottom: 0.5rem;
    margin-bottom: 1rem;
}

.feature-item {
    padding: 0.5rem 0;
    border-bottom: 1px solid #e9ecef;
}

.feature-item:last-child {
    border-bottom: none;
}

.feature-label {
    margin: 0;
    font-weight: 500;
    color: #6c757d;
}

/* Toggle Switch Styles */
.switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 24px;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
}

.slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .4s;
}

input:checked + .slider {
    background-color: #28a745;
}

input:focus + .slider {
    box-shadow: 0 0 1px #28a745;
}

input:checked + .slider:before {
    transform: translateX(26px);
}

.slider.round {
    border-radius: 24px;
}

.slider.round:before {
    border-radius: 50%;
}

.slider.disabled {
    background-color: #6c757d !important;
    cursor: not-allowed;
}

.category-disabled {
    opacity: 0.5;
    pointer-events: none;
}
</style>

<script>
function toggleCategory(categoryKey, enabled) {
    const categoryDiv = document.getElementById('category-' + categoryKey);
    const checkboxes = categoryDiv.querySelectorAll('.feature-checkbox');
    
    if (enabled) {
        categoryDiv.classList.remove('category-disabled');
        checkboxes.forEach(checkbox => {
            checkbox.disabled = false;
        });
    } else {
        categoryDiv.classList.add('category-disabled');
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
            checkbox.disabled = true;
        });
    }
}

function saveFeatures() {
    document.getElementById('featuresForm').submit();
}

function resetFeatures() {
    if (confirm('Are you sure you want to reset all features to default? This will undo all your customizations.')) {
        window.location.href = '{{ route("admin.settings.features.reset") }}';
    }
}

// Initialize category states on page load
document.addEventListener('DOMContentLoaded', function() {
    @foreach($features as $categoryKey => $category)
        @if($categoryKey !== 'core' && is_array($category))
            @if(!isset($category['enabled']) || !$category['enabled'])
                toggleCategory('{{ $categoryKey }}', false);
            @endif
        @endif
    @endforeach
});
</script>
@endsection

@php
function getCategoryIcon($category) {
    $icons = [
        'core' => 'cog',
        'orders' => 'shopping-cart',
        'products' => 'box',
        'customers' => 'users',
        'marketing' => 'bullhorn',
        'analytics' => 'chart-bar',
        'inventory' => 'warehouse',
        'delivery' => 'truck',
        'payments' => 'credit-card',
        'content' => 'file-alt',
        'system' => 'server',
        'advanced' => 'rocket',
        'integrations' => 'plug',
        'mobile_app' => 'mobile-alt'
    ];
    
    return $icons[$category] ?? 'circle';
}
@endphp
