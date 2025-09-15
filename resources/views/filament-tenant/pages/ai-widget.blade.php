<div class="fullscreen-ai-widget">
    <style>
        .fullscreen-ai-widget {
            min-height: 100vh;
            width: 100%;
            padding: 24px;
            background: #f8fafc;
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
    </style>

    <div style="display:flex; justify-content:center;align-items:center;  gap:24px;width:100%;min-height:calc(100vh - 48px);">
        <div style="border-radius:12px; box-shadow:0 2px 6px rgba(0,0,0,0.08); width: 80%; border:1px solid rgba(0,0,0,0.1);">
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
            // Hide any remaining navigation elements
            const elementsToHide = document.querySelectorAll('.fi-sidebar, .fi-topbar, .fi-header, nav, .fi-layout-sidebar');
            elementsToHide.forEach(el => {
                if (el) el.style.display = 'none';
            });

            // Ensure main content takes full width
            const mainContent = document.querySelector('.fi-main');
            if (mainContent) {
                mainContent.style.marginLeft = '0';
                mainContent.style.width = '100%';
            }

            // Set body to full screen
            document.body.style.overflow = 'auto';
            document.body.style.margin = '0';
            document.body.style.padding = '0';
        });
    </script>
</div>