<div class="fullscreen-deployment-widget">
    <style>

        .fullscreen-deployment-widget {
            min-height: 100vh;
            width: 100%;
            padding: 0;
            background: #f9fafb;
            font-family: 'Inter', sans-serif;
        }
        .fullscreen-deployment-widget .fi-sidebar,
        .fullscreen-deployment-widget .fi-topbar,
        .fullscreen-deployment-widget .fi-header,
        .fullscreen-deployment-widget nav {
            display: none !important;
        }
        .fullscreen-deployment-widget .fi-main {
        padding-left: 0 !important;
        padding-right: 0 !important;
        max-width: 100% !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
    }
        .fullscreen-deployment-widget .fi-page {
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
            background: #fff;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
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
        .status-pill {
            display: inline-block;
            padding: 2px 8px;
            font-size: 12px;
            font-weight: 500;
            border-radius: 999px;
        }
        .status-ok { background: #dcfce7; color: #15803d; }
        .status-pending { background: #fef9c3; color: #854d0e; }
        .status-error { background: #fee2e2; color: #b91c1c; }
    </style>

    <!-- Top Bar -->
    <div class="topbar">
        <div class="topbar-left">Deployment</div>
        <div class="topbar-center">🚀 Manage Deployment</div>
        <div class="flex items-center gap-3">
            <a href="{{ route('filament.tenant.pages.ai-widget') }}"
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
                Go back to AI Widget
            </a>

            <!-- Avatar Dropdown -->
            <div x-data="{ open: false }" class="relative">
                <button @click="open = ! open" class="flex items-center focus:outline-none">
                    <img src="{{ filament_admin()->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode(filament_admin()->full_name) }}"
                         alt="Avatar"
                         class="w-9 h-9 rounded-full border border-gray-200">
                </button>
                <div x-show="open"
                     @click.away="open = false"
                     x-transition
                     class="absolute mt-2 w-44 bg-white border border-gray-200 rounded-lg shadow-lg py-1 z-50"
                     style="right: 0; transform: translateX(-20px);">
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
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M15.75 9V5.25a2.25 2.25 0 00-2.25-2.25h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12"/>
                            </svg>
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div style="display:flex; justify-content:center; align-items:flex-start; gap:24px; width:100%; min-height:calc(100vh - 48px); padding:24px;">
        <div style="border-radius:12px; box-shadow:0 2px 6px rgba(0,0,0,0.08); max-width: 1200px; width: 100%; border:1px solid rgba(0,0,0,0.1); background:#fff;">

            <!-- Header -->
            <div style="padding:16px 24px; border-bottom:1px solid rgba(0,0,0,0.05);">
                <h3 style="font-size:16px; font-weight:600; color:#111;">Deployment Dashboard</h3>
                <p style="font-size:14px; color:#666;">Manage and monitor deployments for your  site.</p>
            </div>

            <!-- Content -->
            <div style="padding:24px; " >

                <!-- Overview Cards -->
<div class="flex justify-center" style="margin-bottom: 24px; gap:24px;">
    <div class="p-4 border rounded-lg bg-white shadow-sm" style="display:flex; flex-direction:column; align-items:center; gap:6px; min-width:120px;">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:28px; height:28px; color:#2563eb;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8h18M3 16h18M4 12h16" />
        </svg>
        <h4 class="text-sm font-medium text-gray-600">Queued</h4>
        <p class="text-2xl font-bold" style="color:#2563eb;">0</p>
    </div>
    <div class="p-4 border rounded-lg bg-white shadow-sm" style="display:flex; flex-direction:column; align-items:center; gap:6px; min-width:120px;">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:28px; height:28px; color:#ca8a04;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <h4 class="text-sm font-medium text-gray-600">Running</h4>
        <p class="text-2xl font-bold" style="color:#ca8a04;">0</p>
    </div>
    <div class="p-4 border rounded-lg bg-white shadow-sm" style="display:flex; flex-direction:column; align-items:center; gap:6px; min-width:120px;">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:28px; height:28px; color:#dc2626;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <h4 class="text-sm font-medium text-gray-600">Failed</h4>
        <p class="text-2xl font-bold" style="color:#dc2626;">0</p>
    </div>
    <div class="p-4 border rounded-lg bg-white shadow-sm" style="display:flex; flex-direction:column; align-items:center; gap:6px; min-width:120px;">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:28px; height:28px; color:#16a34a;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        <h4 class="text-sm font-medium text-gray-600">Successful</h4>
        <p class="text-2xl font-bold" style="color:#16a34a;">0</p>
    </div>
</div>

                <!-- Pre-Deployment Checks -->
                <div class="p-4 border rounded-lg bg-white shadow-sm">
                    <h4 class="font-semibold mb-2">Pre-Deployment Checks</h4>
                    <ul class="space-y-2 text-sm">
                        <li class="flex justify-between">
                            <span>✔️ Codebase clean</span>
                            <span class="status-pill status-ok">OK</span>
                        </li>
                        <li class="flex justify-between">
                            <span>🔍 Lint & Tests</span>
                            <span class="status-pill status-pending">Pending</span>
                        </li>
                        <li class="flex justify-between">
                            <span>📦 Dependencies up to date</span>
                            <span class="status-pill status-ok">OK</span>
                        </li>
                        <li class="flex justify-between">
                            <span>⚠️ Security scan</span>
                            <span class="status-pill status-error">Failed</span>
                        </li>
                    </ul>
                </div>

                <!-- Start Deployment -->
                <div class="p-4 border rounded-lg bg-white shadow-sm" style="margin-top: 20px;">
                    <h4 class="font-semibold mb-2">Start Deployment</h4>
                    <button class="px-4 py-2 rounded-md text-white font-medium shadow-sm"
                            style="background: rgb(var(--primary-600));">
                        🚀 Deploy Now
                    </button>
                </div>

                <!-- Latest Deployment -->
                <div class="p-4 border rounded-lg bg-white shadow-sm" style="margin-top: 20px;">
                    <h4 class="font-semibold mb-2">Latest Deployment</h4>
                    <p class="text-sm text-gray-600">No deployments yet.</p>
                </div>

                <!-- Deployment History -->
                <div class="p-4 border rounded-lg bg-white shadow-sm" style="margin-top: 20px;">
                    <h4 class="font-semibold mb-4">Deployment History</h4>
                    <table class="w-full text-sm text-left border-collapse">
                        <thead>
                            <tr class="border-b text-gray-600">
                                <th class="py-2 px-3">ID</th>
                                <th class="py-2 px-3">Commit</th>
                                <th class="py-2 px-3">Status</th>
                                <th class="py-2 px-3">Started At</th>
                                <th class="py-2 px-3">Duration</th>
                                <th class="py-2 px-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-2 px-3">#123</td>
                                <td class="py-2 px-3 font-mono text-xs">a1b2c3d</td>
                                <td class="py-2 px-3">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-600">Successful</span>
                                </td>
                                <td class="py-2 px-3">2025-09-18 14:00</td>
                                <td class="py-2 px-3">2m 30s</td>
                                <td class="py-2 px-3 text-right flex gap-2 justify-end">
                                    <button class="text-blue-600 hover:underline">View Logs</button>
                                    <button class="text-gray-600 hover:underline">Rollback</button>
                                </td>
                            </tr>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-2 px-3">#122</td>
                                <td class="py-2 px-3 font-mono text-xs">f9e8d7c</td>
                                <td class="py-2 px-3">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-600">Failed</span>
                                </td>
                                <td class="py-2 px-3">2025-09-17 19:45</td>
                                <td class="py-2 px-3">45s</td>
                                <td class="py-2 px-3 text-right flex gap-2 justify-end">
                                    <button class="text-blue-600 hover:underline">View Logs</button>
                                    <button class="text-gray-600 hover:underline">Retry</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const elementsToHide = document.querySelectorAll('.fi-sidebar, .fi-topbar, .fi-header, nav, .fi-layout-sidebar');
            elementsToHide.forEach(el => { if (el) el.style.display = 'none'; });
            const mainContent = document.querySelector('.fi-main');
            if (mainContent) {
                 // Remove padding classes
        mainContent.classList.remove('px-4', 'md:px-6', 'lg:px-8', 'mx-auto', 'max-w-full');

        // Force overrides (in case Tailwind applies other defaults)
        mainContent.style.paddingLeft = '0';
        mainContent.style.paddingRight = '0';
        mainContent.style.marginLeft = '0';
        mainContent.style.marginRight = '0';
        mainContent.style.maxWidth = '100%';
            }
            document.body.style.overflow = 'auto';
        });
    </script>
</div>
