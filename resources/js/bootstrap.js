import "bootstrap";

/**
 * Ładowanie axios do obsługi żądań HTTP
 */
import axios from "axios";
window.axios = axios;

window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

/**
 * Konfiguracja Laravel Echo do korzystania z Reverb jako broadcastera
 */
import Echo from "laravel-echo";

window.Pusher = require("pusher-js");

window.Echo = new Echo({
    broadcaster: "reverb",
    host: `${window.location.hostname}:6001`,
});
