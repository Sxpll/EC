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

window.Echo = new Echo({
    broadcaster: "reverb",
    host: `${window.location.hostname}:6001`, // Port 6001 musi odpowiadać Twojemu Reverb App Port
});
