<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — ColdMail</title>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            sans: ['Syne', 'ui-sans-serif', 'system-ui', 'sans-serif'],
          },
        },
      },
    };
  </script>
  <style>
    body { font-family: 'Syne', ui-sans-serif, system-ui, sans-serif; }
  </style>
</head>
<body class="bg-[#0a0a0f] min-h-screen flex items-center justify-center p-4">

  <div class="w-full max-w-sm">

    <div class="text-center mb-8">
      <div class="w-14 h-14 mx-auto mb-5 rounded-2xl bg-[#7c6ef7]/15 border border-[#7c6ef7]/30 flex items-center justify-center">
        <svg class="w-6 h-6 text-[#7c6ef7]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" stroke-linecap="round"/>
          <polyline points="22,6 12,13 2,6"/>
        </svg>
      </div>
      <h1 class="text-3xl font-bold text-white tracking-tight">Cold<span class="text-[#a78bfa]">Mail</span></h1>
      <p class="text-zinc-500 text-sm mt-2.5">Sign in to manage your cold email campaigns</p>
    </div>

    <div class="bg-[#121218] border border-white/[0.08] rounded-3xl p-8 shadow-2xl">

      @if(session('error'))
        <div class="bg-red-500/10 border border-red-500/25 text-red-400 px-4 py-3 rounded-xl text-sm mb-5">
          {{ session('error') }}
        </div>
      @endif

      @if(session('success'))
        <div class="bg-emerald-500/10 border border-emerald-500/25 text-emerald-400 px-4 py-3 rounded-xl text-sm mb-5">
          {{ session('success') }}
        </div>
      @endif

      <a href="{{ route('google.redirect') }}"
         class="flex items-center justify-center gap-3 w-full border border-white/10 rounded-2xl px-4 py-3.5 text-sm font-semibold text-zinc-200 hover:bg-white/5 transition">
        <svg class="w-5 h-5" viewBox="0 0 24 24">
          <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
          <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
          <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
          <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
        </svg>
        Continue with Google
      </a>

      <p class="text-center text-xs text-zinc-600 mt-7">
        Only you can access this dashboard
      </p>

    </div>
  </div>

</body>
</html>