<div id="scrollToBottomBtn" class="mobile-response" style="position: fixed; bottom: 480px; right: 16px; z-index: 40; transition: all 0.3s ease; opacity: 1; pointer-events: auto;">
    <div style="position: relative;">
        <svg style="position: absolute; top: 0; left: 0; width: 48px; height: 48px; transform: rotate(-90deg);" viewBox="0 0 48 48">
            <circle 
                cx="24" 
                cy="24" 
                r="20" 
                stroke="#e5e7eb" 
                stroke-width="3" 
                fill="none"
            />
            <circle 
                id="progressCircle"
                cx="24" 
                cy="24" 
                r="20" 
                stroke="#8b5cf6" 
                stroke-width="3" 
                fill="none"
                stroke-dasharray="125.66"
                stroke-dashoffset="125.66"
                stroke-linecap="round"
                class="transition-all duration-300 ease-out"
            />
        </svg>
        
        <button 
            onclick="scrollToBottom()" 
            style="position: relative; width: 48px; height: 48px; background: white; border-radius: 50%; box-shadow: 0 10px 25px rgba(0,0,0,0.15); border: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: center; transition: all 0.2s ease; cursor: pointer;"
            onmouseover="this.style.transform='scale(1.1)'; this.style.boxShadow='0 15px 35px rgba(0,0,0,0.2)';"
            onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='0 10px 25px rgba(0,0,0,0.15)';"
        >
            <div id="scrollArrow" class="transition-transform duration-300 ease-in-out">
                <svg width="24" height="24" viewBox="0 0 47 47" fill="none">
                    <path d="M11.8017 5.88911C10.951 6.46251 10.0815 5.16531 10.9322 4.59191C14.6922 2.07271 18.9739 0.784912 23.5 0.784912C36.0443 0.784912 46.2151 10.9557 46.2151 23.5C46.2151 36.0443 36.0443 46.2151 23.5 46.2151C10.9557 46.2151 0.784912 36.0443 0.784912 23.5C0.784912 18.9786 2.13381 14.5465 4.66241 10.8006C5.24051 9.94991 6.53771 10.8241 5.95961 11.6748C3.59081 15.1857 2.35001 19.2653 2.35001 23.5C2.35001 35.1795 11.8205 44.65 23.5 44.65C35.1795 44.65 44.65 35.1795 44.65 23.5C44.65 11.8205 35.1795 2.35001 23.5 2.35001C19.2935 2.35001 15.2985 3.54851 11.8017 5.88911ZM23.5 32.3652C23.2844 32.3652 23.0762 32.2792 22.9207 32.1237L16.2707 25.4737C15.9513 25.1543 15.9513 24.6365 16.2707 24.3171C16.5901 23.9977 17.1079 23.9977 17.4273 24.3171L22.7348 29.6246V15.4C22.7348 14.9636 23.0636 14.6348 23.5 14.6348C23.9364 14.6348 24.2652 14.9636 24.2652 15.4V29.6246L29.5727 24.3171C29.8921 23.9977 30.4099 23.9977 30.7293 24.3171C31.0487 24.6365 31.0487 25.1543 30.7293 25.4737L24.0793 32.1237C23.9238 32.2792 23.7156 32.3652 23.5 32.3652Z" fill="#8b5cf6"/>
                </svg>
            </div>
        </button>
    </div>
</div>

<script>
let isScrolling = false;
let scrollProgress = 0;


(function() {
    let scrollComponentInitialized = false;
    
    function initScrollComponent() {
        if (scrollComponentInitialized) return;
        scrollComponentInitialized = true;
        updateScrollButton();
        window.addEventListener('scroll', throttle(updateScrollButton, 16), { passive: true });
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initScrollComponent);
    } else {
        initScrollComponent();
    }
})();

function updateScrollButton() {
    const scrollBtn = document.getElementById('scrollToBottomBtn');
    const progressCircle = document.getElementById('progressCircle');
    const scrollArrow = document.getElementById('scrollArrow');
    
    if (!scrollBtn || !progressCircle || !scrollArrow) return;

    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    const documentHeight = document.documentElement.scrollHeight - window.innerHeight;
    scrollProgress = Math.min(scrollTop / documentHeight, 1);

    // Button is always visible, no need to show/hide based on scroll position

    const circumference = 125.66; 
    const offset = circumference - (scrollProgress * circumference);
    progressCircle.style.strokeDashoffset = offset;
    

    if (scrollProgress > 0.8) {
        scrollArrow.style.transform = 'rotate(180deg)';
        scrollArrow.onclick = scrollToTop;
    } else {
        scrollArrow.style.transform = 'rotate(0deg)';
        scrollArrow.onclick = scrollToBottom;
    }
}

function scrollToBottom() {
    if (isScrolling) return;
    
    isScrolling = true;
    const start = window.pageYOffset;
    const target = document.documentElement.scrollHeight - window.innerHeight;
    const distance = target - start;
    const duration = Math.min(1500, Math.max(800, Math.abs(distance) / 2)); 
    
    animateScroll(start, target, duration, () => {
        isScrolling = false;
    });
}

function scrollToTop() {
    if (isScrolling) return;
    
    isScrolling = true;
    const start = window.pageYOffset;
    const target = 0;
    const distance = start - target;
    const duration = Math.min(1500, Math.max(800, Math.abs(distance) / 2)); 
    
    animateScroll(start, target, duration, () => {
        isScrolling = false;
    });
}

function animateScroll(start, target, duration, callback) {
    const startTime = performance.now();
    
    function scroll(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);

        const easeProgress = progress < 0.5 
            ? 4 * progress * progress * progress 
            : 1 - Math.pow(-2 * progress + 2, 3) / 2;
        
        const currentPosition = start + (target - start) * easeProgress;
        window.scrollTo(0, currentPosition);
        
        if (progress < 1) {
            requestAnimationFrame(scroll);
        } else {
            callback && callback();
        }
    }
    
    requestAnimationFrame(scroll);
}

function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    }
}
</script>
