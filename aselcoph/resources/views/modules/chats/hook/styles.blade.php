<script src="https://unpkg.com/lucide@latest"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

<style>
    body{font-family:'Inter',sans-serif}
    /* Bubbles with "thinking" tail */
    .bubble{position:relative;max-width:48rem;border-radius:18px;padding:12px 14px;line-height:1.5}
    .bubble.ai{background:#f3f4f6;color:#1f2937;border-top-left-radius:8px}
    .bubble.ai::after{content:"";position:absolute;left:-6px;bottom:10px;width:10px;height:10px;background:#f3f4f6;border-bottom-left-radius:10px;transform:rotate(45deg);box-shadow:-1px 1px 0 rgba(0,0,0,.02)}
    .bubble.me{background:#3b82f6;color:#fff;border-top-right-radius:8px;}
    .bubble.me::after{content:"";position:absolute;right:-4px;bottom:10px;width:10px;height:10px;background:#3b82f6;border-bottom-right-radius:10px;transform:rotate(45deg);}
    .stamp{font-size:11px;color:#9ca3af;margin-top:6px}
    .day-sep{display:grid;grid-template-columns:1fr auto 1fr;align-items:center;gap:12px;color:#6b7280;font-size:12px;margin:20px 0 8px}
    .day-sep .line{height:1px;background:#e5e7eb}
    /* Group stacked avatars */
    .stack{display:flex}
    .stack img{border:2px solid #fff;margin-left:-10px}
    .stack img:first-child{margin-left:0}

    /* shimmer */
    .skeleton {
        position: relative;
        overflow: hidden;
        background: #f3f4f6;
    }
    .skeleton::after {
        content: "";
        position: absolute;
        inset: 0;
        transform: translateX(-100%);
        background: linear-gradient(90deg, transparent, rgba(255,255,255,.55), transparent);
        animation: shimmer 1.1s infinite;
    }
    @keyframes shimmer {
        100% { transform: translateX(100%); }
    }
    /* Lists inside bubbles */
    .bubble ul,
    .bubble ol {
        margin: .4rem 0 .6rem;
        padding-left: 1.25rem;         /* indent bullets/numbers */
    }
    .bubble ul { list-style: disc; }
    .bubble ol { list-style: decimal; }
    .bubble li { margin: .15rem 0; line-height: 1.5; }

    /* Nested lists look cleaner */
    .bubble ul ul { list-style: circle; }
    .bubble ol ol { list-style: lower-alpha; }

     .msg { display:flex; gap:.5rem; }
    .msg.me { flex-direction:row-reverse; }
    .msg .bubble {
        max-width: 70%; padding:.5rem .75rem; border-radius:.75rem;
        background:#fff; border:1px solid #e5e7eb;
    }
    .msg.me .bubble { background:#e6f0ff; border-color:#d6e6ff; }
    .msg .meta { font-size:.70rem; color:#6b7280; margin-top:.15rem; }
    .avatar { width:1.75rem; height:1.75rem; border-radius:9999px; object-fit:cover; }
    @keyframes pulse-glow-light {
        0%   { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.3); }
        70%  { box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); }
        100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
    }

    .pulse-red-light {
        border: 2px solid #ef4444; /* Tailwind red-500 */
        animation: pulse-glow-light 1.5s infinite;
        border-radius: 0.5rem; /* matches rounded-lg */
    }
</style>
