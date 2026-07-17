
// Lazy Load Images
document.addEventListener('DOMContentLoaded', function() {
  const lazyImages = document.querySelectorAll('img.lazy');
  
  if ('IntersectionObserver' in window) {
    const imageObserver = new IntersectionObserver(function(entries, observer) {
      entries.forEach(function(entry) {
        if (entry.isIntersecting) {
          const img = entry.target;
          img.classList.add('loaded');
          observer.unobserve(img);
        }
      });
    }, { threshold: 0.1 });
    
    lazyImages.forEach(function(img) {
      imageObserver.observe(img);
    });
  } else {
    // Fallback for older browsers
    lazyImages.forEach(function(img) {
      img.classList.add('loaded');
    });
  }
});

// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
  anchor.addEventListener('click', function(e) {
    const targetId = this.getAttribute('href');
    if (targetId === '#') return;
    const target = document.querySelector(targetId);
    if (target) {
      e.preventDefault();
      target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  });
});

// Social Share Functions
function shareOnFacebook(url) {
  window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(url), '_blank', 'width=600,height=400');
}

function shareOnTwitter(url, title) {
  window.open('https://twitter.com/intent/tweet?url=' + encodeURIComponent(url) + '&text=' + encodeURIComponent(title), '_blank', 'width=600,height=400');
}

function shareOnWhatsApp(url, title) {
  window.open('https://wa.me/?text=' + encodeURIComponent(title + ' ' + url), '_blank');
}

function shareOnLinkedIn(url) {
  window.open('https://www.linkedin.com/shareArticle?mini=true&url=' + encodeURIComponent(url), '_blank', 'width=600,height=400');
}

function copyLink(url) {
  if (navigator.clipboard) {
    navigator.clipboard.writeText(url).then(function() {
      showToast('लिंक कपी गरियो!', 'success');
    });
  } else {
    // Fallback
    var textArea = document.createElement('textarea');
    textArea.value = url;
    document.body.appendChild(textArea);
    textArea.select();
    document.execCommand('copy');
    document.body.removeChild(textArea);
    showToast('लिंक कपी गरियो!', 'success');
  }
}

// Toast notification
function showToast(message, type) {
  var toast = document.createElement('div');
  toast.className = 'toast toast-' + type;
  toast.textContent = message;
  toast.style.cssText = 'position:fixed;bottom:100px;left:50%;transform:translateX(-50%);padding:12px 24px;background:var(--c-text);color:#fff;border-radius:var(--r-lg);font-size:14px;font-weight:500;z-index:9999;animation:fadeIn 0.3s ease';
  document.body.appendChild(toast);
  setTimeout(function() {
    toast.style.animation = 'fadeOut 0.3s ease forwards';
    setTimeout(function() { toast.remove(); }, 300);
  }, 2500);
}

// Add fadeOut animation
var style = document.createElement('style');
style.textContent = '@keyframes fadeOut { to { opacity: 0; transform: translateX(-50%) translateY(-10px); } }';
document.head.appendChild(style);
