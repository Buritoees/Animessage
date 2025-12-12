const form = document.querySelector(".login form"),
    continueBtn = form.querySelector(".button input"),
    errorText = form.querySelector(".error-text"); // Retained but not used for error display

// 検 NEW MODAL ELEMENT REFERENCES 検
const modalOverlay = document.getElementById('dynamicModalOverlay'),
    dynamicModal = document.getElementById('dynamicModal'),
    modalIcon = document.getElementById('modalIcon'),
    modalTitle = document.getElementById('modalTitle'),
    modalMessage = document.getElementById('modalMessage'),
    modalActionLink = document.getElementById('modalActionLink');


// 1. Get ALL elements that need the effect
const spotlightTargets = [
    document.querySelector(".signup-header h1"), // Title (Text effect)
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
        // Euclidean distance from center (Why the effect is strongest at center)
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
        const maxDistanceForFade = (Math.max(rect.width, rect.height) / 2) + 80;

        // This quadratic formula ensures max opacity (1) when distance is 0 (center) and min opacity (0) near the edges
        const glowOpacity = Math.pow(Math.max(0, 1 - (distance / maxDistanceForFade)), 2);

        // Only proceed if the glow opacity is above a minimum threshold
        if (glowOpacity > 0.05) {

            // 3. Apply the dynamic effect (different for title vs buttons)
            if (el.tagName === 'H1') {
                // H1: TEXT SPOTLIGHT EFFECT (Oblong)

                // 泊 FIX: Changed 'circle ${radius}' to 'ellipse 300px 60px'
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

                // 泊 FIX: Changed 'circle ${radius}' to 'ellipse 250px 100px'
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

/**
 * Displays the custom registration modal with login error details.
 * @param {string} message The raw error message from the backend.
 */
const displayLoginErrorModal = (message) => {
    // Reset classes
    dynamicModal.classList.remove('success', 'error');
    modalActionLink.style.display = 'none';

    // Set content and styling based on the message
    // 🔒 UPDATED: Check for new, more specific PHP error messages
    if (message.includes('Invalid account') || message.includes('does not exist')) {
        // User not found
        modalTitle.textContent = "Login Failed";
        modalMessage.textContent = "Invalid account. The email address you entered does not exist.";
        modalIcon.className = "modal-icon fas fa-user-slash";
        dynamicModal.classList.add('error');

    } else if (message.includes('Invalid password') || message.includes('check your password')) {
        // Incorrect password
        modalTitle.textContent = "Authentication Error";
        modalMessage.textContent = "Invalid password. Please check your password and try again.";
        modalIcon.className = "modal-icon fas fa-lock";
        dynamicModal.classList.add('error');

    } else {
        // General Error / Missing Fields (e.g., 'All fields are required' or DB error)
        modalTitle.textContent = "Login Error";
        modalMessage.textContent = message;
        modalIcon.className = "modal-icon fas fa-exclamation-triangle";
        dynamicModal.classList.add('error');
    }

    // Show the modal
    modalOverlay.classList.add('active');
}


// --- FORM SUBMISSION LOGIC (Updated to use Modal) ---

// CRITICAL: Ensure the default form submission is prevented
form.onsubmit = (e) => {
    e.preventDefault();
}

continueBtn.onclick = () => {
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "php/login.php", true);

    // Hide the modal/inline error before sending a new request
    modalOverlay.classList.remove('active');
    errorText.style.display = "none";

    xhr.onload = () => {
        if (xhr.readyState === 4 && xhr.status === 200) {
            // CRITICAL: Trim any invisible characters (spaces, newlines) from the response
            let data = xhr.response.trim();

            // Check if the trimmed response starts with "success"
            if (data.startsWith("success")) {

                // --- REDIRECTION LOGIC (ROBUST) ---
                if (data === "success_admin") {
                    // Use replace() for a cleaner, forced redirect
                    window.location.replace("admin.php");
                } else {
                    // Regular user success
                    window.location.replace("chatPage.php");
                }
                // --- END REDIRECTION LOGIC ---

            } else {
                // 泊 FIX: Display error message using the new modal GUI
                displayLoginErrorModal(data);
            }
        }
    }
    let formData = new FormData(form);
    xhr.send(formData);
}