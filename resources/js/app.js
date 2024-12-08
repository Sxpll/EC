import "./bootstrap";

import toastr from "toastr";
import "toastr/build/toastr.min.css";

window.toastr = toastr;

// Konfiguracja Echo dla Reverb
window.Echo = new Echo({
    broadcaster: "reverb",
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST || window.location.hostname,
    wsPort: import.meta.env.VITE_REVERB_PORT || 6001,
    wssPort: import.meta.env.VITE_REVERB_PORT || 443,
    forceTLS: import.meta.env.VITE_REVERB_SCHEME === "https",
    enabledTransports: ["ws", "wss"],
});

// Konfiguracja powiadomień Toastr
toastr.options = {
    closeButton: true,
    progressBar: true,
    positionClass: "toast-top-right",
    showDuration: 300,
    hideDuration: 1000,
    timeOut: 5000,
    extendedTimeOut: 1000,
    showEasing: "swing",
    hideEasing: "linear",
    showMethod: "fadeIn",
    hideMethod: "fadeOut",
};

// Nasłuchiwanie na kanale 'chat'
window.Echo.channel("chat").listen("MessageSent", (e) => {
    toastr.info(
        `Nowa wiadomość od ${e.message.user.name}: ${e.message.content}`
    );
    console.log("Nowa wiadomość:", e.message);
});

// Obsługa wysyłania wiadomości
document.addEventListener("DOMContentLoaded", () => {
    const sendButton = document.getElementById("send-button");
    const messageInput = document.getElementById("message-input");
    const chatId = document.getElementById("chat-id").value; // Ukryte pole z ID czatu

    if (sendButton && messageInput && chatId) {
        sendButton.addEventListener("click", () => {
            const message = messageInput.value;

            if (message.trim() === "") {
                toastr.warning("Nie możesz wysłać pustej wiadomości!");
                return;
            }

            axios
                .post(`/chat/${chatId}/send-message`, { content: message })
                .then((response) => {
                    toastr.success("Wiadomość wysłana!");
                    messageInput.value = ""; // Wyczyszczenie pola
                })
                .catch((error) => {
                    console.error("Błąd podczas wysyłania wiadomości:", error);
                    toastr.error(
                        "Wystąpił problem podczas wysyłania wiadomości."
                    );
                });
        });
    }
});
