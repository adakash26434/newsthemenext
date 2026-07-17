<?php
$page_title = 'गोपनीयता नीति — ' . site_name();
require SRC_DIR . '/layout/header.php';
?>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
  <div class="lg:col-span-3">
    <div class="stat-card">
      <div class="section-heading mb-5">
        <span class="flex items-center gap-2"><?= icon('shield','w-4 h-4') ?> गोपनीयता नीति</span>
      </div>
      <div class="text-sm leading-7 space-y-5" style="color:var(--c-text2)">
        <p class="text-xs" style="color:var(--c-muted)">अन्तिम अपडेट: <?= nepali_date(date('Y-m-d')) ?></p>

        <div>
          <h2 class="font-bold text-base mb-2" style="color:var(--c-text)">१. परिचय</h2>
          <p><?= h(site_name()) ?> ले तपाईंको गोपनीयतालाई सम्मान गर्दछ। यो नीतिले हामी कसरी तपाईंको जानकारी सङ्कलन, प्रयोग र सुरक्षा गर्छौं भन्ने कुरा वर्णन गर्दछ।</p>
        </div>

        <div>
          <h2 class="font-bold text-base mb-2" style="color:var(--c-text)">२. सङ्कलित जानकारी</h2>
          <ul class="list-disc list-inside space-y-1">
            <li>वेबसाइट भ्रमण गर्दा स्वत: सङ्कलित डेटा (IP ठेगाना, ब्राउजर प्रकार)</li>
            <li>न्यूजलेटर सदस्यताका लागि प्रदान गरिएको इमेल ठेगाना</li>
            <li>टिप्पणी प्रस्तुत गर्दा प्रदान गरिएको जानकारी</li>
            <li>सम्पर्क फर्म मार्फत पठाइएको जानकारी</li>
          </ul>
        </div>

        <div>
          <h2 class="font-bold text-base mb-2" style="color:var(--c-text)">३. जानकारीको प्रयोग</h2>
          <ul class="list-disc list-inside space-y-1">
            <li>समाचार सेवा सुधार गर्न</li>
            <li>न्यूजलेटर र सूचनाहरू पठाउन (सहमतिका साथ मात्र)</li>
            <li>साइट विश्लेषण र तथ्याङ्क</li>
            <li>सुरक्षा र धोखाधडी रोकथाम</li>
          </ul>
        </div>

        <div>
          <h2 class="font-bold text-base mb-2" style="color:var(--c-text)">४. कुकीज</h2>
          <p>हाम्रो साइटले कार्यात्मक कुकीहरू प्रयोग गर्दछ। तपाईं ब्राउजर सेटिङमा कुकी अस्वीकार गर्न सक्नुहुन्छ, तर केही सुविधाहरू उपलब्ध नहुन सक्छन्।</p>
        </div>

        <div>
          <h2 class="font-bold text-base mb-2" style="color:var(--c-text)">५. तेस्रो पक्ष</h2>
          <p>हामी Google Analytics र अन्य सेवा प्रदायकहरू प्रयोग गर्न सक्छौं। यी सेवाहरूको आफ्नै गोपनीयता नीति छ।</p>
        </div>

        <div>
          <h2 class="font-bold text-base mb-2" style="color:var(--c-text)">६. तपाईंका अधिकार</h2>
          <ul class="list-disc list-inside space-y-1">
            <li>आफ्नो जानकारी हेर्ने र सच्याउने अधिकार</li>
            <li>न्यूजलेटरबाट Unsubscribe गर्ने अधिकार</li>
            <li>आफ्नो डेटा मेटाउन अनुरोध गर्ने अधिकार</li>
          </ul>
        </div>

        <div>
          <h2 class="font-bold text-base mb-2" style="color:var(--c-text)">७. सम्पर्क</h2>
          <p>गोपनीयता सम्बन्धी प्रश्नका लागि: <a href="/contact" class="hover:underline" style="color:var(--c-primary)"><?= h(setting('contact_email','')) ?></a></p>
        </div>
      </div>
    </div>
  </div>

  <aside class="space-y-5">
    <div class="sidebar-card">
      <div class="section-heading mb-3 text-sm font-bold">कानुनी</div>
      <div class="space-y-1.5">
        <a href="/privacy" class="footer-link flex items-center gap-2 font-semibold" style="color:var(--c-primary)"><?= icon('shield','w-3.5 h-3.5') ?> गोपनीयता नीति</a>
        <a href="/terms"   class="footer-link flex items-center gap-2"><?= icon('file-text','w-3.5 h-3.5') ?> सेवा शर्तहरू</a>
        <a href="/contact" class="footer-link flex items-center gap-2"><?= icon('mail','w-3.5 h-3.5') ?> सम्पर्क</a>
        <a href="/about"   class="footer-link flex items-center gap-2"><?= icon('info','w-3.5 h-3.5') ?> हाम्रोबारे</a>
      </div>
    </div>
    <?php render_ads('sidebar-top'); ?>
  </aside>
</div>

<?php require SRC_DIR . '/layout/footer.php'; ?>
