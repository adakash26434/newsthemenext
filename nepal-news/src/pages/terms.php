<?php
$page_title = 'सेवा शर्तहरू — ' . site_name();
require SRC_DIR . '/layout/header.php';
?>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
  <div class="lg:col-span-3">
    <div class="stat-card">
      <div class="section-heading mb-5">
        <span class="flex items-center gap-2"><?= icon('file-text','w-4 h-4') ?> सेवा शर्तहरू</span>
      </div>
      <div class="text-sm leading-7 space-y-5" style="color:var(--c-text2)">
        <p class="text-xs" style="color:var(--c-muted)">अन्तिम अपडेट: <?= nepali_date(date('Y-m-d')) ?></p>

        <div>
          <h2 class="font-bold text-base mb-2" style="color:var(--c-text)">१. स्वीकृति</h2>
          <p><?= h(site_name()) ?> वेबसाइट प्रयोग गरेर, तपाईं यी सेवा शर्तहरू स्वीकार गर्नुहुन्छ।</p>
        </div>

        <div>
          <h2 class="font-bold text-base mb-2" style="color:var(--c-text)">२. सामग्री प्रयोग</h2>
          <ul class="list-disc list-inside space-y-1">
            <li>साइटमा प्रकाशित सामग्री हाम्रो बौद्धिक सम्पत्ति हो</li>
            <li>व्यक्तिगत प्रयोजनका लागि सामग्री पढ्न र साझा गर्न स्वतन्त्र</li>
            <li>व्यावसायिक प्रयोजनका लागि पूर्व अनुमति आवश्यक</li>
            <li>साइटको सामग्री कपी गर्दा स्रोत उल्लेख गर्नुपर्छ</li>
          </ul>
        </div>

        <div>
          <h2 class="font-bold text-base mb-2" style="color:var(--c-text)">३. टिप्पणी नियमहरू</h2>
          <ul class="list-disc list-inside space-y-1">
            <li>अपमानजनक, हिंसात्मक वा भेदभावपूर्ण टिप्पणी निषेध</li>
            <li>व्यक्तिगत जानकारी प्रकाशित गर्न निषेध</li>
            <li>स्प्याम वा विज्ञापन प्रकृतिका टिप्पणी हटाइनेछ</li>
            <li>नियम उल्लङ्घन गर्ने प्रयोगकर्तालाई प्रतिबन्ध लगाइनेछ</li>
          </ul>
        </div>

        <div>
          <h2 class="font-bold text-base mb-2" style="color:var(--c-text)">४. सटीकता र उत्तरदायित्व</h2>
          <p>हामी सटीक जानकारी प्रदान गर्न प्रयास गर्छौं, तर समाचारको सटीकतामा पूर्ण ग्यारेन्टी दिन सकिँदैन। कुनै त्रुटि भेटिएमा <a href="/contact" class="hover:underline" style="color:var(--c-primary)">हामीलाई जानकारी</a> दिनुहोस्।</p>
        </div>

        <div>
          <h2 class="font-bold text-base mb-2" style="color:var(--c-text)">५. बाह्य लिंकहरू</h2>
          <p>साइटमा बाह्य वेबसाइटका लिंक हुन सक्छन्। ती वेबसाइटहरूको सामग्री र नीतिका लागि हामी जिम्मेवार छैनौं।</p>
        </div>

        <div>
          <h2 class="font-bold text-base mb-2" style="color:var(--c-text)">६. परिवर्तन</h2>
          <p>हामी यी शर्तहरू जुनसुकै समय परिवर्तन गर्न सक्छौं। परिवर्तन साइटमा प्रकाशित गरिनेछ।</p>
        </div>

        <div>
          <h2 class="font-bold text-base mb-2" style="color:var(--c-text)">७. लागू कानुन</h2>
          <p>यी शर्तहरू नेपालको कानुन अनुसार नियन्त्रित हुनेछन्।</p>
        </div>
      </div>
    </div>
  </div>

  <aside class="space-y-5">
    <div class="sidebar-card">
      <div class="section-heading mb-3 text-sm font-bold">कानुनी</div>
      <div class="space-y-1.5">
        <a href="/privacy" class="footer-link flex items-center gap-2"><?= icon('shield','w-3.5 h-3.5') ?> गोपनीयता नीति</a>
        <a href="/terms"   class="footer-link flex items-center gap-2 font-semibold" style="color:var(--c-primary)"><?= icon('file-text','w-3.5 h-3.5') ?> सेवा शर्तहरू</a>
        <a href="/contact" class="footer-link flex items-center gap-2"><?= icon('mail','w-3.5 h-3.5') ?> सम्पर्क</a>
        <a href="/about"   class="footer-link flex items-center gap-2"><?= icon('info','w-3.5 h-3.5') ?> हाम्रोबारे</a>
      </div>
    </div>
    <?php render_ads('sidebar-top'); ?>
  </aside>
</div>

<?php require SRC_DIR . '/layout/footer.php'; ?>
