// bootstrap.js

import "bootstrap";

// Axios Configuration
import axios from "axios";
window.axios = axios;
window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

import Echo from "laravel-echo";

window.Echo = new Echo({
    broadcaster: "reverb",
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST || window.location.hostname,
    wsPort: import.meta.env.VITE_REVERB_PORT || 6001,
    wssPort: import.meta.env.VITE_REVERB_PORT || 443,
    forceTLS: import.meta.env.VITE_REVERB_SCHEME === "https",
    enabledTransports: ["ws", "wss"],
});

// Toastr Configuration
import toastr from "toastr";
import "toastr/build/toastr.min.css";

window.toastr = toastr;
toastr.options = {
    closeButton: true,
    debug: false,
    newestOnTop: true,
    progressBar: true,
    positionClass: "toast-top-right",
    preventDuplicates: true,
    onclick: null,
    showDuration: "300",
    hideDuration: "1000",
    timeOut: "5000",
    extendedTimeOut: "1000",
    showEasing: "swing",
    hideEasing: "linear",
    showMethod: "fadeIn",
    hideMethod: "fadeOut",
};

// End of bootstrap.js

// app.js

import "./bootstrap";
import { createApp } from "vue";

const app = createApp({});

// Example Vue Component
import ExampleComponent from "./components/ExampleComponent.vue";
app.component("example-component", ExampleComponent);

// Mount Vue Application
app.mount("#app");

// Toastr Example Usage (Optional for Testing)
window.toastr.success("App loaded successfully!");
