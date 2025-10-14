<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @php($title = 'BTransit — BOUESTI Taxi Booking System')
        @include('partials.head')
        <style>
            :root {
                --primary: #16A34A; /* Green */
                --accent: #F59E0B;  /* Amber */
                --text-dark: #1F2937;
                --bg-light: #F9FAFB;
                --white: #FFFFFF;
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
                                Your Premium Ride, Just a Click Away
                            </h1>
                            <p class="tw-body max-w-prose">
                                Experience the comfort and convenience of BTransit. Safe, reliable, and always on time.
                            </p>
                            <div class="flex flex-wrap gap-3">
                                <a href="{{ route('register') }}" class="btn-primary inline-flex items-center rounded-lg bg-[var(--primary)] px-5 py-2.5 text-white shadow-md transition hover:scale-[1.01]">
                                    Get Started
                                </a>
                                <a href="#features" class="inline-flex items-center rounded-lg border border-[var(--primary)] px-5 py-2.5 text-[var(--primary)] transition hover:bg-[var(--primary)] hover:text-white">
                                    Learn More
                                </a>
                            </div>
                            <div class="flex items-center gap-2 text-sm text-gray-600">
                                <span class="inline-flex h-2 w-2 rounded-full bg-[var(--accent)]"></span>
                                <span>Trusted by students and drivers on campus</span>
                            </div>
                        </div>
                        <div class="relative">
                            <div class="aspect-video w-full overflow-hidden rounded-2xl bg-black shadow-md ring-1 ring-gray-200">
                                <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuC3gziRkXi4zVI9tlJVx9-RhItMPuqvTlVrZ0Vdp_qbYCDmXvLrTpMdPDiPe17C2CCjg5AvqW06WvyJIXDHXg4cZoXjFXlyS9uNKaLT-pZuU7Vg-2HY4-GOcWAfWfuKMFHiPz5IlB5cAo6r7bw5FWwliuUed5mo8jmnTDlpNJzwAMAKRpYgstc4yGkJqzFxGd1g1v760PSMwlhispRV6ny92r84rh7-1PPweUouCx6hk0vkokCW9v66b1rVdf-G74jGdliT_mGv0Ds" alt="City night ride" class="h-full w-full object-cover"/>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Features Section -->
            <section id="features" class="mt-16 sm:mt-24">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <h2 class="tw-heading">Why Choose BTransit?</h2>
                    <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        <div class="card transition hover:shadow-lg">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-lg bg-[var(--accent)]/20 flex items-center justify-center">⚡</div>
                                <div>
                                    <div class="font-medium">Easy Booking</div>
                                    <div class="tw-body">Book rides in seconds, anytime.</div>
                                </div>
                            </div>
                        </div>
                        <div class="card transition hover:shadow-lg">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-lg bg-[var(--accent)]/20 flex items-center justify-center">💳</div>
                                <div>
                                    <div class="font-medium">Reliable Drivers</div>
                                    <div class="tw-body">Verified drivers ensure safety and comfort.</div>
                                </div>
                            </div>
                        </div>
                        <div class="card transition hover:shadow-lg">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-lg bg-[var(--accent)]/20 flex items-center justify-center">🚘</div>
                                <div>
                                    <div class="font-medium">Transparent Pricing</div>
                                    <div class="tw-body">Know your fare before you ride.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- How It Works Section -->
            <section id="how-it-works" class="mt-16 sm:mt-24">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <h2 class="tw-heading">How It Works</h2>
                    <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        <div class="card transition hover:shadow-lg">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-lg bg-[var(--accent)]/20 flex items-center justify-center">📝</div>
                                <div>
                                    <div class="font-medium">Step 1: Request a Ride</div>
                                    <div class="tw-body">Choose your destination and pickup preferences.</div>
                                </div>
                            </div>
                        </div>
                        <div class="card transition hover:shadow-lg">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-lg bg-[var(--accent)]/20 flex items-center justify-center">🤝</div>
                                <div>
                                    <div class="font-medium">Step 2: Get Matched</div>
                                    <div class="tw-body">We connect you with a reliable nearby driver.</div>
                                </div>
                            </div>
                        </div>
                        <div class="card transition hover:shadow-lg">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-lg bg-[var(--accent)]/20 flex items-center justify-center">⭐</div>
                                <div>
                                    <div class="font-medium">Step 3: Enjoy Your Trip</div>
                                    <div class="tw-body">Track progress and arrive safely, on time.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- CTA: Riders -->
            <section id="about" class="mt-16 sm:mt-24">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="rounded-2xl bg-[var(--primary)] px-6 py-10 text-white shadow-md sm:px-8 lg:px-10">
                        <div class="grid items-center gap-6 md:grid-cols-3">
                            <div class="md:col-span-2">
                                <h3 class="text-2xl font-semibold">Ready to Ride with BTransit?</h3>
                                <p class="mt-2 text-white/90">Join thousands of satisfied customers and enjoy a hassle-free journey.</p>
                            </div>
                            <div class="flex md:justify-end">
                                <a href="{{ route('register') }}" class="inline-flex items-center rounded-lg bg-white px-5 py-2.5 font-medium text-[var(--primary)] shadow-md transition hover:scale-[1.01]">Join Now</a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- CTA: Drivers -->
            <section id="cta-drivers" class="mt-8 sm:mt-12">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="rounded-2xl bg-[var(--primary)] px-6 py-10 text-white shadow-md sm:px-8 lg:px-10">
                        <div class="grid gap-6">
                            <h3 class="text-2xl font-semibold">Become a BTransit Driver</h3>
                            <p class="text-white/90">Start earning on your schedule. Drive with BTransit and enjoy flexibility and benefits.</p>
                            <div class="grid gap-4 sm:grid-cols-3">
                                <div class="rounded-lg bg-white/10 p-4">
                                    <div class="font-medium">Weekly Payouts</div>
                                    <div class="text-white/80 text-sm">Get paid fast and regularly.</div>
                                </div>
                                <div class="rounded-lg bg-white/10 p-4">
                                    <div class="font-medium">Flexible Hours</div>
                                    <div class="text-white/80 text-sm">Drive when it fits your life.</div>
                                </div>
                                <div class="rounded-lg bg-white/10 p-4">
                                    <div class="font-medium">24/7 Support</div>
                                    <div class="text-white/80 text-sm">We’re here whenever you need us.</div>
                                </div>
                            </div>
                            <div>
                                <a href="{{ route('register') }}" class="inline-flex items-center rounded-lg bg-white px-5 py-2.5 font-medium text-[var(--primary)] shadow-md transition hover:scale-[1.01]">Join as a Driver</a>
                            </div>
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
                        <span class="hidden sm:inline-block h-4 w-px bg-gray-300"></span>
                        <a href="https://twitter.com" target="_blank" rel="noopener noreferrer" class="hover:text-[var(--primary)]">Twitter</a>
                        <a href="https://instagram.com" target="_blank" rel="noopener noreferrer" class="hover:text-[var(--primary)]">Instagram</a>
                        <a href="https://github.com" target="_blank" rel="noopener noreferrer" class="hover:text-[var(--primary)]">GitHub</a>
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