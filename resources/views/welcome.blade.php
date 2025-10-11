<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @php($title = 'BTransit — BOUESTI Taxi Booking System')
        @include('partials.head')
        <style>
            :root {
                --primary: #007F5F;
                --accent: #F4C430;
                --text-dark: #1F2937;
                --bg-light: #F9FAFB;
                --white: #FFFFFF;
                --success: #16A34A;
            }
        </style>
    </head>
    <body class="min-h-screen bg-[var(--bg-light)] text-[var(--text-dark)]" style="font-family: Inter, Nunito, Poppins, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;">
        <!-- Header / Navigation Bar -->
        <header class="fixed top-0 z-40 w-full bg-white/80 backdrop-blur-md shadow-sm">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <!-- Logo -->
                    <a href="#" class="flex items-center gap-2 font-semibold text-lg" aria-label="BTransit">
                        <span class="text-2xl">🚖</span>
                        <span>BTransit</span>
                    </a>
                    <!-- Desktop Nav -->
                    <nav class="hidden md:flex items-center gap-6 text-sm">
                        <a href="#home" data-nav class="border-b-2 border-transparent hover:text-[var(--primary)] hover:border-[var(--accent)] transition">Home</a>
                        <a href="#features" data-nav class="border-b-2 border-transparent hover:text-[var(--primary)] hover:border-[var(--accent)] transition">Features</a>
                        <a href="#about" data-nav class="border-b-2 border-transparent hover:text-[var(--primary)] hover:border-[var(--accent)] transition">About</a>
                        <a href="#contact" data-nav class="border-b-2 border-transparent hover:text-[var(--primary)] hover:border-[var(--accent)] transition">Contact</a>
                        <a href="{{ route('login') }}" class="hover:text-[var(--primary)] transition">Login</a>
                        <a href="{{ route('register') }}" class="ml-2 inline-flex items-center rounded-lg bg-[var(--primary)] px-4 py-2 text-white shadow-sm transition hover:brightness-95">Register</a>
                    </nav>
                    <!-- Mobile toggle -->
                    <button id="menu-toggle" class="md:hidden inline-flex items-center justify-center rounded-lg border border-gray-200 p-2 text-gray-700 hover:bg-gray-50" aria-label="Open menu">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h16.5m-16.5 6h16.5m-16.5 6h16.5" />
                        </svg>
                    </button>
                </div>
            </div>
            <!-- Mobile Nav -->
            <div id="mobile-menu" class="md:hidden hidden border-t border-gray-200 bg-white">
                <div class="mx-auto max-w-7xl px-4 py-3 space-y-2 text-sm">
                    <a href="#home" data-nav class="block px-2 py-1 hover:text-[var(--primary)] hover:border-s-4 hover:border-[var(--accent)] border-s-4 border-transparent">Home</a>
                    <a href="#features" data-nav class="block px-2 py-1 hover:text-[var(--primary)] hover:border-s-4 hover:border-[var(--accent)] border-s-4 border-transparent">Features</a>
                    <a href="#about" data-nav class="block px-2 py-1 hover:text-[var(--primary)] hover:border-s-4 hover:border-[var(--accent)] border-s-4 border-transparent">About</a>
                    <a href="#contact" data-nav class="block px-2 py-1 hover:text-[var(--primary)] hover:border-s-4 hover:border-[var(--accent)] border-s-4 border-transparent">Contact</a>
                    <a href="{{ route('login') }}" class="block px-2 py-1">Login</a>
                    <a href="{{ route('register') }}" class="mt-2 inline-flex items-center rounded-lg bg-[var(--primary)] px-4 py-2 text-white shadow-sm">Register</a>
                </div>
            </div>
        </header>

        <main id="home" class="pt-20 sm:pt-24">
            <!-- Hero Section -->
            <section class="relative">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="grid items-center gap-8 lg:grid-cols-2">
                        <div class="space-y-6">
                            <h1 class="text-3xl font-semibold sm:text-4xl lg:text-5xl">
                                BTransit — BOUESTI Taxi Booking System
                            </h1>
                            <p class="tw-body max-w-prose">
                                Seamless campus rides: book, pay securely, and track driver availability. Clean, fast, and built for BOUESTI.
                            </p>
                            <div class="flex flex-wrap gap-3">
                                <a href="{{ route('register') }}" class="btn-primary inline-flex items-center rounded-lg bg-[var(--primary)] px-5 py-2.5 text-white shadow-md transition hover:scale-[1.01]">
                                    Get Started
                                </a>
                                <a href="{{ route('login') }}" class="inline-flex items-center rounded-lg border border-[var(--primary)] px-5 py-2.5 text-[var(--primary)] transition hover:bg-[var(--primary)] hover:text-white">
                                    Login
                                </a>
                            </div>
                            <div class="flex items-center gap-2 text-sm text-gray-600">
                                <span class="inline-flex h-2 w-2 rounded-full bg-[var(--accent)]"></span>
                                <span>Trusted by students and drivers on campus</span>
                            </div>
                        </div>
                        <div class="relative">
                            <div class="aspect-video w-full overflow-hidden rounded-2xl bg-white shadow-md ring-1 ring-gray-200">
                                <div class="h-full w-full grid place-items-center bg-gradient-to-br from-[var(--primary)]/10 via-[var(--accent)]/10 to-white">
                                    <div class="text-7xl animate-[pulse_3s_ease-in-out_infinite]">🚖</div>
                                </div>
                            </div>
                            <div class="absolute -bottom-6 -left-6 hidden lg:block h-24 w-24 rounded-full bg-[var(--accent)]/20 blur"></div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Features Section -->
            <section id="features" class="mt-16 sm:mt-24">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <h2 class="tw-heading">Features</h2>
                    <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="card transition hover:shadow-lg">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-lg bg-[var(--accent)]/20 flex items-center justify-center">⚡</div>
                                <div>
                                    <div class="font-medium">Quick Booking</div>
                                    <div class="tw-body">Book rides in seconds with a clean flow.</div>
                                </div>
                            </div>
                        </div>
                        <div class="card transition hover:shadow-lg">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-lg bg-[var(--accent)]/20 flex items-center justify-center">💳</div>
                                <div>
                                    <div class="font-medium">Secure Payments</div>
                                    <div class="tw-body">Paystack integration for reliable payments.</div>
                                </div>
                            </div>
                        </div>
                        <div class="card transition hover:shadow-lg">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-lg bg-[var(--accent)]/20 flex items-center justify-center">🚘</div>
                                <div>
                                    <div class="font-medium">Live Availability</div>
                                    <div class="tw-body">Drivers toggle presence with animated switches.</div>
                                </div>
                            </div>
                        </div>
                        <div class="card transition hover:shadow-lg">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-lg bg-[var(--accent)]/20 flex items-center justify-center">💵</div>
                                <div>
                                    <div class="font-medium">Cash Confirmation</div>
                                    <div class="tw-body">Modal confirmation before completing cash rides.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- About / CTA Section -->
            <section id="about" class="mt-16 sm:mt-24">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="card grid gap-3 md:grid-cols-3 md:items-center">
                        <div class="md:col-span-2">
                            <h3 class="text-xl font-semibold">Built for BOUESTI</h3>
                            <p class="tw-body mt-2">BTransit is tailored to meet campus mobility needs with speed, clarity, and safety. Enjoy minimal UI, fast actions, and transparent fares.</p>
                        </div>
                        <div class="flex md:justify-end">
                            <a href="{{ route('user.rides.book') }}" class="btn-primary inline-flex items-center rounded-lg bg-[var(--primary)] px-5 py-2.5 text-white shadow-md transition hover:scale-[1.01]">
                                Book a Ride
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Contact placeholder -->
            <section id="contact" class="mt-16 sm:mt-24">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="card">
                        <h3 class="text-xl font-semibold">Contact</h3>
                        <p class="tw-body mt-2">For support or inquiries, reach us via the dashboard once signed in.</p>
                    </div>
                </div>
            </section>
        </main>

        <!-- Footer -->
        <footer class="mt-16 border-t border-gray-200 bg-white">
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 text-sm text-gray-600">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>© {{ date('Y') }} BTransit. All rights reserved.</div>
                    <div class="flex items-center gap-4">
                        <a href="#features" class="hover:text-[var(--primary)]">Features</a>
                        <a href="#about" class="hover:text-[var(--primary)]">About</a>
                        <a href="#contact" class="hover:text-[var(--primary)]">Contact</a>
                    </div>
                </div>
            </div>
        </footer>

        <script>
            // Mobile menu toggle
            const toggle = document.getElementById('menu-toggle');
            const menu = document.getElementById('mobile-menu');
            if (toggle && menu) {
                toggle.addEventListener('click', () => menu.classList.toggle('hidden'));
            }

            // Active link highlight based on hash
            const navLinks = Array.from(document.querySelectorAll('[data-nav]'));
            function setActive() {
                const hash = window.location.hash || '#home';
                navLinks.forEach((el) => {
                    const isActive = el.getAttribute('href') === hash;
                    // Desktop style
                    if (el.closest('nav')) {
                        el.classList.toggle('border-[var(--accent)]', isActive);
                        el.classList.toggle('text-[var(--primary)]', isActive);
                    }
                    // Mobile style
                    if (el.closest('#mobile-menu')) {
                        el.classList.toggle('border-[var(--accent)]', isActive);
                        el.classList.toggle('text-[var(--primary)]', isActive);
                    }
                });
            }
            window.addEventListener('hashchange', setActive);
            window.addEventListener('load', setActive);
        </script>
    </body>
</html>