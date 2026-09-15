<?php
session_start();

// Settings file path (acts as our database for this standalone kit)
$settingsFile = __DIR__ . '/fwallet_settings.json';

// Default settings
$settings = [
    'api_key' => '',
    'success_url' => 'https://yourwebsite.com/success',
    'cancel_url' => 'https://yourwebsite.com/cancel'
];

// Load existing settings if available
if (file_exists($settingsFile)) {
    $loadedSettings = json_decode(file_get_contents($settingsFile), true);
    if ($loadedSettings) {
        $settings = array_merge($settings, $loadedSettings);
    }
}

// Handle AJAX & Form POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Action: Save Settings
    if ($action === 'save_settings') {
        $settings['api_key'] = trim($_POST['api_key'] ?? '');
        $settings['success_url'] = trim($_POST['success_url'] ?? '');
        $settings['cancel_url'] = trim($_POST['cancel_url'] ?? '');
        
        file_put_contents($settingsFile, json_encode($settings, JSON_PRETTY_PRINT));
        echo json_encode(['status' => 'success', 'message' => 'Settings have been saved successfully!']);
        exit;
    }

    // Action: Export Settings
    if ($action === 'export') {
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="fwallet_backup.json"');
        echo json_encode($settings, JSON_PRETTY_PRINT);
        exit;
    }

    // Action: Import Settings
    if ($action === 'import') {
        if (isset($_FILES['import_file']) && $_FILES['import_file']['error'] === UPLOAD_ERR_OK) {
            $content = file_get_contents($_FILES['import_file']['tmp_name']);
            $imported = json_decode($content, true);
            
            if ($imported && isset($imported['api_key'])) {
                file_put_contents($settingsFile, json_encode($imported, JSON_PRETTY_PRINT));
                echo json_encode(['status' => 'success', 'message' => 'Settings restored successfully! Please refresh.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Invalid backup file format!']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to upload the file!']);
        }
        exit;
    }

    // Action: Process Payment
    if ($action === 'pay') {
        $amount = $_POST['amount'] ?? 0;
        $product_name = $_POST['product_name'] ?? 'Unknown Product';

        if (empty($settings['api_key'])) {
            echo json_encode(['status' => 'error', 'message' => 'API Key is missing! Please configure settings first.']);
            exit;
        }

        if ($amount < 10) {
             echo json_encode(['status' => 'error', 'message' => 'Minimum amount is 10 BDT.']);
             exit;
        }

        // Prepared Data for Fwallet API
        $paymentData = [
            "api_key" => $settings['api_key'],
            "amount" => (float)$amount,
            "product_name" => $product_name,
            "success_url" => $settings['success_url'],
            "cancel_url" => $settings['cancel_url']
        ];

        /*
        // -------------------------------------------------------------
        // ACTUAL cURL CODE FOR LIVE SERVER (Uncomment to use real API)
        // -------------------------------------------------------------
        $apiUrl = "https://yourdomain.com/api/create_payment.php";
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($paymentData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        $response = curl_exec($ch);
        if(curl_errno($ch)){
            echo json_encode(['status' => 'error', 'message' => 'cURL Error: ' . curl_error($ch)]);
            exit;
        }
        curl_close($ch);
        $result = json_decode($response, true);
        */

        // -------------------------------------------------------------
        // SIMULATED RESPONSE FOR DEMO PURPOSES
        // -------------------------------------------------------------
        $result = [
            'status' => 'success',
            'message' => 'Payment session created successfully.',
            'checkout_url' => 'https://example.com/checkout?token=' . bin2hex(random_bytes(8))
        ];

        // Process Response
        if (isset($result['status']) && $result['status'] === 'success') {
            // Frontend will handle the redirect to checkout_url
            echo json_encode(['status' => 'success', 'checkout_url' => $result['checkout_url'], 'message' => 'Redirecting to Fwallet...']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'API Error: ' . ($result['message'] ?? 'Unknown error occurred.')]);
        }
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fwallet Open Source Kit</title>
    <!-- Using Tailwind CSS for modern, app-like styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Custom App-like Scrollbar & Animations */
        body { font-family: 'Segoe UI', system-ui, sans-serif; background-color: #f3f4f6; }
        .glass-card { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); }
        .fade-in { animation: fadeIn 0.3s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        
        /* Vibrant gradients as requested (Red, Blue, Green, Pink, Black, White mix) */
        .bg-gradient-pink-red { background: linear-gradient(135deg, #ec4899, #ef4444); }
        .bg-gradient-blue-cyan { background: linear-gradient(135deg, #3b82f6, #06b6d4); }
        .bg-gradient-green-teal { background: linear-gradient(135deg, #10b981, #14b8a6); }
        .bg-dark-metal { background: linear-gradient(135deg, #1f2937, #000000); }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <!-- Main App Container -->
    <div class="w-full max-w-md glass-card rounded-3xl shadow-2xl overflow-hidden border border-gray-100 fade-in relative">
        
        <!-- Header -->
        <div class="bg-dark-metal text-white p-6 text-center rounded-t-3xl relative">
            <h1 class="text-2xl font-bold tracking-wider"><i class="fa-solid fa-wallet text-pink-400 mr-2"></i>FWALLET</h1>
            <p class="text-xs text-gray-300 mt-1 opacity-80">Open Source Payment Engine</p>
            <!-- Decorative colored dots -->
            <div class="absolute top-4 right-4 flex space-x-1">
                <div class="w-3 h-3 rounded-full bg-red-500"></div>
                <div class="w-3 h-3 rounded-full bg-green-400"></div>
                <div class="w-3 h-3 rounded-full bg-blue-400"></div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="flex border-b border-gray-200 bg-white">
            <button onclick="switchTab('checkout')" id="tab-checkout" class="flex-1 py-3 text-sm font-semibold text-green-600 border-b-2 border-green-500 transition-colors">
                <i class="fa-solid fa-cart-shopping mb-1 block"></i> Checkout
            </button>
            <button onclick="switchTab('settings')" id="tab-settings" class="flex-1 py-3 text-sm font-semibold text-gray-400 border-b-2 border-transparent transition-colors hover:text-blue-500">
                <i class="fa-solid fa-gear mb-1 block"></i> Settings
            </button>
            <button onclick="switchTab('backup')" id="tab-backup" class="flex-1 py-3 text-sm font-semibold text-gray-400 border-b-2 border-transparent transition-colors hover:text-pink-500">
                <i class="fa-solid fa-cloud-arrow-up mb-1 block"></i> Backup
            </button>
        </div>

        <!-- Content Area -->
        <div class="p-6 bg-white min-h-[350px]">
            
            <!-- Checkout Tab -->
            <div id="content-checkout" class="block fade-in">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Secure Payment</h2>
                <form id="payForm" onsubmit="handlePayment(event)">
                    <input type="hidden" name="action" value="pay">
                    
                    <div class="mb-4">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Product Name</label>
                        <div class="relative">
                            <i class="fa-solid fa-box absolute left-3 top-3.5 text-gray-400"></i>
                            <input type="text" name="product_name" required value="Premium Package" class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-400 focus:outline-none transition-all">
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Amount (BDT)</label>
                        <div class="relative">
                            <i class="fa-solid fa-bangladeshi-taka-sign absolute left-3 top-3.5 text-gray-400"></i>
                            <input type="number" name="amount" min="10" step="0.01" required placeholder="500.00" class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-400 focus:outline-none transition-all text-lg font-bold text-gray-800">
                        </div>
                    </div>
                    
                    <button type="submit" id="payBtn" class="w-full bg-gradient-green-teal text-white font-bold py-4 rounded-xl shadow-lg hover:shadow-green-500/30 transform hover:-translate-y-0.5 transition-all">
                        <i class="fa-solid fa-lock mr-2"></i> Pay Now
                    </button>
                </form>
            </div>

            <!-- Settings Tab -->
            <div id="content-settings" class="hidden fade-in">
                <h2 class="text-lg font-bold text-gray-800 mb-4">API Configuration</h2>
                <form id="settingsForm" onsubmit="handleSettings(event)">
                    <input type="hidden" name="action" value="save_settings">
                    
                    <div class="mb-4">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Secret API Key</label>
                        <input type="password" name="api_key" value="<?php echo htmlspecialchars($settings['api_key']); ?>" required placeholder="FW_XXXXXXXXXXXXXXXX" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none text-sm">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Success URL</label>
                        <input type="url" name="success_url" value="<?php echo htmlspecialchars($settings['success_url']); ?>" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none text-sm">
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Cancel URL</label>
                        <input type="url" name="cancel_url" value="<?php echo htmlspecialchars($settings['cancel_url']); ?>" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none text-sm">
                    </div>
                    
                    <button type="submit" class="w-full bg-gradient-blue-cyan text-white font-bold py-3 rounded-xl shadow-lg hover:shadow-blue-500/30 transition-all">
                        <i class="fa-solid fa-floppy-disk mr-2"></i> Save Settings
                    </button>
                </form>
            </div>

            <!-- Backup & Restore Tab -->
            <div id="content-backup" class="hidden fade-in">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Data Management</h2>
                <p class="text-xs text-gray-500 mb-6">Backup your API keys and settings to a JSON file, or restore them easily.</p>
                
                <!-- Export -->
                <form method="POST" action="" class="mb-6">
                    <input type="hidden" name="action" value="export">
                    <button type="submit" class="w-full bg-gradient-pink-red text-white font-bold py-3 rounded-xl shadow-lg hover:shadow-pink-500/30 transition-all flex justify-center items-center">
                        <i class="fa-solid fa-download mr-2"></i> Export Data (JSON)
                    </button>
                </form>

                <div class="relative flex py-2 items-center mb-4">
                    <div class="flex-grow border-t border-gray-200"></div>
                    <span class="flex-shrink-0 mx-4 text-gray-400 text-xs uppercase font-bold">OR</span>
                    <div class="flex-grow border-t border-gray-200"></div>
                </div>

                <!-- Import -->
                <form id="importForm" onsubmit="handleImport(event)">
                    <input type="hidden" name="action" value="import">
                    <div class="mb-4">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Restore from Backup</label>
                        <input type="file" name="import_file" accept=".json" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 transition-all">
                    </div>
                    <button type="submit" class="w-full bg-dark-metal text-white font-bold py-3 rounded-xl shadow-lg hover:shadow-gray-500/30 transition-all">
                        <i class="fa-solid fa-upload mr-2"></i> Import Settings
                    </button>
                </form>
            </div>

        </div>
    </div>

    <!-- CUSTOM MODAL SYSTEM (Replaces alert(), prompt() etc) -->
    <div id="customModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden items-center justify-center z-50 fade-in">
        <div class="bg-white p-6 rounded-2xl shadow-2xl max-w-sm w-full mx-4 text-center transform scale-95 transition-transform duration-300" id="modalContentBox">
            <div id="modalIcon" class="text-5xl mb-4"></div>
            <h3 id="modalTitle" class="text-xl font-bold text-gray-800 mb-2">Title</h3>
            <p id="modalMessage" class="text-sm text-gray-600 mb-6">Message goes here.</p>
            <button onclick="closeModal()" class="bg-gray-800 text-white font-bold py-2 px-8 rounded-full hover:bg-black transition-colors w-full">
                Okay
            </button>
        </div>
    </div>

    <script>
        // Tab Switcher Logic
        function switchTab(tabName) {
            // Hide all contents
            ['checkout', 'settings', 'backup'].forEach(name => {
                document.getElementById('content-' + name).classList.add('hidden');
                let tab = document.getElementById('tab-' + name);
                tab.classList.remove('text-green-600', 'text-blue-600', 'text-pink-600', 'border-green-500', 'border-blue-500', 'border-pink-500');
                tab.classList.add('text-gray-400', 'border-transparent');
            });

            // Show selected content
            document.getElementById('content-' + tabName).classList.remove('hidden');
            let activeTab = document.getElementById('tab-' + tabName);
            
            // Apply specific colors based on tab
            if(tabName === 'checkout') { activeTab.classList.add('text-green-600', 'border-green-500'); }
            if(tabName === 'settings') { activeTab.classList.add('text-blue-600', 'border-blue-500'); }
            if(tabName === 'backup') { activeTab.classList.add('text-pink-600', 'border-pink-500'); }
        }

        // Custom Modal Logic (NO ALERT USED)
        function showModal(type, title, message) {
            const modal = document.getElementById('customModal');
            const icon = document.getElementById('modalIcon');
            const titleEl = document.getElementById('modalTitle');
            const msgEl = document.getElementById('modalMessage');

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            
            titleEl.innerText = title;
            msgEl.innerText = message;

            if (type === 'success') {
                icon.innerHTML = '<i class="fa-solid fa-circle-check text-green-500"></i>';
                titleEl.className = "text-xl font-bold text-green-600 mb-2";
            } else if (type === 'error') {
                icon.innerHTML = '<i class="fa-solid fa-circle-xmark text-red-500"></i>';
                titleEl.className = "text-xl font-bold text-red-600 mb-2";
            } else {
                icon.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-blue-500"></i>';
                titleEl.className = "text-xl font-bold text-blue-600 mb-2";
            }
        }

        function closeModal() {
            const modal = document.getElementById('customModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        // Handle AJAX form submissions generic function
        async function submitForm(url, formData, successCallback) {
            showModal('loading', 'Processing...', 'Please wait while we process your request.');
            try {
                const response = await fetch(url, { method: 'POST', body: formData });
                const result = await response.json();
                
                if (result.status === 'success') {
                    showModal('success', 'Success!', result.message || 'Action completed successfully.');
                    if (successCallback) successCallback(result);
                } else {
                    showModal('error', 'Error!', result.message || 'Something went wrong.');
                }
            } catch (error) {
                showModal('error', 'Network Error', 'Failed to connect to the server.');
            }
        }

        // Handle Settings Save
        function handleSettings(e) {
            e.preventDefault();
            const form = e.target;
            submitForm('', new FormData(form));
        }

        // Handle Import Upload
        function handleImport(e) {
            e.preventDefault();
            const form = e.target;
            submitForm('', new FormData(form), () => {
                setTimeout(() => window.location.reload(), 1500);
            });
        }

        // Handle Payment Checkout
        function handlePayment(e) {
            e.preventDefault();
            const form = e.target;
            const btn = document.getElementById('payBtn');
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Processing...';
            btn.disabled = true;

            submitForm('', new FormData(form), (result) => {
                // Redirect on success
                setTimeout(() => {
                    window.location.href = result.checkout_url;
                }, 1000);
            }).finally(() => {
                btn.innerHTML = '<i class="fa-solid fa-lock mr-2"></i> Pay Now';
                btn.disabled = false;
            });
        }
    </script>
</body>
</html>