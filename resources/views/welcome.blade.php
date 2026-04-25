<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>LegiDash - Command Center for Legislative Offices</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <meta name="description"
        content="LegiDash - The command center for legislative offices. Track policy issues, prepare for meetings, manage key relationships, and keep your team aligned—all in one secure platform.">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Source+Serif+4:opsz,wght@8..60,400;8..60,600;8..60,700&family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Vite for Tailwind CSS (needed for modal) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        *,
        *::before,
        *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #0c1220;
            min-height: 100vh;
            overflow-x: hidden;
            color: #e2e8f0;
        }

        .page-wrapper {
            background: linear-gradient(180deg, #0c1220 0%, #162032 50%, #1a2744 100%);
            min-height: 100vh;
            position: relative;
        }

        /* Subtle gradient glow */
        .page-wrapper::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 100%;
            background-image: radial-gradient(ellipse at 50% -20%, rgba(59, 130, 246, 0.08) 0%, transparent 60%);
            pointer-events: none;
        }

        .serif {
            font-family: 'Source Serif 4', Georgia, serif;
        }

        .gradient-text {
            background: linear-gradient(135deg, #60a5fa 0%, #3b82f6 50%, #1d4ed8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .card-glass {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .card-glass:hover {
            background: rgba(255, 255, 255, 0.06);
            border-color: rgba(59, 130, 246, 0.3);
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: #fff;
            padding: 14px 32px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 15px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            letter-spacing: 0.3px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(59, 130, 246, 0.35);
        }

        .btn-secondary {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #e2e8f0;
            padding: 14px 32px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 15px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.3);
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.2s;
        }

        .nav-link:hover {
            color: #fff;
        }

        .fade-in {
            animation: fadeIn 0.8s ease-out forwards;
            opacity: 0;
        }

        .fade-in-delay-1 { animation-delay: 0.15s; }
        .fade-in-delay-2 { animation-delay: 0.3s; }
        .fade-in-delay-3 { animation-delay: 0.45s; }
        .fade-in-delay-4 { animation-delay: 0.6s; }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(16px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .pulse-dot {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .text-muted {
            color: rgba(255, 255, 255, 0.6);
        }

        .text-muted-strong {
            color: rgba(255, 255, 255, 0.5);
        }

        .divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            margin: 60px 0;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 24px;
        }

        @media (max-width: 768px) {
            .feature-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="page-wrapper">
        <!-- Navigation -->
        <nav style="padding: 24px 48px; position: relative; z-index: 10;">
            <div style="max-width: 1200px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 14px;">
                    <img src="{{ asset('images/logo.png') }}" alt="LegiDash" style="height: 40px; width: auto;">
                </div>

                @if (Route::has('login'))
                    <div style="display: flex; align-items: center; gap: 20px;">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="btn-primary">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="nav-link">Sign In</a>
                            <button onclick="Livewire.dispatch('openBetaModal')" class="btn-primary">
                                Request Beta Access
                            </button>
                        @endauth
                    </div>
                @endif
            </div>
        </nav>

        <!-- Hero Section -->
        <main style="padding: 60px 24px 80px;">
            <div style="max-width: 1000px; margin: 0 auto; text-align: center;">
                <!-- Beta Badge -->
                <div class="fade-in" style="display: inline-flex; align-items: center; gap: 10px; padding: 10px 18px; border-radius: 100px; background: rgba(234, 179, 8, 0.1); border: 1px solid rgba(234, 179, 8, 0.25); margin-bottom: 36px;">
                    <span class="pulse-dot" style="width: 8px; height: 8px; border-radius: 50%; background: #eab308;"></span>
                    <span style="color: rgba(255,255,255,0.8); font-size: 13px; font-weight: 500;">Beta · Built for elected offices</span>
                </div>

                <!-- Main Heading -->
                <h1 class="fade-in fade-in-delay-1 serif" style="font-size: clamp(40px, 6vw, 64px); font-weight: 700; line-height: 1.15; margin-bottom: 28px; color: #fff;">
                    The command center for<br>
                    <span class="gradient-text">legislative offices</span>
                </h1>

                <!-- Subheading -->
                <p class="fade-in fade-in-delay-2 text-muted" style="font-size: 18px; max-width: 680px; margin: 0 auto 44px; line-height: 1.75;">
                    Track policy issues, prepare for meetings, manage key relationships, and keep your team aligned—all in one secure platform.
                </p>

                <!-- CTA Buttons -->
                <div class="fade-in fade-in-delay-3" style="display: flex; flex-wrap: wrap; gap: 16px; justify-content: center; margin-bottom: 80px;">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="btn-primary">
                            Go to Dashboard
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    @else
                        <button onclick="Livewire.dispatch('openBetaModal')" class="btn-primary">
                            Request Beta Access
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </button>
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}" class="btn-secondary">Sign In</a>
                        @endif
                    @endauth
                </div>

                <!-- Primary Feature Cards -->
                <div class="fade-in fade-in-delay-4 feature-grid">
                    <!-- Issue Tracking -->
                    <div class="card-glass" style="border-radius: 16px; padding: 28px; text-align: left;">
                        <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(59, 130, 246, 0.15); display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                            <svg width="24" height="24" fill="none" stroke="#60a5fa" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h3 style="color: #fff; font-weight: 600; font-size: 17px; margin-bottom: 10px;">Issue Tracking</h3>
                        <p class="text-muted-strong" style="font-size: 14px; line-height: 1.65;">Track legislation, policy priorities, and emerging issues. Link everything to meetings, decisions, and key contacts.</p>
                    </div>

                    <!-- Meeting Intelligence -->
                    <div class="card-glass" style="border-radius: 16px; padding: 28px; text-align: left;">
                        <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(139, 92, 246, 0.15); display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                            <svg width="24" height="24" fill="none" stroke="#a78bfa" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 style="color: #fff; font-weight: 600; font-size: 17px; margin-bottom: 10px;">Meeting Intelligence</h3>
                        <p class="text-muted-strong" style="font-size: 14px; line-height: 1.65;">Prepare briefings, capture notes, and track follow-ups. AI-powered summaries help you stay on top of every conversation.</p>
                    </div>

                    <!-- Relationship Management -->
                    <div class="card-glass" style="border-radius: 16px; padding: 28px; text-align: left;">
                        <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(34, 197, 94, 0.15); display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                            <svg width="24" height="24" fill="none" stroke="#4ade80" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <h3 style="color: #fff; font-weight: 600; font-size: 17px; margin-bottom: 10px;">Relationship Management</h3>
                        <p class="text-muted-strong" style="font-size: 14px; line-height: 1.65;">Track stakeholders, advocacy groups, lobbyists, and colleagues. See interaction history and relationship context at a glance.</p>
                    </div>
                </div>

                <div class="divider"></div>

                <!-- Secondary Features -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 32px; text-align: left; max-width: 900px; margin: 0 auto;">
                    <div>
                        <div style="color: #60a5fa; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px;">Knowledge Base</div>
                        <p class="text-muted-strong" style="font-size: 14px; line-height: 1.6;">Searchable repository of documents, decisions, and institutional memory.</p>
                    </div>
                    <div>
                        <div style="color: #a78bfa; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px;">Media Tracking</div>
                        <p class="text-muted-strong" style="font-size: 14px; line-height: 1.6;">Track press coverage, manage inquiries, and coordinate communications.</p>
                    </div>
                    <div>
                        <div style="color: #4ade80; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px;">AI Assistant</div>
                        <p class="text-muted-strong" style="font-size: 14px; line-height: 1.6;">Ask questions about your office's history, decisions, and relationships.</p>
                    </div>
                    <div>
                        <div style="color: #f472b6; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px;">Team Coordination</div>
                        <p class="text-muted-strong" style="font-size: 14px; line-height: 1.6;">Keep your entire team aligned with shared dashboards and workflows.</p>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer style="padding: 40px 24px; text-align: center; border-top: 1px solid rgba(255,255,255,0.05);">
            <p style="color: rgba(255,255,255,0.35); font-size: 13px;">
                © {{ date('Y') }} LegiDash. Built for public service.
            </p>
        </footer>
    </div>

    {{-- Beta Request Form Modal --}}
    @guest
        <livewire:beta-request-form />
    @endguest

    @livewireScripts
</body>

</html>
