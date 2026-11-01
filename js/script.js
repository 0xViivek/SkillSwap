/**
 * SkillSwap — Client-side JS
 * File: js/script.js
 *
 * Keep this file simple — no frameworks.
 * Only progressive enhancements and UI helpers.
 */

// ── Flash message auto-dismiss ─────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {

    // Auto-hide flash alerts after 4 seconds
    document.querySelectorAll('.alert[data-flash]').forEach(function (el) {
        setTimeout(function () {
            el.style.transition = 'opacity 0.5s';
            el.style.opacity    = '0';
            setTimeout(function () { el.remove(); }, 500);
        }, 4000);
    });

    // ── Skill search filter (skills.php / matches.php) ─────────────
    const searchInput = document.getElementById('skill-search');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const q = this.value.toLowerCase().trim();
            document.querySelectorAll('.skill-row').forEach(function (row) {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(q) ? '' : 'none';
            });
        });
    }

    // ── Student/match search filter ────────────────────────────────
    const matchSearch = document.getElementById('match-search');
    if (matchSearch) {
        matchSearch.addEventListener('input', function () {
            const q = this.value.toLowerCase().trim();
            document.querySelectorAll('.match-card').forEach(function (card) {
                const text = card.textContent.toLowerCase();
                card.style.display = text.includes(q) ? '' : 'none';
            });
        });
    }

    // ── Confirm before dangerous actions ──────────────────────────
    document.querySelectorAll('form:has([data-confirm])').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            const button = e.submitter || this.querySelector('[data-confirm]');
            if (button && button.dataset.confirm && !confirm(button.dataset.confirm)) e.preventDefault();
        });
    });

    // ── Score badge colour fix (for dynamically added badges) ─────
    document.querySelectorAll('.match-score-badge').forEach(function (badge) {
        if (!badge.hasAttribute('data-score')) return;
        const score = parseInt(badge.dataset.score, 10);
        if      (score === 100) badge.classList.add('score-100');
        else if (score >= 50)   badge.classList.add('score-50');
        else                    badge.classList.add('score-0');
    });
});
