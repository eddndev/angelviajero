/**
 * We use a global state variable to track the last known value of the attribute selector.
 * This allows us to detect changes when Nova's searchable component updates the hidden input.
 */
let lastAttributeId = null;

/**
 * Main function that checks the current state of the form.
 * It's designed to be called repeatedly by the MutationObserver on any DOM change.
 */
function findAndCheckFormState() {
    // Check if we are on the correct Nova resource page.
    const onAttributeValueForm = window.location.pathname.includes('/resources/attribute-values/');
    if (!onAttributeValueForm) {
        // If we navigate away, reset the state to allow re-initialization later.
        if (lastAttributeId !== null) {
            lastAttributeId = null;
        }
        return;
    }

    // For a searchable BelongsTo field, Nova uses a hidden input to store the selected ID.
    // This is the correct element to monitor.
    const hiddenInput = document.querySelector('[data-field="attribute-selector"] input[type="hidden"]');

    // If the hidden input doesn't exist yet (e.g., form is still loading), do nothing.
    if (!hiddenInput) return;

    const currentAttributeId = hiddenInput.value;

    // This is the core logic: we only act if the current value is different from the last one we saw.
    // This effectively simulates a "change" event.
    if (currentAttributeId !== lastAttributeId) {
        // Update our state with the new value.
        lastAttributeId = currentAttributeId;
        // Trigger the logic to show/hide the conditional fields.
        toggleFieldsVisibility(currentAttributeId);
    }
}

/**
 * Toggles the visibility of form fields based on the selected Attribute's display_type.
 * @param {string|null} attributeId The ID of the selected attribute.
 */
async function toggleFieldsVisibility(attributeId) {
    // Select the field wrappers using their data-attributes.
    const standardValueWrapper = document.querySelector('[data-field="standard-value-field"]');
    const colorNameWrapper = document.querySelector('[data-field="color-name-field"]');
    const colorPickerWrapper = document.querySelector('[data-field="color-picker-field"]');

    // Safety check in case the fields are not yet rendered.
    if (!standardValueWrapper || !colorNameWrapper || !colorPickerWrapper) return;
    
    // If no attribute is selected (ID is empty), default to the standard view.
    if (!attributeId) {
        updateFieldVisibility(true, standardValueWrapper, colorNameWrapper, colorPickerWrapper);
        return;
    }

    try {
        // Make an authenticated API call via Nova's helper to check the display type.
        const { data } = await Nova.request().get(`/api/nova-tools/attribute/${attributeId}/check-display-type`);
        
        if (data.is_color_swatch) {
            // It's a color swatch, show the color-specific fields.
            updateFieldVisibility(false, standardValueWrapper, colorNameWrapper, colorPickerWrapper);
        } else {
            // It's a standard attribute, show the standard value field.
            updateFieldVisibility(true, standardValueWrapper, colorNameWrapper, colorPickerWrapper);
        }
    } catch (error) {
        console.error('Error checking attribute display type:', error);
        // On error, revert to the safest default view.
        updateFieldVisibility(true, standardValueWrapper, colorNameWrapper, colorPickerWrapper);
    }
}

/**
 * Handles the actual DOM manipulation to show/hide fields.
 * @param {boolean} showStandard Should the standard field be visible?
 * @param {HTMLElement} standardWrapper The wrapper for the standard value field.
 * @param {HTMLElement} colorNameWrapper The wrapper for the color name field.
 * @param {HTMLElement} colorPickerWrapper The wrapper for the color picker field.
 */
function updateFieldVisibility(showStandard, standardWrapper, colorNameWrapper, colorPickerWrapper) {
    // The element selected by `data-field` is the entire field wrapper,
    // so we can toggle its display property directly.
    standardWrapper.style.display = showStandard ? '' : 'none';
    colorNameWrapper.style.display = showStandard ? 'none' : '';
    colorPickerWrapper.style.display = showStandard ? 'none' : '';
}

// Create a MutationObserver to watch for Nova's dynamic page changes.
const observer = new MutationObserver(findAndCheckFormState);

// We target a stable, high-level element within Nova's layout (#nova is the root Vue app).
const novaApp = document.querySelector('#nova');
if (novaApp) {
    observer.observe(novaApp, {
        childList: true, // Watch for added/removed nodes.
        subtree: true    // Watch all descendants of #nova.
    });
}

