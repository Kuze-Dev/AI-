<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Halcyon Laravel | Tall Boilerplate</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans&display=swap" rel="stylesheet">

    @vite('resources/css/web/app.css')
</head>

<body class="bg-gradient-to-r from-gray-100 via-white to-gray-100">
    <div class="container mx-auto min-h-screen flex flex-col items-center justify-center gap-8 md:gap-12 py-4">
        <div class="flex gap-8 md:gap-12 justify-center items-center px-4 mt-8">
            <a href="https://tailwindcss.com/" target="_blank" rel="noopener noreferrer">
                <svg class="h-14" viewBox="0 0 200 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path class="" fill-rule="evenodd" clip-rule="evenodd"
                        d="M100 0C73.333 0 56.667 13.333 50 40c10-13.333 21.667-18.333 35-15 7.607 1.9 13.044 7.422 19.063 13.53C113.867 48.48 125.215 60 150 60c26.667 0 43.333-13.333 50-40-10 13.333-21.667 18.333-35 15-7.607-1.9-13.044-7.422-19.063-13.53C136.133 11.52 124.785 0 100 0ZM50 60C23.333 60 6.667 73.333 0 100c10-13.333 21.667-18.333 35-15 7.607 1.904 13.044 7.422 19.063 13.53C63.867 108.48 75.215 120 100 120c26.667 0 43.333-13.333 50-40-10 13.333-21.667 18.333-35 15-7.607-1.9-13.044-7.422-19.063-13.53C86.133 71.52 74.785 60 50 60Z"
                        fill="#38BDF8"></path>
                </svg>
            </a>
            <a href="https://alpinejs.dev/" target="_blank" rel="noopener noreferrer">
                <svg class="h-12" viewBox="0 0 200 92" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path class="" fill-rule="evenodd" clip-rule="evenodd"
                        d="M155.556 0 200 44.25l-44.444 44.249-44.445-44.25L155.556 0Z" fill="#77C1D2"></path>
                    <path class="" fill-rule="evenodd" clip-rule="evenodd"
                        d="m44.444 0 92.139 91.735H47.694L0 44.249 44.444 0Z" fill="#2D3441"></path>
                </svg>
            </a>
            <a href="https://laravel.com/" target="_blank" rel="noopener noreferrer">
                <svg class="h-20" viewBox="0 0 200 206" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M199.887 46.582c.074.275.112.558.113.842v44.198a3.226 3.226 0 0 1-1.619 2.796l-37.093 21.358v42.333a3.231 3.231 0 0 1-1.611 2.796l-77.428 44.576c-.177.101-.37.166-.564.234-.072.024-.14.069-.217.089a3.254 3.254 0 0 1-1.652 0c-.088-.024-.169-.073-.253-.105-.178-.065-.363-.121-.532-.218L1.619 160.905A3.23 3.23 0 0 1 0 158.109V25.514c0-.29.04-.572.113-.845.024-.093.08-.178.113-.27.06-.17.116-.343.205-.5.06-.105.149-.19.222-.286.092-.129.177-.262.285-.374.093-.093.214-.162.319-.242.117-.097.221-.202.354-.278h.004L40.323.43a3.23 3.23 0 0 1 3.222 0l38.708 22.288h.008c.13.08.238.181.355.274.104.08.221.153.314.242.113.116.193.25.29.378.068.097.161.181.217.286.093.161.145.33.21.5.032.092.088.177.113.274.074.274.112.557.112.842v82.817l32.256-18.575V47.42c0-.282.04-.568.112-.838.029-.096.081-.18.113-.274.065-.169.121-.342.21-.5.06-.104.149-.188.217-.285.097-.129.177-.262.29-.375.093-.092.21-.16.314-.241.121-.097.226-.202.355-.278h.004l38.712-22.289a3.224 3.224 0 0 1 3.222 0l38.708 22.289c.137.08.241.18.362.274.101.08.218.153.31.241.113.117.194.25.29.379.073.097.162.181.218.286.093.157.145.33.209.5.037.092.089.177.113.273Zm-6.34 43.175V53.003l-13.545 7.8-18.714 10.775v36.754l32.263-18.575h-.004Zm-38.707 66.487v-36.778l-18.408 10.514-52.564 30.002v37.125l70.972-40.863ZM6.453 31.094v125.15l70.963 40.859v-37.117l-37.073-20.983-.012-.008-.016-.008c-.125-.073-.23-.177-.346-.266-.101-.081-.218-.145-.306-.234l-.009-.012c-.104-.1-.177-.225-.265-.338-.081-.109-.178-.202-.242-.314l-.004-.012c-.073-.121-.117-.266-.17-.403-.052-.121-.12-.234-.152-.363v-.004c-.04-.153-.049-.314-.065-.471-.016-.121-.048-.242-.048-.363V49.668l-18.71-10.78-13.545-7.79v-.004ZM41.938 6.948 9.687 25.515 41.93 44.08l32.247-18.57L41.93 6.948h.008ZM58.71 122.817l18.71-10.771V31.094l-13.546 7.798L45.16 49.668v80.952l13.55-7.803Zm99.356-93.959-32.247 18.566 32.247 18.567 32.243-18.57-32.243-18.563Zm-3.226 42.72-18.714-10.776-13.546-7.799v36.754l18.71 10.772 13.55 7.803V71.578Zm-74.202 82.825 47.299-27.006 23.644-13.494-32.223-18.554-37.1 21.361-33.815 19.469 32.195 18.224Z"
                        fill="#FF2D20"></path>
                </svg>
            </a>
            <a href="https://laravel-livewire.com/" target="_blank" rel="noopener noreferrer">
                <svg class="h-20" viewBox="0 0 200 228" height="228" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M189.821 151.122c-3.708 5.607-6.525 12.514-14.065 12.514-12.688 0-13.374-19.565-26.069-19.565-12.695 0-12.009 19.565-24.698 19.565-12.688 0-13.374-19.565-26.07-19.565-12.695 0-12.009 19.565-24.697 19.565-12.688 0-13.374-19.565-26.07-19.565-12.695 0-12.009 19.565-24.697 19.565-3.988 0-6.79-1.932-9.152-4.582C5.223 143.253 0 124.737 0 104.941 0 46.984 44.772 0 100 0s100 46.984 100 104.941c0 16.571-3.66 32.246-10.179 46.181Z"
                        fill="#FB70A9"></path>
                    <mask id="a" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="31"
                        y="122" width="133" height="106">
                        <path
                            d="M60.065 140.762v43.467c0 7.8-6.323 14.123-14.123 14.123-7.8 0-14.124-6.323-14.124-14.123v-52.474c2.636-4.838 5.647-9.028 11.076-9.028 8.838 0 11.926 11.102 17.17 18.035Zm53.355 2.265v68.735c0 8.667-7.026 15.693-15.693 15.693-8.666 0-15.692-7.026-15.692-15.693v-77.795c2.955-5.698 6-11.24 12.245-11.24 9.885 0 12.577 13.891 19.14 20.3Zm50.216-1.262v49.9c0 7.801-6.323 14.124-14.123 14.124-7.8 0-14.123-6.323-14.123-14.124v-61.332c2.459-4.209 5.387-7.606 10.275-7.606 9.278 0 12.219 12.236 17.971 19.038Z"
                            fill="#fff"></path>
                    </mask>
                    <g mask="url(#a)">
                        <path class=""
                            d="M60.065 140.762v43.467c0 7.8-6.323 14.123-14.123 14.123-7.8 0-14.124-6.323-14.124-14.123v-52.474c2.636-4.838 5.647-9.028 11.076-9.028 8.838 0 11.926 11.102 17.17 18.035Zm53.355 2.265v68.735c0 8.667-7.026 15.693-15.693 15.693-8.666 0-15.692-7.026-15.692-15.693v-77.795c2.955-5.698 6-11.24 12.245-11.24 9.885 0 12.577 13.891 19.14 20.3Zm50.216-1.262v49.9c0 7.801-6.323 14.124-14.123 14.124-7.8 0-14.123-6.323-14.123-14.124v-61.332c2.459-4.209 5.387-7.606 10.275-7.606 9.278 0 12.219 12.236 17.971 19.038Z"
                            fill="#4E56A6"></path>
                    </g>
                    <mask id="b" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="31"
                        y="97" width="133" height="73">
                        <path
                            d="M60.065 154.084c-2.505-3.068-5.471-5.344-9.76-5.344-10.185 0-12.054 12.835-18.487 18.622v-55.365c0-7.8 6.323-14.123 14.124-14.123 7.8 0 14.123 6.323 14.123 14.123v42.087Zm53.355.99c-2.672-3.555-5.795-6.334-10.535-6.334-11.33 0-12.37 15.887-20.85 20.28v-24.485c0-8.667 7.026-15.693 15.692-15.693 8.667 0 15.693 7.026 15.693 15.693v10.539Zm50.216-2.748c-2.174-2.14-4.752-3.586-8.17-3.586-10.977 0-12.295 14.909-20.076 19.835v-49.696c0-7.8 6.323-14.124 14.123-14.124 7.8 0 14.123 6.324 14.123 14.124v33.447Z"
                            fill="#fff"></path>
                    </mask>
                    <g mask="url(#b)">
                        <path
                            d="M60.065 154.084c-2.505-3.068-5.471-5.344-9.76-5.344-10.185 0-12.054 12.835-18.487 18.622v-55.365c0-7.8 6.323-14.123 14.124-14.123 7.8 0 14.123 6.323 14.123 14.123v42.087Zm53.355.99c-2.672-3.555-5.795-6.334-10.535-6.334-11.33 0-12.37 15.887-20.85 20.28v-24.485c0-8.667 7.026-15.693 15.692-15.693 8.667 0 15.693 7.026 15.693 15.693v10.539Zm50.216-2.748c-2.174-2.14-4.752-3.586-8.17-3.586-10.977 0-12.295 14.909-20.076 19.835v-49.696c0-7.8 6.323-14.124 14.123-14.124 7.8 0 14.123 6.324 14.123 14.124v33.447Z"
                            fill="#000" fill-opacity=".299"></path>
                    </g>
                    <path class="" fill-rule="evenodd" clip-rule="evenodd"
                        d="M189.821 151.122c-3.708 5.607-6.525 12.514-14.065 12.514-12.688 0-13.374-19.565-26.069-19.565-12.695 0-12.009 19.565-24.698 19.565-12.688 0-13.374-19.565-26.07-19.565-12.695 0-12.009 19.565-24.697 19.565-12.688 0-13.374-19.565-26.07-19.565-12.695 0-12.009 19.565-24.697 19.565-3.988 0-6.79-1.932-9.152-4.582C5.223 143.253 0 124.737 0 104.941 0 46.984 44.772 0 100 0s100 46.984 100 104.941c0 16.571-3.66 32.246-10.179 46.181Z"
                        fill="#FB70A9"></path>
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M168.182 161.546c26.205-38.985 26.883-82.227 2.031-129.728C188.634 50.69 200 76.587 200 105.162c0 16.509-3.794 32.124-10.55 46.007-3.844 5.586-6.764 12.467-14.579 12.467-2.674 0-4.834-.806-6.689-2.09Z"
                        fill="#E24CA6"></path>
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M94.639 131.119c34.775 0 49.417-20.174 49.417-48.824s-22.125-55.022-49.417-55.022c-27.293 0-49.417 26.372-49.417 55.022s14.642 48.824 49.417 48.824Z"
                        fill="#fff"></path>
                    <path
                        d="M81.352 83.916c10.234 0 18.531-9.158 18.531-20.454 0-11.297-8.296-20.455-18.531-20.455S62.82 52.165 62.82 63.462c0 11.296 8.297 20.454 18.532 20.454Z"
                        fill="#030776"></path>
                    <path
                        d="M78.263 68.182c5.118 0 9.266-4.227 9.266-9.44 0-5.215-4.148-9.441-9.266-9.441-5.117 0-9.266 4.226-9.266 9.44 0 5.214 4.149 9.44 9.266 9.44Z"
                        fill="#fff"></path>
                </svg>
            </a>
        </div>

        <div class="prose-lg max-w-none text-center">
            <h1>
                Halcyon Laravel<br>
                TALL Boilerplate
            </h1>
            <p>
                Great minds come to one to produce a lovely product to help ease development time of projects.<br>
                We love it, and we know you'll do too!
            </p>
        </div>

        <div class="max-w-screen-xl px-4 grid grid-cols-1 lg:grid-cols-3 gap-4">
            <a href="https://filamentphp.com/" target="_blank" rel="noopener noreferrer">
                <div class="h-full rounded overflow-hidden shadow-md bg-white">
                    <div class="px-6 py-4 flex gap-4">
                        <x-heroicon-o-table class="h-10 flex-shrink-0" />
                        <div class="prose">
                            <h3>Filament</h3>
                            <p>Build fully featured admin panels with Filament. Guess what, it's built with TALL stack too!</p>
                        </div>
                    </div>
                </div>
            </a>
            <div class="h-full rounded overflow-hidden shadow-md bg-white">
                <div class="px-6 py-4 flex gap-4 content-start">
                    <x-heroicon-o-template class="h-10 flex-shrink-0" />
                    <div class="prose">
                        <h3>Domain Driven Design</h3>
                        <p>Maintain and Implement business logics with ease. Code once, use anywhere.</p>
                    </div>
                </div>
            </div>
            <a href="https://bitbucket.org/halcyonlaravel/tall-boilerplate/wiki/Home" target="_blank" rel="noopener noreferrer">
                <div class="h-full rounded overflow-hidden shadow-md bg-white">
                    <div class="px-6 py-4 flex gap-4 content-start">
                        <x-heroicon-o-book-open class="h-10 flex-shrink-0" />
                        <div class="prose">
                            <h3>Documentation</h3>
                            <p>
                                We get it, what's the use of a product if you don't know how to use it, right? Here's a
                                handy guide all for you luv!
                            </p>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="ml-4 text-center text-sm text-gray-500 sm:text-right sm:ml-0">
            Laravel v{{ Illuminate\Foundation\Application::VERSION }} | Livewire v{{ Composer\InstalledVersions::getVersion('livewire/livewire') }} | Filament v{{ Composer\InstalledVersions::getVersion('filament/filament') }} | PHP v{{ PHP_VERSION }}
        </div>
    </div>
</body>

</html>
