let chatInterval; // Global variable to hold the setInterval ID

/**
 * Global function to load the chat UI and messages for a given user ID.
 * This is called from users.js when a user link is clicked.
 */
function loadChatArea(userId) {
    const chatContainer = document.getElementById('dynamic-chat-container');
    const placeholder = document.getElementById('chat-placeholder');
    const chatContentArea = document.getElementById('chat-content-area');

    // 1. Clear any existing chat interval
    if (chatInterval) {
        clearInterval(chatInterval);
    }

    // 2. Hide placeholder and show loading state
    placeholder.style.display = 'none';
    chatContentArea.classList.remove('no-chat-selected');
    chatContainer.innerHTML = 'Loading chat...';

    // 3. AJAX to fetch the complete chat UI (header, box, form)
    let xhr = new XMLHttpRequest();
    // *** IMPORTANT: Changed path to use the file you uploaded: get-chat-ui.php ***
    xhr.open("GET", `php/get-chat-ui.php?user_id=${userId}`, true);

    xhr.onload = () => {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                // SUCCESS: Inject HTML and initialize chat
                chatContainer.innerHTML = xhr.response;
                initializeChat(userId, chatContainer); // Pass the container element

                // For mobile view: Add the class to slide the chat panel in
                chatContentArea.classList.add('chat-active');
            } else {
                console.error("AJAX Error loading chat UI. Status:", xhr.status, "Response:", xhr.responseText);
                chatContainer.innerHTML = '<p style="text-align: center; color: red;">Error loading chat UI. Check console (F12) for details.</p>';
            }
        }
    }

    xhr.onerror = () => {
        console.error("Network Error attempting to fetch chat UI.");
        chatContainer.innerHTML = '<p style="text-align: center; color: red;">Network Error loading chat UI. Check console (F12) for details.</p>';
    }

    xhr.send();
}


/**
 * Initializes the form submission and auto-refresh for the loaded chat area.
 * @param {string} incoming_id The unique ID of the user being chatted with.
 * @param {Element} container The dynamic-chat-container element to scope searches.
 */
function initializeChat(incoming_id, container) {
    // Select elements SCOPED to the container that just loaded the HTML
    const form = container.querySelector(".typing-area"),
        inputField = form ? form.querySelector(".input-field") : null,
        sendBtn = form ? form.querySelector("button") : null,
        chatBox = container.querySelector(".chat-box");

    // Safety check: if the required elements aren't found, stop
    if (!form || !inputField || !sendBtn || !chatBox) {
        console.error("Chat elements not found in dynamic container. Check the HTML output of get-chat-ui.php.");
        return;
    }

    // --- Message Insertion Logic ---
    form.onsubmit = (e) => {
        e.preventDefault();
    }

    inputField.focus();
    inputField.onkeyup = () => {
        if (inputField.value !== "") {
            sendBtn.classList.add("active");
        } else {
            sendBtn.classList.remove("active");
        }
    }

    sendBtn.onclick = () => {
        let xhr = new XMLHttpRequest();
        xhr.open("POST", "php/insert-chat.php", true);
        xhr.onload = () => {
            if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
                inputField.value = "";
                getChat(incoming_id, chatBox); // Immediate refresh after sending
            }
        }
        let formData = new FormData(form);
        xhr.send(formData);
    }

    // --- Auto-Refresh Logic (getChat) ---
    const getChat = (id, box) => {
        let xhr = new XMLHttpRequest();
        xhr.open("POST", "php/get-chat.php", true);
        xhr.onload = () => {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    let data = xhr.response;
                    const isNewMessage = data.trim() !== box.innerHTML.trim();
                    box.innerHTML = data;

                    if (!box.classList.contains("active") && isNewMessage) {
                        scrollToBottom(box);
                    }
                } else {
                    console.error("AJAX Error fetching messages from get-chat.php. Status:", xhr.status, "Response:", xhr.responseText);
                }
            }
        }
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.send("incoming_id=" + id);
    };

    // Initial load of messages
    getChat(incoming_id, chatBox);

    // Start the interval for message refresh
    // Assigning to window.chatInterval makes it globally accessible for the back button
    window.chatInterval = setInterval(() => {
        getChat(incoming_id, chatBox);
    }, 500);

    // --- Scroll Lock on Hover ---
    chatBox.onmouseenter = () => {
        chatBox.classList.add("active");
    }

    chatBox.onmouseleave = () => {
        chatBox.classList.remove("active");
    }
}

function scrollToBottom(element) {
    element.scrollTop = element.scrollHeight;
}