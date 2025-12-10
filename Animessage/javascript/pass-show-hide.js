const togglePasswordVisibility = (inputField, icon) => {
    // 1. Toggle the input type between 'password' and 'text'
    if (inputField.type === "password") {
        inputField.type = "text";
    } else {
        inputField.type = "password";
    }
    
    // 2. Toggle the icon classes. This class change triggers the CSS animation.
    icon.classList.toggle("fa-eye");
    icon.classList.toggle("fa-eye-slash");
};

// Select all icons that are direct siblings of an input element within a field
const passwordIcons = document.querySelectorAll('.field.input input + i.fas'); 

passwordIcons.forEach(icon => {
    // The password input field is the immediate previous sibling of the icon
    const inputField = icon.previousElementSibling;
    
    // Safety check to ensure we found an input field of type password/text
    if (inputField && (inputField.type === 'password' || inputField.type === 'text')) {
        icon.style.cursor = 'pointer'; 
        
        // Attach the click handler
        icon.addEventListener('click', () => {
            togglePasswordVisibility(inputField, icon);
        });
    }
});