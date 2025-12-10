// --- javascript/cursor-follow-effect.js (REVISED) ---

document.addEventListener('DOMContentLoaded', () => {
    const shinobuImage = document.querySelector('.shinobu');
    
    if (!shinobuImage) return;

    // *** Key Change: Reduced sensitivity for a tighter, subtle follow ***
    // This value determines how much the image moves relative to the cursor position.
    // Recommended range: 0.01 (very subtle) to 0.05 (more noticeable floating).
    const sensitivity = 0.02; // Change from 0.04/0.05 to 0.02 for tighter control

    // Store the initial calculated position of the image on the screen
    const initialRect = shinobuImage.getBoundingClientRect();
    const initialX = initialRect.left + initialRect.width / 2;
    const initialY = initialRect.top + initialRect.height / 2;

    document.addEventListener('mousemove', (e) => {
        // 1. Calculate the difference between the cursor position and the image's initial position
        const mouseX = e.clientX;
        const mouseY = e.clientY;

        // Calculate the difference (delta) from the initial position
        const diffX = mouseX - initialX;
        const diffY = mouseY - initialY;

        // 2. Apply the sensitivity to the difference
        // This ensures the image only moves by a fraction of the total cursor movement
        const translateX = diffX * sensitivity; 
        const translateY = diffY * sensitivity; 

        // 3. Apply the movement using CSS transform
        // The -50%, -50% centers the image, and the calculated translation moves it based on the cursor.
        shinobuImage.style.transform = `translate(-50%, -50%) translate(${translateX}px, ${translateY}px)`;
    });
});