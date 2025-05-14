<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp Chat Analyzer</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#ffffff',
                        secondary: '#3b82f6',
                        accent: '#1d4ed8'
                    }
                }
            }
        }
    </script>
    <style>
        .blur-bg {
            position: absolute;
            width: 15rem;
            height: 15rem;
            border-radius: 50%;
            background-color: rgba(59, 130, 246, 0.5);
            filter: blur(70px);
            z-index: -1;
        }
    </style>
</head>
<body class="bg-primary text-gray-800 min-h-screen relative overflow-x-hidden">
    <!-- Blur effects -->
    <div class="blur-bg top-20 -left-10"></div>
    <div class="blur-bg bottom-20 right-10"></div>
    <div class="blur-bg top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2"></div>

    <!-- Navbar -->
    <nav class="bg-white/80 backdrop-blur-md shadow-sm sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <i class="fas fa-comment-dots text-secondary text-2xl mr-2"></i>
                    <span class="font-bold text-xl">WhatsApp Chat Analyzer</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="#features" class="text-gray-600 hover:text-secondary">Features</a>
                    <a href="#how-it-works" class="text-gray-600 hover:text-secondary">How It Works</a>
                    <a href="#faq" class="text-gray-600 hover:text-secondary">FAQ</a>
                    <a href="{{ route('view') }}" class="text-gray-600 hover:text-secondary">View Group</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative py-20 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-16">
                <h1 class="text-4xl md:text-5xl font-bold text-gray-800 mb-4">Analyze Your WhatsApp Conversations</h1>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Upload your WhatsApp chat export and get insights, statistics, and visualizations about your conversations.
                </p>
            </div>

            <!-- Upload Section -->
            <div x-data="{ fileName: '' }" class="max-w-2xl mx-auto bg-white rounded-xl shadow-sm overflow-hidden relative z-10">
                <div class="p-8 text-center">
                    <h2 class="text-2xl font-bold text-gray-700 mb-6">Upload WhatsApp Chat (.zip)</h2>

                    <!-- Alert Messages -->
                    @if(session('success'))
                        <div class="bg-green-100 text-green-700 p-3 rounded-md mb-4">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="bg-red-100 text-red-700 p-3 rounded-md mb-4">
                            {{ session('error') }}
                        </div>
                    @endif
                    @if($errors->any())
                        <div class="bg-red-100 text-red-700 p-3 rounded-md mb-4">
                            <ul class="list-disc pl-5">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Upload Form -->
                    <form action="{{ route('chat.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                        @csrf

                        <!-- Drag and Drop Area -->
                        <div 
                            x-on:dragover.prevent 
                            x-on:drop.prevent="$refs.input.files = $event.dataTransfer.files; fileName = $event.dataTransfer.files[0].name" 
                            class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center transition hover:border-blue-500 bg-gray-50 hover:bg-blue-50 cursor-pointer"
                            x-on:click="$refs.input.click()"
                        >
                            <input 
                                type="file" 
                                name="chat_zip" 
                                accept=".zip" 
                                required 
                                class="hidden" 
                                x-ref="input" 
                                x-on:change="fileName = $refs.input.files[0]?.name"
                            >
                            {{-- <svg class="mx-auto mb-3 w-12 h-12 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 16v-4m0 0V8m0 4h4m-4 0H8m8 4a4 4 0 100-8 4 4 0 000 8zm-8 0a4 4 0 100-8 4 4 0 000 8z"/>
                            </svg> --}}
                            <svg  class="mx-auto mb-3 w-12 h-12 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-cloud-upload"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 18a4.6 4.4 0 0 1 0 -9a5 4.5 0 0 1 11 2h1a3.5 3.5 0 0 1 0 7h-1" /><path d="M9 15l3 -3l3 3" /><path d="M12 12l0 9" /></svg>
                            <p class="text-gray-600">
                                <span class="font-medium text-blue-600">Click to upload</span> or drag and drop
                            </p>
                            <p class="text-sm text-gray-400 mt-1" x-text="fileName || 'Only .zip files under 20MB accepted'"></p>
                        </div>

                        <!-- Submit -->
                        <div class="text-center">
                            <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded-md hover:bg-blue-700 transition">
                                Upload File
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-16 px-4 sm:px-6 lg:px-8 bg-gray-50">
        <div class="max-w-7xl mx-auto">
            <h2 class="text-3xl font-bold text-center mb-12">What You'll Discover</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white p-6 rounded-xl shadow-md">
                    <div class="text-secondary text-3xl mb-4">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Message Statistics</h3>
                    <p class="text-gray-600">Get detailed statistics about message frequency, time patterns, and conversation activity.</p>
                </div>
                
                <div class="bg-white p-6 rounded-xl shadow-md">
                    <div class="text-secondary text-3xl mb-4">
                        <i class="fas fa-user-friends"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Participant Analysis</h3>
                    <p class="text-gray-600">See who talks the most, who sends the most media, and individual conversation patterns.</p>
                </div>
                
                <div class="bg-white p-6 rounded-xl shadow-md">
                    <div class="text-secondary text-3xl mb-4">
                        <i class="fas fa-smile"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Emoji & Media Insights</h3>
                    <p class="text-gray-600">Discover the most used emojis, media types, and common phrases in your conversations.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="py-16 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <h2 class="text-3xl font-bold text-center mb-12">How It Works</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-100 text-secondary text-xl mb-4">
                        <i class="fas fa-file-export"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">1. Export Your Chat</h3>
                    <p class="text-gray-600">Export your WhatsApp chat from the app and save the .zip file to your device.</p>
                </div>
                
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-100 text-secondary text-xl mb-4">
                        <i class="fas fa-upload"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">2. Upload the File</h3>
                    <p class="text-gray-600">Select your .zip file and upload it to our secure platform for analysis.</p>
                </div>
                
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-100 text-secondary text-xl mb-4">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">3. Get Insights</h3>
                    <p class="text-gray-600">View detailed analytics and visualizations about your WhatsApp conversations.</p>
                </div>
            </div>
        </div>
    </section>
    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Branding -->
                <div>
                    <h3 class="text-2xl font-bold text-blue-400 mb-4">iCrewSystems</h3>
                    <p class="text-gray-400">
                        Empowering communication analytics with smart and scalable tools. Analyze, optimize, and innovate with iCrewSystems.
                    </p>
                </div>

                <!-- Navigation Links -->
                <div>
                    <h3 class="text-xl font-semibold mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="https://icrewsystems.com/#features" class="text-gray-400 hover:text-white transition">Features</a></li>
                        <li><a href="https://icrewsystems.com/#products" class="text-gray-400 hover:text-white transition">Products</a></li>
                        <li><a href="https://icrewsystems.com/#support" class="text-gray-400 hover:text-white transition">Support</a></li>
                        <li><a href="https://icrewsystems.com/contact" class="text-gray-400 hover:text-white transition">Contact</a></li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div>
                    <h3 class="text-xl font-semibold mb-4">Contact Us</h3>
                    <p class="text-gray-400">Email: <a href="mailto:support@icrewsystems.com" class="underline hover:text-white">support@icrewsystems.com</a></p>
                    <p class="text-gray-400 mt-2">Website: <a href="https://icrewsystems.com" target="_blank" class="underline hover:text-white">www.icrewsystems.com</a></p>

                    <div class="flex space-x-4 mt-4">
                        <a href="#" class="text-gray-400 hover:text-white text-lg"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white text-lg"><i class="fab fa-linkedin"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white text-lg"><i class="fab fa-github"></i></a>
                    </div>
                </div>
            </div>

            <!-- Footer Bottom -->
            <div class="border-t border-gray-700 mt-10 pt-6 text-center text-gray-500 text-sm">
                <p>&copy; {{ now()->year }} iCrewSystems. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Add Alpine.js -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    
</body>
</html>
