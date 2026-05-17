// Shared plan badge helpers
(function(){
    function normalizeHex(hex){
        if (!hex || typeof hex !== 'string') return null;
        let clean = hex.replace('#','');
        if (clean.length === 3) {
            clean = clean.split('').map(c => c + c).join('');
        }
        if (clean.length !== 6) return null;
        return clean;
    }

    function hexToRgba(hex, alpha){
        const clean = normalizeHex(hex);
        if (!clean) return `rgba(229, 231, 235, ${alpha})`;
        const r = parseInt(clean.slice(0,2), 16);
        const g = parseInt(clean.slice(2,4), 16);
        const b = parseInt(clean.slice(4,6), 16);
        return `rgba(${r}, ${g}, ${b}, ${alpha})`;
    }

    function darkenColor(hex, amount){
        const clean = normalizeHex(hex);
        if (!clean) return '#1f2937';
        const r = parseInt(clean.slice(0,2), 16);
        const g = parseInt(clean.slice(2,4), 16);
        const b = parseInt(clean.slice(4,6), 16);
        const factor = 1 - Math.min(Math.max(amount, 0), 0.8);
        const dr = Math.max(0, Math.floor(r * factor));
        const dg = Math.max(0, Math.floor(g * factor));
        const db = Math.max(0, Math.floor(b * factor));
        return `rgb(${dr}, ${dg}, ${db})`;
    }

    function badgeHtml({label, color, icon, className} = {}){
        const accent = color || '#f59e0b';
        const text = darkenColor(accent, 0.45);
        const bg = hexToRgba(accent, 0.15);
        const iconHtml = icon ? `<span class="lif-plan-icon">${icon}</span>` : '';
        return `<span class="lif-badge ${className || ''}" style="background:${bg}; color:${text}">${iconHtml}${label || '-'}</span>`;
    }

    window.LifPlanBadge = {
        hexToRgba,
        darkenColor,
        badgeHtml,
    };
})();
