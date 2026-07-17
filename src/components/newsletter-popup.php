<?php
/**
 * Newsletter Popup Component
 * Shows subscription popup for first-time visitors
 */
if (session_status() === PHP_SESSION_NONE) session_start();

// Check if popup was already shown today
$popup_shown_key = 'newsletter_popup_shown_' . date('Y-m-d');
if (!empty($_SESSION[$popup_shown_key])) {
    return; // Already shown today
}
?>
<div x-data="{ show: false, email: '', submitted: false, error: '' }" 
     x-init="setTimeout(() => { if (!localStorage.getItem('newsletter_subscribed')) show = true; }, 3000)"
     x-show="show" 
     class="fixed inset-0 z-[200] flex items-center justify-center p-4"
     style="display: none;"
     @keydown.escape.window="show = false">
    
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="show = false"></div>
    
    <!-- Modal -->
    <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl max-w-md w-full overflow-hidden"
         @click.stop>
        
        <!-- Close button -->
        <button @click="show = false" 
                class="absolute top-3 right-3 w-8 h-8 flex items-center justify-center rounded-full bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
            <svg class="w-4 h-4 text-slate-600 dark:text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
        
        <!-- Content -->
        <div class="p-8 text-center">
            <!-- Icon -->
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gradient-to-br from-red-500 to-red-600 flex items-center justify-center">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            
            <h3 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">
                <?= lang_label('ताजा खबर पाउनुहोस्!', 'Get Breaking News First!') ?>
            </h3>
            
            <p class="text-slate-600 dark:text-slate-300 mb-6">
                <?= lang_label(
                    'ताजा खबर, विश्लेषण र अपडेट प्राप्त गर्नुहोस्। कुनै स्प्याम छैन।',
                    'Get latest news, analysis and updates. No spam, ever.'
                ) ?>
            </p>
            
            <!-- Success message -->
            <div x-show="submitted" x-cloak class="text-center py-4">
                <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <p class="text-green-600 dark:text-green-400 font-semibold">
                    <?= lang_label('धन्यवाद! तपाईं सब्सक्राइब भर्नुभयो।', "Thank you! You're subscribed.") ?>
                </p>
            </div>
            
            <!-- Form -->
            <form x-show="!submitted" @submit.prevent="
                if (!email || !email.includes('@')) {
                    error = '<?= lang_label('कृपया मान्य इमेल प्रविष्ट गर्नुहोस्', 'Please enter a valid email') ?>';
                    return;
                }
                error = '';
                submitted = true;
                localStorage.setItem('newsletter_subscribed', '1');
            " class="space-y-3">
                <div>
                    <input type="email" x-model="email" 
                           placeholder="<?= lang_label('तपाईंको इमेल', 'Your email') ?>"
                           class="w-full px-4 py-3 rounded-lg border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition-colors">
                    <p x-show="error" x-text="error" class="text-red-500 text-sm text-left mt-1"></p>
                </div>
                <button type="submit" 
                        class="w-full py-3 px-4 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white font-semibold rounded-lg transition-all">
                    <?= lang_label('सब्सक्राइब गर्नुहोस्', 'Subscribe Now') ?>
                </button>
            </form>
            
            <!-- No thanks link -->
            <button @click="show = false" 
                    class="mt-4 text-sm text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300">
                <?= lang_label('होइन, धन्यवाद', 'No thanks, maybe later') ?>
            </button>
        </div>
    </div>
</div>
