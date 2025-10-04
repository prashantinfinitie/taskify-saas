document.addEventListener("DOMContentLoaded", function () {
    const addFeatureBtn = document.getElementById("addFeature");
    if (!addFeatureBtn) return;

    let featureIndex = parseInt(addFeatureBtn.dataset.featureCount) || 1;

    addFeatureBtn.addEventListener("click", function () {
        const container = document.getElementById("featuresContainer");
        const newFeature = document.createElement("div");
        newFeature.classList.add("feature-item", "border", "rounded", "p-3", "mb-3", "position-relative");
        newFeature.setAttribute("data-index", featureIndex);
        newFeature.innerHTML = `
            <!-- Header with title and remove button -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0 text-primary">Feature Item ${featureIndex + 1}</h6>
                <button type="button" class="btn btn-danger btn-sm remove-feature" 
                    onclick="removeFeature(this)" data-bs-toggle="tooltip" 
                    data-bs-original-title="Remove Feature">
                    <i class='bx bx-trash'></i>
                </button>
            </div>
            
            <div class="row">
                <div class="mb-3 col-md-4">
                    <label class="form-label">Feature Title</label>
                    <input type="text" class="form-control" name="features[${featureIndex}][title]"
                        placeholder="e.g., Project Management" required>
                </div>
                <div class="mb-3 col-md-8">
                    <label class="form-label">Feature Description</label>
                    <textarea class="form-control" name="features[${featureIndex}][description]" rows="2"
                        placeholder="Brief description of the feature..." required></textarea>
                </div>
                <div class="mb-3 col-md-6">
                    <label class="form-label">Feature Icon</label>
                    <input type="file" accept="image/*" class="form-control" name="features[${featureIndex}][icon]">
                    <small class="text-muted">Supported formats: JPG, PNG, SVG</small>
                </div>
            </div>
        `;
        container.appendChild(newFeature);
        
        // Initialize tooltip for the new remove button
        const tooltipElements = newFeature.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltipElements.forEach(element => {
            new bootstrap.Tooltip(element);
        });
        
        featureIndex++;
    });
});

function removeFeature(button) {
    const featureItems = document.querySelectorAll(".feature-item");
    if (featureItems.length > 1) {
        // Dispose tooltip before removing element
        const tooltip = bootstrap.Tooltip.getInstance(button);
        if (tooltip) {
            tooltip.dispose();
        }
        
        button.closest(".feature-item").remove();
        
        // Reindex remaining features
        document.querySelectorAll(".feature-item").forEach((item, index) => {
            item.setAttribute("data-index", index);
            
            // Update the header title
            const headerTitle = item.querySelector("h6.text-primary");
            if (headerTitle) {
                headerTitle.textContent = `Feature Item ${index + 1}`;
            }
            
            // Update form field names
            const titleInput = item.querySelector('input[name*="[title]"]');
            if (titleInput) {
                titleInput.name = `features[${index}][title]`;
            }
            
            const descriptionTextarea = item.querySelector('textarea[name*="[description]"]');
            if (descriptionTextarea) {
                descriptionTextarea.name = `features[${index}][description]`;
            }
            
            const iconInput = item.querySelector('input[name*="[icon]"]');
            if (iconInput) {
                iconInput.name = `features[${index}][icon]`;
            }
        });
        
        // Update the global feature index
        featureIndex = document.querySelectorAll(".feature-item").length;
    } else {
        toastr.error("At least one feature item is required.");
    }
}