<x-filament-panels::page>
    <div style="display:flex;flex-direction:column;gap:24px;width:100%;">
        <div style="border-radius:12px;background:#fff;box-shadow:0 2px 6px rgba(0,0,0,0.08);border:1px solid rgba(0,0,0,0.1);">

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
</x-filament-panels::page>
