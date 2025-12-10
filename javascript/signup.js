const form = document.querySelector(".signup form"),
continueBtn = form.querySelector(".button input");
// errorText = form.querySelector(".error-text"); // Removed usage of old error text

// 🌟 NEW MODAL ELEMENT REFERENCES 🌟
const modalOverlay = document.getElementById('dynamicModalOverlay'),
      dynamicModal = document.getElementById('dynamicModal'),
      modalIcon = document.getElementById('modalIcon'),
      modalTitle = document.getElementById('modalTitle'),
      modalMessage = document.getElementById('modalMessage'),
      modalActionLink = document.getElementById('modalActionLink');


// 1. Get ALL elements that need the effect
const spotlightTargets = [
    document.querySelector(".signup-header h1"), // Title (Text effect)
   //document.querySelector(".image-attach-label"), // Image button (Background effect - Label)
    document.querySelector(".auth-buttons-group .primary-btn input") // Primary button (Background effect - Input)
];

// Constants for effect
const fadeThreshold = 350; 

/**
 * Handles the dynamic spotlight effect for all target elements.
 */
const handleSpotlight = (e) => {
    
    // Stop the spotlight effect if the modal is active (behind the blur)
    if (modalOverlay && modalOverlay.classList.contains('active')) {
        return;
    }
    
    spotlightTargets.forEach(el => {
        if (!el) return; // Skip if element not found

        const rect = el.getBoundingClientRect();
        
        const mouseX = e.clientX;
        const mouseY = e.clientY;

        // 1. Calculate center and distance
        const centerX = rect.left + (rect.width / 2);
        const centerY = rect.top + (rect.height / 2);

        const distanceX = mouseX - centerX;
        const distanceY = mouseY - centerY;
        // Euclidean distance from center
        const distance = Math.sqrt(distanceX * distanceX + distanceY * distanceY); 

        // Calculate position relative to the element's top-left corner
        const x = mouseX - rect.left;
        const y = mouseY - rect.top;

        // Convert to percentage for the radial-gradient position in CSS
        const xPercent = (x / rect.width) * 100;
        const yPercent = (y / rect.height) * 100;
        
        // Get element-specific CSS variables
        const style = getComputedStyle(el);
        const baseColor = style.getPropertyValue('--base-color').trim();
        const accentColor = style.getPropertyValue('--accent-color').trim();
        
        // Increase the max distance for the fade to enable proximity effect.
        const maxDistanceForFade = (Math.max(rect.width, rect.height) / 2) + 200; 
        
        // Calculate 'glow opacity' based on distance from the center for the smooth fade
        const glowOpacity = Math.pow(Math.max(0, 1 - (distance / maxDistanceForFade)), 1);

        // Only proceed if the glow opacity is above a minimum threshold
        if (glowOpacity > 0.05) { 
            
            // 3. Apply the dynamic effect (different for title vs buttons)
            if (el.tagName === 'H1') {
                // H1: TEXT SPOTLIGHT EFFECT (Oblong)
                el.style.background = `radial-gradient(
                    ellipse 300px 60px at ${xPercent}% ${yPercent}%,
                    ${accentColor} 0%,
                    ${baseColor} 100%
                )`;
                // Keep text clipping properties for the H1
                el.style.webkitBackgroundClip = 'text';
                el.style.backgroundClip = 'text';
                el.style.webkitTextFillColor = 'transparent';
                
                // Dynamic text shadow for white glow
                el.style.textShadow = `0 0 15px rgba(255, 255, 255, ${glowOpacity * 0.8})`;
                
            } else {
                // BUTTONS: BACKGROUND SPOTLIGHT EFFECT (Oblong & Fixed Intensity)
                el.style.background = `radial-gradient(
                    ellipse 250px 100px at ${xPercent}% ${yPercent}%, 
                    rgba(255, 255, 255, 0.7) 20%, 
                    rgba(255, 255, 255, 0) 95% 
                ), var(--base-color)`; 
                
                // Fixed Box Shadow for subtle glow (Primary button only)
                if (el.closest('.primary-btn')) {
                     el.style.boxShadow = `0 0 25px 3px rgba(255, 255, 255, 0.5)`;
                }
            }
            
        } else {
            // --- CURSOR IS NOT IN PROXIMITY: Reset to default background ---
            
            // 4. Reset inline styles to let CSS rules take over
            if (el.tagName === 'H1') {
                 // H1: Reset all inline styles related to the effect
                el.style.background = el.style.webkitBackgroundClip = el.style.backgroundClip = el.style.webkitTextFillColor = el.style.textShadow = '';
            } else {
                // BUTTONS: Clear inline background and box-shadow styles
                el.style.background = ''; 
                el.style.boxShadow = '';
            }
        }
    });
}

// Attach the unified handler to the body
document.body.addEventListener("mousemove", handleSpotlight);


// --- NEW FORM SUBMISSION LOGIC ---

form.onsubmit = (e)=>{
    e.preventDefault();
}

// ... (Previous code)

continueBtn.onclick = ()=>{
    // 1. Reset Modal State before AJAX call
    dynamicModal.classList.remove('success', 'error');
    modalActionLink.style.display = 'none';
    
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "php/signup.php", true);
    xhr.onload = ()=>{
        if(xhr.readyState === XMLHttpRequest.DONE){
            if(xhr.status === 200){
                let data;
                try {
                    // 🌟 STEP 1: Parse the JSON string into a JavaScript object
                    data = JSON.parse(xhr.response.trim());
                } catch (e) {
                    // Fallback for non-JSON response (e.g., PHP error, plain text)
                    modalTitle.textContent = "Server Error";
                    modalMessage.textContent = "Received unexpected response from server.";
                    modalIcon.className = "modal-icon fas fa-exclamation-triangle";
                    dynamicModal.classList.add('error');
                    modalOverlay.classList.add('active');
                    return; // Stop execution
                }
                
                // 🌟 STEP 2: Check the 'status' property
                if(data.status && data.status.toLowerCase() === "success"){
                    // 🟢 SUCCESS
                    modalTitle.textContent = "Success!";
                    // 🌟 Use the 'message' property from the JSON
                    modalMessage.textContent = data.message || 'Account successfully created! You can now log in.'; 
                    modalIcon.className = "modal-icon fas fa-check-circle";
                    dynamicModal.classList.add('success'); 
                    modalActionLink.style.display = 'inline-block'; 
                    form.reset(); 
                    
                } else if (data.status && data.status.toLowerCase() === "error") {
                    // ❌ ERROR
                    modalTitle.textContent = "Registration Failed";
                    // 🌟 Use the 'message' property from the JSON
                    modalMessage.textContent = data.message || 'An unknown error occurred during registration.'; 
                    modalIcon.className = "modal-icon fas fa-times-circle";
                    dynamicModal.classList.add('error'); 
                    modalActionLink.style.display = 'none'; 
                } else {
                    // Unhandled response structure
                    modalTitle.textContent = "Unknown Response";
                    modalMessage.textContent = data.message || `Received: ${xhr.response.trim()}`;
                    modalIcon.className = "modal-icon fas fa-exclamation-triangle";
                    dynamicModal.classList.add('error');
                }
                
                // Show the modal
                modalOverlay.classList.add('active');

            } else {
                // ⚠️ HTTP Status error (e.g., 404, 500)
                // ... (existing HTTP error handling)
            }
        }
    }
    let formData = new FormData(form);
    xhr.send(formData);
}