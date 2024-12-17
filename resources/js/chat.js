document.addEventListener("DOMContentLoaded", () => {
    const chatWindow = document.getElementById("chat-window");
    const sendButton = document.getElementById("send-button");
    const messageInput = document.getElementById("message-input");
    const chatId = document.getElementById("chat-id")?.value;
    const userId = document.getElementById("user-id")?.value;

    if (!chatId) {
        console.error("Chat ID is not set");
        return;
    }

    function appendMessage(message, isOwnMessage) {
        const messageDiv = document.createElement("div");

        // Określenie klasy wiadomości (zależne od użytkownika)
        const messageClass = isOwnMessage ? "user" : "other";

        messageDiv.classList.add("message", messageClass);

        // Określenie nazwy nadawcy (lub "Ty" dla zalogowanego użytkownika)
        const senderName =
            message.user.id == userId
                ? "Ty"
                : `${message.user.name || "Nieznany"} ${
                      message.user.lastname || ""
                  }`.trim();

        messageDiv.innerHTML = `
        <strong>${senderName}:</strong> ${message.message}
        <span class="message-time">${new Date(
            message.created_at
        ).toLocaleTimeString()}</span>
    `;

        chatWindow.appendChild(messageDiv);
        chatWindow.scrollTop = chatWindow.scrollHeight;
    }

    // Send message
    function sendMessage(message) {
        if (message.trim() === "") {
            toastr.warning("Nie możesz wysłać pustej wiadomości!");
            return;
        }

        axios
            .post(`/chat/${chatId}/send-message`, { message })
            .then((response) => {
                appendMessage(response.data.message, true);
                messageInput.value = ""; // Clear input
                toastr.success("Wiadomość wysłana!");
            })
            .catch((error) => {
                console.error("Błąd podczas wysyłania wiadomości:", error);
                toastr.error("Wystąpił problem podczas wysyłania wiadomości.");
            });
    }

    // Handle send button click
    sendButton?.addEventListener("click", () => {
        sendMessage(messageInput.value);
    });

    // Handle Enter key press
    messageInput?.addEventListener("keydown", (e) => {
        if (e.key === "Enter" && !e.shiftKey) {
            e.preventDefault();
            sendMessage(messageInput.value);
        }
    });

    window.Echo.channel(`chat.${chatId}`).listen("MessageSent", (e) => {
        const isOwnMessage = e.message.user.id == userId;
        appendMessage(e.message, isOwnMessage);
    });
});
