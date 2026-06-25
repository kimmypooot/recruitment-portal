<template>
  <div class="min-h-screen flex flex-col bg-gray-50">

    <!-- ── Navbar ─────────────────────────────────────────────────────── -->
    <header class="bg-white sticky top-0 z-50 shadow-sm">
      <!-- CSC red brand stripe -->
      <div class="h-1 w-full bg-[#ec1c2d]"></div>
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

          <!-- Logo + wordmark -->
          <!-- LOGO: Replace /images/csc-logo.png with your actual logo file.
               Place the file at: public/images/csc-logo.png -->
          <Link href="/" class="flex items-center gap-3 group">
            <img src="/images/csc-logo.png" alt="CSC Logo"
              class="h-10 w-10 object-contain flex-shrink-0"
              onerror="this.style.display='none';this.nextElementSibling.style.display='flex'" />
            <div class="w-10 h-10 rounded-lg bg-[#2a338f] items-center justify-center flex-shrink-0 hidden">
              <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
              </svg>
            </div>
            <div class="leading-tight">
              <p class="text-sm font-bold text-gray-900 group-hover:text-[#2a338f] transition-colors">CSC Regional Office VIII</p>
              <p class="text-xs text-gray-500">Recruitment Portal</p>
            </div>
          </Link>

          <!-- Desktop nav -->
          <nav class="hidden md:flex items-center gap-1">
            <Link href="/"
              class="px-3 py-2 rounded-md text-sm font-medium transition-colors"
              :class="$page.url === '/' ? 'bg-[#2a338f]/10 text-[#2a338f]' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'">
              Vacancies
            </Link>
            <Link href="/how-to-apply"
              class="px-3 py-2 rounded-md text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition-colors">
              How to Apply
            </Link>
            <Link href="/about"
              class="px-3 py-2 rounded-md text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition-colors">
              About
            </Link>
          </nav>

          <!-- Auth buttons + mobile hamburger -->
          <div class="flex items-center gap-2">
            <Link href="/login"
              class="hidden sm:inline-flex px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 transition-colors">
              Sign In
            </Link>
            <Link href="/register"
              class="hidden sm:inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-[#2a338f] hover:bg-[#1e2570] rounded-lg transition-colors shadow-sm">
              Register
            </Link>
            <button @click="mobileOpen = !mobileOpen" class="md:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100 transition-colors">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
              </svg>
            </button>
          </div>
        </div>
      </div>
    </header>

    <!-- ── Mobile nav panel ────────────────────────────────────────── -->
    <Transition
      enter-active-class="transition-all duration-200 ease-out"
      enter-from-class="opacity-0 -translate-y-2"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition-all duration-150 ease-in"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 -translate-y-2">
      <nav v-if="mobileOpen" class="md:hidden bg-white border-b border-gray-200 px-4 py-3 space-y-1">
        <Link href="/" @click="mobileOpen = false"
          class="block px-3 py-2 rounded-lg text-sm font-medium transition-colors"
          :class="$page.url === '/' ? 'bg-[#2a338f]/10 text-[#2a338f]' : 'text-gray-600 hover:bg-gray-100'">
          Vacancies
        </Link>
        <Link href="/how-to-apply" @click="mobileOpen = false"
          class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 transition-colors">
          How to Apply
        </Link>
        <Link href="/about" @click="mobileOpen = false"
          class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 transition-colors">
          About
        </Link>
        <hr class="my-2 border-gray-100" />
        <Link href="/login" @click="mobileOpen = false"
          class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 transition-colors">
          Sign In
        </Link>
        <Link href="/register" @click="mobileOpen = false"
          class="block px-3 py-2 rounded-lg text-sm font-medium text-white bg-[#2a338f] hover:bg-[#1e2570] transition-colors text-center rounded-lg">
          Register
        </Link>
      </nav>
    </Transition>

    <!-- ── Page Content (slot) ────────────────────────────────────────── -->
    <main class="flex-1">
      <slot />
    </main>

    <!-- ── Footer ─────────────────────────────────────────────────────── -->
    <footer class="bg-white border-t border-gray-200 mt-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

          <!-- Col 1: Brand -->
          <div>
            <div class="flex items-center gap-2 mb-3">
              <!-- LOGO (footer): same file as navbar — public/images/csc-logo.png -->
              <img src="/images/csc-logo.png" alt="CSC Logo" class="h-7 w-7 object-contain"
                onerror="this.style.display='none';this.nextElementSibling.style.display='flex'" />
              <div class="w-7 h-7 rounded bg-[#2a338f] items-center justify-center hidden">
                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                </svg>
              </div>
              <span class="text-sm font-bold text-gray-900">CSC Regional Office VIII</span>
            </div>
            <p class="text-xs text-gray-500 leading-relaxed">
              Civil Service Commission Regional Office VIII.<br>
              Recruitment and selection for government positions.
            </p>
          </div>

          <!-- Col 2: Quick links -->
          <div>
            <h3 class="text-xs font-semibold text-gray-900 uppercase tracking-wider mb-3">Quick Links</h3>
            <ul class="space-y-2">
              <li><Link href="/" class="text-sm text-gray-500 hover:text-[#2a338f] transition-colors">Open Vacancies</Link></li>
              <li><Link href="/how-to-apply" class="text-sm text-gray-500 hover:text-[#2a338f] transition-colors">How to Apply</Link></li>
              <li><Link href="/register" class="text-sm text-gray-500 hover:text-[#2a338f] transition-colors">Register as Applicant</Link></li>
              <li><Link href="/login" class="text-sm text-gray-500 hover:text-[#2a338f] transition-colors">Track My Application</Link></li>
            </ul>
          </div>

          <!-- Col 3: Contact -->
          <div>
            <h3 class="text-xs font-semibold text-gray-900 uppercase tracking-wider mb-3">Contact</h3>
            <ul class="space-y-2 text-sm text-gray-500">
              <li class="flex items-start gap-2">
                <svg class="w-4 h-4 mt-0.5 flex-shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                ro08.hrd@csc.gov.ph
              </li>
              <li class="flex items-start gap-2">
                <svg class="w-4 h-4 mt-0.5 flex-shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
                (053) 888-1811
              </li>
              <li class="flex items-start gap-2">
                <svg class="w-4 h-4 mt-0.5 flex-shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Civil Service Commission Regional Office VIII - Human Resource Division
              </li>
            </ul>
          </div>
        </div>

        <div class="mt-8 pt-6 border-t border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-2">
          <p class="text-xs text-gray-400">© {{ new Date().getFullYear() }} Civil Service Commission. All rights reserved.</p>
          <p class="text-xs text-gray-400">Republic of the Philippines</p>
        </div>
      </div>
    </footer>

  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'

const mobileOpen = ref(false)
</script>