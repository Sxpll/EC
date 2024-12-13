// Import Bootstrap
import "bootstrap";

// Axios Configuration
import axios from "axios";
window.axios = axios;

// Configure Axios Headers
window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

// Add CSRF Token to Axios
const csrfToken = document
    .querySelector('meta[name="csrf-token"]')
    .getAttribute("content");
window.axios.defaults.headers.common["X-CSRF-TOKEN"] = csrfToken;
