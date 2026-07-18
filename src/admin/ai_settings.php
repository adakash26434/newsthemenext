<?php
admin_check();

$form_title = 'AI Settings';
$ai_enabled = setting('ai_enabled', '0') === '1';
$ai_provider = setting('ai_provider', 'openai');
$ai_model = setting('ai_model', 'gpt-4o-mini');
$openai_key = setting('openai_key', '');
$gemini_key = setting('gemini_key', '');
$deepseek_key = setting('deepseek_key', '');
$ai_temperature = (float) setting('ai_temperature', '0.7');
$ai_max_tokens = (int) setting('ai_max_tokens', '1000');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    
    $new_settings = [
        'ai_enabled' => isset($_POST['ai_enabled']) ? '1' : '0',
        'ai_provider' => $_POST['ai_provider'] ?? 'openai',
        'ai_model' => trim($_POST['ai_model'] ?? ''),
        'openai_key' => trim($_POST['openai_key'] ?? ''),
        'gemini_key' => trim($_POST['gemini_key'] ?? ''),
        'deepseek_key' => trim($_POST['deepseek_key'] ?? ''),
        'ai_temperature' => (float) ($_POST['ai_temperature'] ?? 0.7),
        'ai_max_tokens' => (int) ($_POST['ai_max_tokens'] ?? 1000),
    ];
    
    foreach ($new_settings as $key => $val) {
        save_setting($key, $val);
    }
    
    flash_set('success', 'AI settings saved successfully.');
    redirect('admin/ai_settings');
}

admin_html_start($form_title);
admin_sidebar('ai_settings');
?>
<div class="admin-content">
<?php admin_topbar($form_title); ?>
<div class="p-6">
<?php admin_flash(); ?>

<form method="POST" class="max-w-2xl space-y-6">
  <?= csrf_field() ?>
  
  <!-- Enable/Disable -->
  <div class="stat-card">
    <h3 class="font-bold text-sm mb-4 flex items-center gap-2">
      <?= icon('bot', 'icon-sm') ?> AI Chatbot
    </h3>
    
    <label class="flex items-center gap-3 cursor-pointer">
      <input type="checkbox" name="ai_enabled" <?= $ai_enabled ? 'checked' : '' ?> 
             class="rounded w-5 h-5" value="1">
      <span class="font-medium">Enable AI Chat Widget</span>
    </label>
    <p class="text-xs mt-2" style="color:var(--c-muted)">
      When enabled, a chat icon will appear on the public website allowing users to interact with AI.
    </p>
  </div>
  
  <!-- Provider Selection -->
  <div class="stat-card">
    <h3 class="font-bold text-sm mb-4 flex items-center gap-2">
      <?= icon('cpu', 'icon-sm') ?> AI Provider
    </h3>
    
    <div class="space-y-3">
      <label class="flex items-center gap-3 cursor-pointer p-3 rounded" 
             style="background:<?= $ai_provider === 'openai' ? 'var(--c-surface2)' : 'transparent' ?>">
        <input type="radio" name="ai_provider" value="openai" <?= $ai_provider === 'openai' ? 'checked' : '' ?>
               class="w-4 h-4">
        <div>
          <span class="font-medium">OpenAI (ChatGPT)</span>
          <p class="text-xs" style="color:var(--c-muted)">Uses GPT-4o, GPT-4o-mini models</p>
        </div>
      </label>
      
      <label class="flex items-center gap-3 cursor-pointer p-3 rounded"
             style="background:<?= $ai_provider === 'gemini' ? 'var(--c-surface2)' : 'transparent' ?>">
        <input type="radio" name="ai_provider" value="gemini" <?= $ai_provider === 'gemini' ? 'checked' : '' ?>
               class="w-4 h-4">
        <div>
          <span class="font-medium">Google Gemini</span>
          <p class="text-xs" style="color:var(--c-muted)">Uses Gemini Pro, Gemini Flash models</p>
        </div>
      </label>
      
      <label class="flex items-center gap-3 cursor-pointer p-3 rounded"
             style="background:<?= $ai_provider === 'deepseek' ? 'var(--c-surface2)' : 'transparent' ?>">
        <input type="radio" name="ai_provider" value="deepseek" <?= $ai_provider === 'deepseek' ? 'checked' : '' ?>
               class="w-4 h-4">
        <div>
          <span class="font-medium">DeepSeek</span>
          <p class="text-xs" style="color:var(--c-muted)">Cost-effective Chinese AI model</p>
        </div>
      </label>
    </div>
  </div>
  
  <!-- API Keys -->
  <div class="stat-card">
    <h3 class="font-bold text-sm mb-4 flex items-center gap-2">
      <?= icon('key', 'icon-sm') ?> API Keys
    </h3>
    
    <div class="space-y-4">
      <div class="form-group">
        <label class="form-label">OpenAI API Key</label>
        <input type="password" name="openai_key" class="form-control" 
               placeholder="sk-..." value="<?= h($openai_key) ?>">
        <p class="form-hint">Get key from <a href="https://platform.openai.com/api-keys" target="_blank" class="underline">platform.openai.com</a></p>
      </div>
      
      <div class="form-group">
        <label class="form-label">Google Gemini API Key</label>
        <input type="password" name="gemini_key" class="form-control" 
               placeholder="AI..." value="<?= h($gemini_key) ?>">
        <p class="form-hint">Get key from <a href="https://aistudio.google.com/app/apikey" target="_blank" class="underline">Google AI Studio</a></p>
      </div>
      
      <div class="form-group">
        <label class="form-label">DeepSeek API Key</label>
        <input type="password" name="deepseek_key" class="form-control" 
               placeholder="sk-..." value="<?= h($deepseek_key) ?>">
        <p class="form-hint">Get key from <a href="https://platform.deepseek.com/api_keys" target="_blank" class="underline">platform.deepseek.com</a></p>
      </div>
    </div>
  </div>
  
  <!-- Model Settings -->
  <div class="stat-card">
    <h3 class="font-bold text-sm mb-4 flex items-center gap-2">
      <?= icon('settings', 'icon-sm') ?> Model Settings
    </h3>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div class="form-group">
        <label class="form-label">Default Model</label>
        <select name="ai_model" class="form-control">
          <optgroup label="OpenAI">
            <option value="gpt-4o" <?= $ai_model === 'gpt-4o' ? 'selected' : '' ?>>GPT-4o</option>
            <option value="gpt-4o-mini" <?= $ai_model === 'gpt-4o-mini' ? 'selected' : '' ?>>GPT-4o Mini</option>
            <option value="gpt-4-turbo" <?= $ai_model === 'gpt-4-turbo' ? 'selected' : '' ?>>GPT-4 Turbo</option>
          </optgroup>
          <optgroup label="Google Gemini">
            <option value="gemini-2.0-flash" <?= $ai_model === 'gemini-2.0-flash' ? 'selected' : '' ?>>Gemini 2.0 Flash</option>
            <option value="gemini-1.5-pro" <?= $ai_model === 'gemini-1.5-pro' ? 'selected' : '' ?>>Gemini 1.5 Pro</option>
            <option value="gemini-1.5-flash" <?= $ai_model === 'gemini-1.5-flash' ? 'selected' : '' ?>>Gemini 1.5 Flash</option>
          </optgroup>
          <optgroup label="DeepSeek">
            <option value="deepseek-chat" <?= $ai_model === 'deepseek-chat' ? 'selected' : '' ?>>DeepSeek Chat</option>
            <option value="deepseek-coder" <?= $ai_model === 'deepseek-coder' ? 'selected' : '' ?>>DeepSeek Coder</option>
          </optgroup>
        </select>
      </div>
      
      <div class="form-group">
        <label class="form-label">Temperature (<?= $ai_temperature ?>)</label>
        <input type="range" name="ai_temperature" min="0" max="2" step="0.1" 
               value="<?= $ai_temperature ?>" class="w-full">
        <p class="form-hint">Lower = more focused, Higher = more creative</p>
      </div>
      
      <div class="form-group">
        <label class="form-label">Max Tokens</label>
        <input type="number" name="ai_max_tokens" class="form-control" 
               value="<?= $ai_max_tokens ?>" min="100" max="8000">
        <p class="form-hint">Maximum response length</p>
      </div>
    </div>
  </div>
  
  <!-- Submit -->
  <div class="flex gap-3">
    <button type="submit" class="btn btn-primary">
      <?= icon('save', 'w-4 h-4') ?> Save Settings
    </button>
    <a href="/admin/settings" class="btn btn-secondary">Cancel</a>
  </div>
</form>

</div>
</div>
</body></html>
