<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Test Dashboard</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Alpine.js for interactivity -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Axios for HTTP requests -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <style>
        .highlight {
            background: linear-gradient(45deg, #f59e0b, #d97706);
            color: white;
        }

        .response-container {
            max-height: 400px;
            overflow-y: auto;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
        }

        .loading {
            animation: pulse 1.5s ease-in-out infinite;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen" x-data="apiTester()">

    <!-- Header -->
    <header class="bg-white shadow-lg border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">🧪 API Test Dashboard</h1>
                    <p class="text-gray-600 mt-1">Laravel Backend API Testing Interface</p>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-500">Laravel {{app()->version()}}</span>
                    <div class="w-3 h-3 bg-green-500 rounded-full" title="Server Online"></div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Authentication Section -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">🔐 Authentication</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Login Form -->
                <div class="space-y-4">
                    <h3 class="font-medium text-gray-700">Login</h3>
                    <input type="email" x-model="loginData.email" placeholder="Email"
                           class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <input type="password" x-model="loginData.password" placeholder="Password"
                           class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <button @click="login()" :disabled="loading"
                            class="w-full bg-blue-600 text-white py-3 px-4 rounded-md hover:bg-blue-700 disabled:opacity-50"
                            :class="{'loading': loading}">
                        <span x-show="!loading">Login</span>
                        <span x-show="loading">Logging in...</span>
                    </button>
                </div>

                <!-- Token Display -->
                <div class="space-y-4">
                    <h3 class="font-medium text-gray-700">Access Token</h3>
                    <div class="p-3 bg-gray-100 rounded-md min-h-[2.5rem] flex items-center">
                        <span x-text="authToken || 'No token yet'" class="text-sm text-gray-600 break-all"></span>
                    </div>
                    <button @click="logout()" x-show="authToken"
                            class="w-full bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700">
                        Logout
                    </button>
                </div>
            </div>
        </div>

        <!-- API Endpoints Testing -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

            <!-- Products API -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">📦 Products API</h2>

                <div class="space-y-4">
                    <!-- Get All Products -->
                    <button @click="testEndpoint('/api/products', 'GET')"
                            class="w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700">
                        GET /api/products
                    </button>

                    <!-- Get Product by ID -->
                    <div class="flex space-x-2">
                        <input type="number" x-model="productId" placeholder="Product ID"
                               class="flex-1 p-2 border border-gray-300 rounded-md">
                        <button @click="testEndpoint('/api/products/' + productId, 'GET')"
                                class="bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">
                            GET by ID
                        </button>
                    </div>

                    <!-- Create Product -->
                    <div class="space-y-2">
                        <h4 class="font-medium text-gray-700">Create Product:</h4>
                        <input type="text" x-model="newProduct.name" placeholder="Product Name"
                               class="w-full p-2 border border-gray-300 rounded-md">
                        <input type="number" x-model="newProduct.price" placeholder="Price"
                               class="w-full p-2 border border-gray-300 rounded-md">
                        <input type="text" x-model="newProduct.sku" placeholder="SKU"
                               class="w-full p-2 border border-gray-300 rounded-md">
                        <button @click="createProduct()"
                                class="w-full bg-purple-600 text-white py-2 px-4 rounded-md hover:bg-purple-700">
                            POST Create Product
                        </button>
                    </div>
                </div>
            </div>

            <!-- Categories API -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">📂 Categories API</h2>

                <div class="space-y-4">
                    <!-- Get All Categories -->
                    <button @click="testEndpoint('/api/categories', 'GET')"
                            class="w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700">
                        GET /api/categories
                    </button>

                    <!-- Get Category by ID -->
                    <div class="flex space-x-2">
                        <input type="number" x-model="categoryId" placeholder="Category ID"
                               class="flex-1 p-2 border border-gray-300 rounded-md">
                        <button @click="testEndpoint('/api/categories/' + categoryId, 'GET')"
                                class="bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">
                            GET by ID
                        </button>
                    </div>
                </div>
            </div>

            <!-- Posts API -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">📝 Posts API</h2>

                <div class="space-y-4">
                    <!-- Get All Posts -->
                    <button @click="testEndpoint('/api/posts', 'GET')"
                            class="w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700">
                        GET /api/posts
                    </button>

                    <!-- Get Published Posts -->
                    <button @click="testEndpoint('/api/posts?published=1', 'GET')"
                            class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700">
                        GET Published Posts
                    </button>
                </div>
            </div>

            <!-- Error Testing -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">⚠️ Error Testing</h2>

                <div class="space-y-4">
                    <!-- Test 404 Error -->
                    <button @click="testEndpoint('/api/products/999999', 'GET')"
                            class="w-full bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700">
                        Test 404 Error
                    </button>

                    <!-- Test Validation Error -->
                    <button @click="testEndpoint('/api/products', 'POST', {})"
                            class="w-full bg-orange-600 text-white py-2 px-4 rounded-md hover:bg-orange-700">
                        Test Validation Error
                    </button>
                </div>
            </div>
        </div>

        <!-- Response Display -->
        <div class="bg-white rounded-lg shadow-lg p-6 mt-8">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-gray-900">📊 API Response</h2>
                <div class="flex items-center space-x-4">
                    <span x-show="lastRequest.method" class="text-sm text-gray-600">
                        <span x-text="lastRequest.method" class="font-semibold"></span>
                        <span x-text="lastRequest.url"></span>
                    </span>
                    <span x-show="lastRequest.status"
                          :class="{
                              'text-green-600': lastRequest.status >= 200 && lastRequest.status < 300,
                              'text-red-600': lastRequest.status >= 400,
                              'text-blue-600': lastRequest.status >= 300 && lastRequest.status < 400
                          }"
                          class="font-semibold">
                        Status: <span x-text="lastRequest.status"></span>
                    </span>
                </div>
            </div>

            <div class="response-container bg-gray-900 text-green-400 p-4 rounded-md font-mono text-sm">
                <pre x-html="formatResponse(lastResponse)"></pre>
            </div>
        </div>

    </div>

    <script>
        function apiTester() {
            return {
                loading: false,
                authToken: localStorage.getItem('api_token') || '',
                lastResponse: 'Ready to test API endpoints...',
                lastRequest: {},

                // Form data
                loginData: {
                    email: 'test@example.com',
                    password: 'password123'
                },
                productId: 1,
                categoryId: 1,
                newProduct: {
                    name: 'Test Product',
                    price: 99.99,
                    sku: 'TEST-001',
                    description: 'A test product',
                    quantity: 10,
                    category_id: 1,
                    is_active: true
                },

                // Initialize
                init() {
                    this.setupAxios();
                },

                // Setup Axios
                setupAxios() {
                    // Set CSRF token
                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    axios.defaults.headers.common['X-CSRF-TOKEN'] = token;

                    // Set auth header if token exists
                    if (this.authToken) {
                        axios.defaults.headers.common['Authorization'] = `Bearer ${this.authToken}`;
                    }
                },

                // Login
                async login() {
                    this.loading = true;
                    try {
                        const response = await axios.post('/api/auth/login', this.loginData);
                        this.authToken = response.data.access_token;
                        localStorage.setItem('api_token', this.authToken);
                        axios.defaults.headers.common['Authorization'] = `Bearer ${this.authToken}`;

                        this.lastRequest = {
                            method: 'POST',
                            url: '/api/auth/login',
                            status: response.status
                        };
                        this.lastResponse = response.data;

                        alert('Login successful!');
                    } catch (error) {
                        this.handleError(error);
                    }
                    this.loading = false;
                },

                // Logout
                logout() {
                    this.authToken = '';
                    localStorage.removeItem('api_token');
                    delete axios.defaults.headers.common['Authorization'];
                    this.lastResponse = 'Logged out successfully';
                },

                // Test any endpoint
                async testEndpoint(url, method = 'GET', data = null) {
                    this.loading = true;
                    try {
                        const config = {
                            method: method.toLowerCase(),
                            url: url
                        };

                        if (data && ['post', 'put', 'patch'].includes(method.toLowerCase())) {
                            config.data = data;
                        }

                        const response = await axios(config);

                        this.lastRequest = {
                            method: method,
                            url: url,
                            status: response.status
                        };
                        this.lastResponse = response.data;

                    } catch (error) {
                        this.handleError(error);
                    }
                    this.loading = false;
                },

                // Create product
                async createProduct() {
                    await this.testEndpoint('/api/products', 'POST', this.newProduct);
                },

                // Handle errors
                handleError(error) {
                    this.lastRequest = {
                        method: error.config?.method?.toUpperCase() || 'UNKNOWN',
                        url: error.config?.url || 'UNKNOWN',
                        status: error.response?.status || 'ERROR'
                    };
                    this.lastResponse = error.response?.data || { error: error.message };
                },

                // Format response for display
                formatResponse(response) {
                    if (typeof response === 'string') {
                        return response;
                    }
                    return JSON.stringify(response, null, 2);
                }
            }
        }
    </script>
</body>
</html>
