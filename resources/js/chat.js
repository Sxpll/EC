// Nasłuchiwanie na kanale 'chat'
window.Echo.channel("chat").listen("MessageSent", (e) => {
    console.log(`Nowa wiadomość od ${e.user}: ${e.message}`);
});

// Wysyłanie wiadomości
document.getElementById("send-button").addEventListener("click", () => {
    const message = document.getElementById("message-input").value;
    const user = document.getElementById("user-input").value;

    axios
        .post("/send-message", {
            message: message,
            user: user,
        })
        .then((response) => {
            console.log(response.data.status);
        })
        .catch((error) => {
            console.error("Error sending message:", error);
        });
});
