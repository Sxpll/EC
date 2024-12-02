const mix = require("laravel-mix");

mix.js("resources/js/app.js", "public/js")
    .js("resources/js/chat.js", "public/js")
    .postCss("resources/css/app.css", "public/css")
    .sourceMaps();
