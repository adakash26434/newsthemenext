<?php
/**
 * Mobile Bottom Navigation Component
 * PWA-style bottom navigation for mobile users
 */
?>
<nav class="mobile-bottom-nav hidden lg:hidden" x-data="{ active: window.location.pathname }">
  <div class="flex items-center justify-around bg-white dark:bg-slate-800 border-t border-slate-200 dark:border-slate-700 safe-area-bottom">
    
    <!-- Home -->
    <a href="/" class="nav-item" :class="active === '/' ? 'active' : ''">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
      </svg>
      <span>Home</span>
    </a>
    
    <!-- Categories -->
    <a href="/categories" class="nav-item" :class="active.includes('/category') ? 'active' : ''">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
      </svg>
      <span>Categories</span>
    </a>
    
    <!-- Trending -->
    <a href="/trending" class="nav-item" :class="active === '/trending' ? 'active' : ''">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
      </svg>
      <span>Trending</span>
    </a>
    
    <!-- Bookmarks -->
    <a href="/saved" class="nav-item" :class="active === '/saved' ? 'active' : ''">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
      </svg>
      <span>Saved</span>
    </a>
    
    <!-- Profile -->
    <a href="/profile" class="nav-item" :class="active === '/profile' ? 'active' : ''">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
      </svg>
      <span>Profile</span>
    </a>
    
  </div>
</nav>

<style>
.mobile-bottom-nav {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  z-index: 100;
  padding-bottom: env(safe-area-inset-bottom, 0);
}
.mobile-bottom-nav .nav-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2px;
  padding: 8px 12px;
  color: #64748b;
  transition: color 0.2s;
  -webkit-tap-highlight-color: transparent;
}
.mobile-bottom-nav .nav-item span {
  font-size: 10px;
  font-weight: 500;
}
.mobile-bottom-nav .nav-item:hover,
.mobile-bottom-nav .nav-item.active {
  color: var(--c-primary, #7F1D1D);
}
.mobile-bottom-nav .nav-item.active svg {
  transform: scale(1.1);
}
[data-theme="dark"] .mobile-bottom-nav {
  background: #1e293b;
  border-color: #2d3a4e;
}
[data-theme="dark"] .mobile-bottom-nav .nav-item {
  color: #94a3b8;
}
</style>
