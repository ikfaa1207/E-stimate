<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Secure Access Required - {{ $project->name }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
        @endif
    </head>
    <body class="font-sans antialiased text-gray-900 bg-gradient-to-tr from-slate-900 via-indigo-950 to-slate-900 flex items-center justify-center min-h-screen">
        <div class="max-w-md w-full mx-4">
            
            <!-- Branding Header -->
            <div class="text-center mb-8">
                <span class="text-xs bg-indigo-500/10 text-indigo-300 border border-indigo-400/20 px-3 py-1 rounded-full font-semibold uppercase tracking-wider">
                    E-stimate Secure Portal
                </span>
            </div>

            <!-- Glassmorphic Access Card -->
            <div class="bg-white/95 border border-slate-200/50 shadow-2xl rounded-2xl overflow-hidden backdrop-blur-md">
                <div class="p-8">
                    <div class="flex flex-col items-center text-center">
                        <!-- Shield Icon -->
                        <div class="h-14 w-14 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600 mb-4 shadow-inner border border-indigo-100/50">
                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        
                        <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Enter Access PIN</h2>
                        <p class="text-sm text-gray-500 mt-2 max-w-sm">
                            This project proposal is password-protected. Please enter the 6-digit access PIN provided by your contractor.
                        </p>
                    </div>

                    <!-- Passcode Form -->
                    <form method="POST" action="{{ route('projects.share.verify', $project->share_token) }}" class="mt-8 space-y-6">
                        @csrf

                        <!-- PIN Input -->
                        <div>
                            <label for="passcode" class="sr-only">Access Passcode</label>
                            <input 
                                type="text" 
                                name="passcode" 
                                id="passcode" 
                                maxlength="6" 
                                pattern="\d{6}" 
                                required 
                                autofocus
                                autocomplete="off"
                                class="block w-full text-center text-3xl font-mono tracking-[0.5em] pl-[0.25em] py-3.5 border-gray-300 rounded-xl shadow-sm transition-all focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" 
                                placeholder="000000"
                            />
                        </div>

                        <!-- Error Message -->
                        @if ($errors->has('passcode'))
                            <div class="rounded-lg bg-red-50 border border-red-200 p-3 flex items-start gap-2.5">
                                <svg class="h-5 w-5 text-red-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <span class="text-xs text-red-800 font-medium">{{ $errors->first('passcode') }}</span>
                            </div>
                        @endif

                        <!-- Submit Button -->
                        <div>
                            <button 
                                type="submit" 
                                class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-200"
                            >
                                Verify & Access Proposal
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Card Footer Info -->
                <div class="px-8 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                    <span>Project: <strong>{{ $project->name }}</strong></span>
                    <span class="flex items-center gap-1">
                        <svg class="h-3.5 w-3.5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        SSL Secured
                    </span>
                </div>
            </div>
            
            <!-- Help Footer -->
            <p class="text-center text-xs text-slate-400 mt-6">
                Having trouble accessing? Please contact your project estimator or builder to request a new link or confirm the passcode.
            </p>
        </div>
    </body>
</html>
