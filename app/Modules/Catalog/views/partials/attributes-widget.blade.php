@props([
    'attributes' => [],
    'entityAttributes' => [],
    'entityType' => 'product',
    'entityId' => null
])

@php
    $allAttributes = \App\Modules\Catalog\Models\Attribute::orderBy('name')->get();
    $existingAttributes = [];
    
    if ($entityAttributes) {
        foreach ($entityAttributes as $attr) {
            $existingAttributes[$attr->id] = [
                'name' => $attr->name,
                'slug' => $attr->slug,
                'value' => $attr->pivot->value ?? ''
            ];
        }
    }
@endphp

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="card-title mb-0">
            <i class="bi bi-sliders me-2"></i> Атрибуты
        </h6>
        <span class="badge bg-primary" id="attributes-count">{{ count($existingAttributes) }}</span>
    </div>
    <div class="card-body">
        <!-- Selected Attributes List -->
        <div id="selected-attributes-container" class="mb-3">
            @if(count($existingAttributes) > 0)
                @foreach($existingAttributes as $attrId => $attrData)
                    <div class="attribute-item border rounded p-3 mb-2" data-attribute-id="{{ $attrId }}">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-4">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-tag text-primary me-2"></i>
                                    <span class="fw-medium">{{ $attrData['name'] }}</span>
                                </div>
                                <input type="hidden" name="attributes[{{ $attrId }}][id]" value="{{ $attrId }}">
                                <input type="hidden" name="attributes[{{ $attrId }}][name]" value="{{ $attrData['name'] }}">
                                <input type="hidden" name="attributes[{{ $attrId }}][slug]" value="{{ $attrData['slug'] }}">
                            </div>
                            <div class="col-md-7">
                                <input type="text" 
                                       class="form-control form-control-sm" 
                                       name="attributes[{{ $attrId }}][value]" 
                                       value="{{ $attrData['value'] }}"
                                       placeholder="Значение атрибута"
                                       maxlength="255">
                            </div>
                            <div class="col-md-1 text-end">
                                <button type="button" 
                                        class="btn btn-sm btn-outline-danger remove-attribute"
                                        data-attribute-id="{{ $attrId }}"
                                        title="Удалить атрибут">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="alert alert-info mb-0" id="no-attributes-message">
                    <i class="bi bi-info-circle me-2"></i>
                    Атрибуты не добавлены. Выберите атрибут из списка ниже.
                </div>
            @endif
        </div>

        <!-- Add Attribute Section -->
        <div class="border-top pt-3">
            <label class="form-label small text-muted">Добавить атрибут</label>
            <div class="input-group">
                <select class="form-select" id="attribute-select">
                    <option value="">-- Выберите атрибут --</option>
                    @foreach($allAttributes as $attr)
                        @if(!isset($existingAttributes[$attr->id]))
                            <option value="{{ $attr->id }}" 
                                    data-name="{{ $attr->name }}" 
                                    data-slug="{{ $attr->slug }}">
                                {{ $attr->name }} ({{ $attr->slug }})
                            </option>
                        @endif
                    @endforeach
                </select>
                <button type="button" class="btn btn-outline-primary" id="add-attribute-btn">
                    <i class="bi bi-plus-circle"></i> Добавить
                </button>
            </div>
            <div class="form-text">
                Выберите атрибут и нажмите "Добавить", затем укажите значение.
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const attributeSelect = document.getElementById('attribute-select');
    const addAttributeBtn = document.getElementById('add-attribute-btn');
    const selectedAttributesContainer = document.getElementById('selected-attributes-container');
    const noAttributesMessage = document.getElementById('no-attributes-message');
    const attributesCount = document.getElementById('attributes-count');
    
    let attributeCounter = {{ count($existingAttributes) }};
    
    // Update attributes count badge
    function updateAttributesCount() {
        const count = selectedAttributesContainer.querySelectorAll('.attribute-item').length;
        if (attributesCount) {
            attributesCount.textContent = count;
        }
        
        // Show/hide no attributes message
        if (noAttributesMessage) {
            if (count === 0) {
                noAttributesMessage.style.display = 'block';
            } else {
                noAttributesMessage.style.display = 'none';
            }
        }
    }
    
    // Add attribute
    function addAttribute() {
        const selectedOption = attributeSelect.selectedOptions[0];
        if (!selectedOption || selectedOption.value === '') {
            alert('Пожалуйста, выберите атрибут из списка');
            return;
        }
        
        const attributeId = selectedOption.value;
        const attributeName = selectedOption.dataset.name;
        const attributeSlug = selectedOption.dataset.slug;
        
        // Check if attribute already exists
        if (selectedAttributesContainer.querySelector(`[data-attribute-id="${attributeId}"]`)) {
            alert('Этот атрибут уже добавлен');
            return;
        }
        
        // Remove no attributes message if exists
        if (noAttributesMessage) {
            noAttributesMessage.remove();
        }
        
        // Create attribute item
        const attributeItem = document.createElement('div');
        attributeItem.className = 'attribute-item border rounded p-3 mb-2';
        attributeItem.dataset.attributeId = attributeId;
        attributeItem.innerHTML = `
            <div class="row g-2 align-items-center">
                <div class="col-md-4">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-tag text-primary me-2"></i>
                        <span class="fw-medium">${attributeName}</span>
                    </div>
                    <input type="hidden" name="attributes[${attributeId}][id]" value="${attributeId}">
                    <input type="hidden" name="attributes[${attributeId}][name]" value="${attributeName}">
                    <input type="hidden" name="attributes[${attributeId}][slug]" value="${attributeSlug}">
                </div>
                <div class="col-md-7">
                    <input type="text" 
                           class="form-control form-control-sm" 
                           name="attributes[${attributeId}][value]" 
                           value=""
                           placeholder="Значение атрибута"
                           maxlength="255">
                </div>
                <div class="col-md-1 text-end">
                    <button type="button" 
                            class="btn btn-sm btn-outline-danger remove-attribute"
                            data-attribute-id="${attributeId}"
                            title="Удалить атрибут">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `;
        
        selectedAttributesContainer.appendChild(attributeItem);
        
        // Remove option from select
        selectedOption.remove();
        
        // Reset select
        attributeSelect.value = '';
        
        // Add remove event listener
        const removeBtn = attributeItem.querySelector('.remove-attribute');
        removeBtn.addEventListener('click', function() {
            removeAttribute(this, attributeId, attributeName, attributeSlug);
        });
        
        attributeCounter++;
        updateAttributesCount();
    }
    
    // Remove attribute
    function removeAttribute(btn, attributeId, attributeName, attributeSlug) {
        const attributeItem = btn.closest('.attribute-item');
        
        // Add option back to select
        const option = document.createElement('option');
        option.value = attributeId;
        option.dataset.name = attributeName;
        option.dataset.slug = attributeSlug;
        option.textContent = `${attributeName} (${attributeSlug})`;
        attributeSelect.appendChild(option);
        
        // Sort options alphabetically
        const options = Array.from(attributeSelect.options).slice(1);
        options.sort((a, b) => a.textContent.localeCompare(b.textContent));
        
        // Clear and re-add sorted options
        attributeSelect.innerHTML = '<option value="">-- Выберите атрибут --</option>';
        options.forEach(opt => attributeSelect.appendChild(opt));
        
        // Remove attribute item
        attributeItem.remove();
        
        // Show no attributes message if needed
        if (selectedAttributesContainer.querySelectorAll('.attribute-item').length === 0) {
            const message = document.createElement('div');
            message.className = 'alert alert-info mb-0';
            message.id = 'no-attributes-message';
            message.innerHTML = '<i class="bi bi-info-circle me-2"></i>Атрибуты не добавлены. Выберите атрибут из списка ниже.';
            selectedAttributesContainer.appendChild(message);
        }
        
        updateAttributesCount();
    }
    
    // Event listeners
    if (addAttributeBtn) {
        addAttributeBtn.addEventListener('click', addAttribute);
    }
    
    // Add existing remove buttons listeners
    document.querySelectorAll('.remove-attribute').forEach(btn => {
        const attributeItem = btn.closest('.attribute-item');
        const attributeId = btn.dataset.attributeId;
        const attributeName = attributeItem.querySelector('input[name*="[name]"]').value;
        const attributeSlug = attributeItem.querySelector('input[name*="[slug]"]').value;
        
        btn.addEventListener('click', function() {
            removeAttribute(this, attributeId, attributeName, attributeSlug);
        });
    });
    
    // Initialize count
    updateAttributesCount();
});
</script>
@endpush

<style>
.attribute-item {
    transition: all 0.2s ease;
}

.attribute-item:hover {
    background-color: #f8f9fa;
}

#attribute-select {
    max-width: 400px;
}

.remove-attribute:hover {
    background-color: #dc3545;
    color: white;
}
</style>
