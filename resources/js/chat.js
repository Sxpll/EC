document.addEventListener("DOMContentLoaded", () => {
    const chatId = document.getElementById("chat-id")?.value;
    const chatWindow = document.getElementById("chat-window");
    const sendButton = document.getElementById("send-button");
    const messageInput = document.getElementById("message-input");

    window.notificationBannerShown = false; // Globalna zmienna dla powiadomień
    let lastMessageId = null; // Do śledzenia ostatniej wiadomości

    // Funkcja odświeżania wiadomości
    function refreshMessages(chatId) {
        axios
            .get(`/chat/${chatId}/messages`)
            .then((response) => {
                const messages = response.data; // Bezpośrednio przypisujemy odpowiedź

                // Sprawdzenie, czy dane są tablicą
                if (!Array.isArray(messages)) {
                    console.error(
                        "Oczekiwano tablicy, ale otrzymano:",
                        messages
                    );
                    return;
                }

                chatWindow.innerHTML = ""; // Wyczyszczenie okna czatu

                // Iteracja po wiadomościach i ich renderowanie
                messages.forEach((message) => {
                    appendMessage(
                        message,
                        message.user && message.user.id === userId
                            ? "self"
                            : "other"
                    );
                });
            })
            .catch((error) => {
                console.error("Błąd odświeżania wiadomości:", error);
            });
    }

    // Funkcja dodawania wiadomości do okna czatu
    function appendMessage(message, type) {
        const userName = type === "self" ? "Ty" : "Użytkownik";
        const messageContent = message.message || "Brak treści";

        const messageDiv = document.createElement("div");
        messageDiv.classList.add("message", type === "self" ? "self" : "other");
        messageDiv.innerHTML = `
        <strong>${userName}:</strong> ${messageContent}
        <span class="message-time">${new Date(
            message.created_at
        ).toLocaleTimeString()}</span>
    `;
        chatWindow.appendChild(messageDiv);
        chatWindow.scrollTop = chatWindow.scrollHeight; // Przewiń na dół
    }

    // Funkcja wysyłania wiadomości
    function sendMessage(chatId, message) {
        if (message.trim() === "") {
            toastr.warning("Nie możesz wysłać pustej wiadomości!");
            return;
        }

        axios
            .post(`/chat/${chatId}/send-message`, { message })
            .then(() => {
                appendMessage(
                    { user: { name: "Ty" }, content: message },
                    "self"
                );
                messageInput.value = ""; // Wyczyszczenie pola
                toastr.success("Wiadomość wysłana!");
            })
            .catch((error) => {
                toastr.error("Wystąpił problem podczas wysyłania wiadomości.");
                console.error("Błąd:", error);
            });
    }

    // Inicjalizacja interakcji z czatem
    if (sendButton && messageInput && chatId) {
        sendButton.addEventListener("click", () => {
            sendMessage(chatId, messageInput.value);
        });

        messageInput.addEventListener("keydown", (e) => {
            if (e.key === "Enter" && !e.shiftKey) {
                e.preventDefault();
                sendMessage(chatId, messageInput.value);
            }
        });
    }

    // Funkcja uruchamiająca auto-odświeżanie wiadomości
    function startAutoRefresh(chatId) {
        setInterval(() => {
            refreshMessages(chatId);
        }, 5000); // Odświeżanie co 5 sekund
    }

    // Rozpoczęcie auto-odświeżania wiadomości
    if (chatId) {
        startAutoRefresh(chatId);
    }
});
