const searchBar = document.querySelector(".search input"),
      searchIcon = document.querySelector(".search button"),
      usersList = document.querySelector(".users-list");

// --- Search Icon Toggle Logic ---
searchIcon.onclick = () => {
    searchBar.classList.toggle("show");
    searchIcon.classList.toggle("active");
    searchBar.focus();
    // This logic ensures if the search icon is clicked to hide the bar, the search term is cleared
    if (!searchIcon.classList.contains("active")) {
        searchBar.value = "";
        searchBar.classList.remove("active");
    }
}

// --- Real-time Search Logic ---
searchBar.onkeyup = () => {
    let searchTerm = searchBar.value;
    if (searchTerm != "") {
        searchBar.classList.add("active");
    } else {
        searchBar.classList.remove("active");
    }
    
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "php/search.php", true);
    xhr.onload = () => {
        if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
            let data = xhr.response;
            usersList.innerHTML = data;
        }
    }
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.send("searchTerm=" + searchTerm);
}

// --- Auto-Refresh User List (Only when not searching) ---
setInterval(() => {
    // Only refresh the list if the user is NOT actively searching
    if (!searchBar.classList.contains("active")) {
        let xhr = new XMLHttpRequest();
        xhr.open("GET", "php/users.php", true);
        xhr.onload = () => {
            if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
                let data = xhr.response;
                // Double check if the search bar is still inactive before updating
                if (!searchBar.classList.contains("active")) {
                    usersList.innerHTML = data;
                }
            }
        }
        xhr.send();
    }
}, 500);

// --- Delegate Clicks on User List to Load Chat (REVISED) ---
usersList.addEventListener('click', (e) => {
    // 💡 Look for EITHER the custom class OR an <a> tag pointing to chat.php
    let userLink = e.target.closest('.user-link');
    if (!userLink) {
        userLink = e.target.closest('a[href^="chat.php?"]');
    }
    
    if (userLink) {
        e.preventDefault(); // <-- CRITICAL: Stops the browser navigation
        
        // 1. Get the user ID
        let userId = userLink.getAttribute('data-user-id');
        
        // If data-user-id is missing, extract it from the href attribute (fallback)
        if (!userId) {
            const href = userLink.getAttribute('href');
            const match = href.match(/user_id=(\d+)/);
            if (match) {
                userId = match[1];
            }
        }
        
        if (userId) {
            // 2. Update selection styling
            document.querySelectorAll('.user-link').forEach(link => link.classList.remove('active'));
            userLink.classList.add('active');
            
            // 3. Call the function from chat.js to load the content
            if (typeof loadChatArea === 'function') {
                loadChatArea(userId);
            }
        } else {
            console.error("Could not find user ID on the clicked link.");
        }
    }
});