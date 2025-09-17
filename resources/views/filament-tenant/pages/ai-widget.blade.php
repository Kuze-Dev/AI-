<div class="fullscreen-ai-widget">
    <style>
        .fullscreen-ai-widget {
            min-height: 100vh;
            width: 100%;
            padding: 0;
            background: white;
            font-family: 'Inter', sans-serif;
        }
        .fi-sidebar, .fi-topbar, .fi-header, nav {
            display: none !important;
        }
        .fi-main {
            margin: 0 !important;
            padding: 0 !important;
        }
        .fi-page {
            max-width: none !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        body {
            margin: 0;
            padding: 0;
        }
        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 24px;
            border-bottom: 1px solid rgba(0,0,0,0.08);
            background: #f9fafb;
        }
        .topbar-left {
            font-size: 16px;
            font-weight: 600;
            color: #111;
        }
        .topbar-center {
            flex: 1;
            text-align: center;
            font-size: 15px;
            font-weight: 500;
            color: #444;
        }
        .wave-animation {
            animation: wave 1s ease-in-out infinite;
            transform-origin: 70% 70%;
        }
        @keyframes wave {
            0% { transform: rotate(0deg); }
            10% { transform: rotate(14deg); }
            20% { transform: rotate(-8deg); }
            30% { transform: rotate(14deg); }
            40% { transform: rotate(-4deg); }
            50% { transform: rotate(10deg); }
            60% { transform: rotate(0deg); }
            100% { transform: rotate(0deg); }
        }
    </style>

    <!-- Top Bar -->
    <div class="topbar">
        <div class="topbar-left">AI Widget</div>
        <div class="topbar-center">
            <svg class="inline w-5 h-5 mr-2 wave-animation" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a7.5 7.5 0 0015 0v-5a1.5 1.5 0 00-3 0m-6-3V11m0-5.5v-1a1.5 1.5 0 013 0v1m0 0V11m0-5.5a1.5 1.5 0 013 0v3"/>
            </svg>
            Welcome Content Creator {{ filament_admin()->full_name }}!
        </div>

        <!-- Right side: Go back + Avatar -->
        <div class="flex items-center gap-3">
            <a href="{{ filament()->getPanel('tenant')->getUrl() }}"
               class="flex items-center gap-2 text-white text-sm font-medium px-4 py-2 rounded-md shadow border border-gray-200"
               style="background: rgb(var(--primary-600));">
                <svg xmlns="http://www.w3.org/2000/svg"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke-width="1.5"
                     stroke="currentColor"
                     class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                Go back to CMS
            </a>

        <!-- Avatar Dropdown -->
<div x-data="{ open: false }" class="relative">
    <button @click="open = ! open" class="flex items-center focus:outline-none">
        <img src="{{ filament_admin()->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode(filament_admin()->full_name) }}"
             alt="Avatar"
             class="w-9 h-9 rounded-full border border-gray-200">
    </button>

    <!-- Dropdown -->
    <div x-show="open"
         @click.away="open = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform scale-95 translate-y-[-10px]"
         x-transition:enter-end="opacity-100 transform scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 transform scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 transform scale-95 translate-y-[-10px]"
         class="absolute mt-2 w-44 bg-white border border-gray-200 rounded-lg shadow-lg py-1 z-50"
         style="right: 0; left: auto; transform: translateX(-20px); box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1), 0 4px 6px rgba(0, 0, 0, 0.05);">
        <form method="POST" action="{{ filament()->getLogoutUrl() }}">
            @csrf
            <button type="submit"
                    class="w-full flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                <svg xmlns="http://www.w3.org/2000/svg"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke-width="1.5"
                     stroke="currentColor"
                     class="w-5 h-5 text-gray-500">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25a2.25 2.25 0 00-2.25-2.25h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12"/>
                </svg>
                Logout
            </button>
        </form>
    </div>
</div>
        </div>
    </div>

    <!-- Main Content -->
    <div style="display:flex; justify-content:center;align-items:center; gap:24px; width:100%; min-height:calc(100vh - 48px);" >
        <div  style="border-radius:12px; box-shadow:0 2px 6px rgba(0,0,0,0.08); max-width: 1400px; width: 100%; border:1px solid rgba(0,0,0,0.1);">
            <!-- Header -->
            <div style="display:flex;flex-direction:column;gap:12px;padding:16px 24px;border-bottom:1px solid rgba(0,0,0,0.05);">
                <div style="display:flex;align-items:center;gap:12px;">
                    <div style="flex:1;display:grid;gap:4px;">
                        <h3 style="font-size:16px;font-weight:600;color:#111;">
                            AI File Upload Widget
                        </h3>
                        <p style="font-size:14px;color:#666;">
                            Upload your files for AI processing
                        </p>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div style="padding:24px;">
                {{ $this->form }}
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const elementsToHide = document.querySelectorAll('.fi-sidebar, .fi-topbar, .fi-header, nav, .fi-layout-sidebar');
            elementsToHide.forEach(el => {
                if (el) el.style.display = 'none';
            });
            const mainContent = document.querySelector('.fi-main');
            if (mainContent) {
                mainContent.style.marginLeft = '0';
                mainContent.style.width = '100%';
            }
            document.body.style.overflow = 'auto';
            document.body.style.margin = '0';
            document.body.style.padding = '0';
        });
    </script>
</div>
