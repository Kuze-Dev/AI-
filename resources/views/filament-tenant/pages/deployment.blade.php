<div class="fullscreen-deployment-widget">
    <style>
        .fullscreen-deployment-widget {
            min-height: 100vh;
            width: 100%;
            padding: 0;
            background: white;
            font-family: 'Inter', sans-serif;
        }
        .fullscreen-deployment-widget .fi-sidebar,
        .fullscreen-deployment-widget .fi-topbar,
        .fullscreen-deployment-widget .fi-header,
        .fullscreen-deployment-widget nav {
            display: none !important;
        }
        .fullscreen-deployment-widget .fi-main {
            margin: 0 !important;
            padding: 0 !important;
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
    </style>

    <!-- Top Bar -->
    <div class="topbar">
        <div class="topbar-left">Deployment</div>
        <div class="topbar-center">
            🚀 Manage Deployment
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('filament.tenant.pages.ai-widget') }}"
               class="flex items-center gap-2 text-white text-sm font-medium px-4 py-2 rounded-md shadow border border-gray-200"
               style="background: rgb(var(--primary-600));">
                ← Back to AI Widget
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div style="display:flex; justify-content:center; align-items:center; gap:24px; width:100%; min-height:calc(100vh - 48px);" >
        <div style="border-radius:12px; box-shadow:0 2px 6px rgba(0,0,0,0.08); max-width: 1200px; width: 100%; border:1px solid rgba(0,0,0,0.1);">
            <!-- Header -->
            <div style="padding:16px 24px; border-bottom:1px solid rgba(0,0,0,0.05);">
                <h3 style="font-size:16px; font-weight:600; color:#111;">Deployment Dashboard</h3>
                <p style="font-size:14px; color:#666;">Manage and monitor deployments for your tenant site.</p>
            </div>

            <!-- Content -->
            <div style="padding:24px;">
                <div class="grid gap-6 md:grid-cols-2">
                    <div class="p-4 border rounded-lg bg-white">
                        <h4 class="font-semibold mb-2">Start Deployment</h4>
                        <button class="px-4 py-2 rounded-md text-white"
                                style="background: rgb(var(--primary-600));">
                            Deploy Now
                        </button>
                    </div>

                    <div class="p-4 border rounded-lg bg-white">
                        <h4 class="font-semibold mb-2">Latest Deployment</h4>
                        <p class="text-sm text-gray-600">No deployments yet.</p>
                    </div>
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
                mainContent.style.marginLeft = '0';
                mainContent.style.width = '100%';
            }
            document.body.style.overflow = 'auto';
        });
    </script>
</div>
