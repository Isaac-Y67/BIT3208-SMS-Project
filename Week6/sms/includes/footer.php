</div><!-- end .sms-content -->
</main><!-- end .sms-main -->

</div><!-- end .sms-wrapper -->

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Bootstrap Icons -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
// ================================================
// Global JavaScript — runs on every page
// ================================================

// ── Sidebar toggle for mobile ──
const sidebarToggle  = document.getElementById('sidebarToggle');
const sidebar        = document.getElementById('sidebar');
const sidebarOverlay = document.getElementById('sidebarOverlay');

if (sidebarToggle) {
    sidebarToggle.addEventListener('click', function() {
        sidebar.classList.toggle('open');
        sidebarOverlay.classList.toggle('show');
    });
}

// Close sidebar when overlay is clicked
if (sidebarOverlay) {
    sidebarOverlay.addEventListener('click', function() {
        sidebar.classList.remove('open');
        sidebarOverlay.classList.remove('show');
    });
}

// ── Auto dismiss flash messages after 4 seconds ──
const flashMessage = document.getElementById('flashMessage');
if (flashMessage) {
    setTimeout(function() {
        flashMessage.style.transition = 'opacity 0.5s';
        flashMessage.style.opacity   = '0';
        setTimeout(function() {
            flashMessage.remove();
        }, 500);
    }, 4000);
}

// ── Confirm before delete or deactivate ──
document.addEventListener('click', function(e) {
    // Delete confirmation
    if (e.target.closest('.confirm-delete')) {
        if (!confirm('Are you sure you want to delete this record? This cannot be undone.')) {
            e.preventDefault();
        }
    }
    // Deactivate confirmation
    if (e.target.closest('.confirm-deactivate')) {
        if (!confirm('Are you sure you want to deactivate this account?')) {
            e.preventDefault();
        }
    }
});

// ── Live search on tables ──
// Add id="liveSearch" to any search input
// Add class="searchable-row" to each table row
const liveSearch = document.getElementById('liveSearch');
if (liveSearch) {
    liveSearch.addEventListener('input', function() {
        const query = this.value.toLowerCase();
        const rows  = document.querySelectorAll('.searchable-row');
        rows.forEach(function(row) {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(query) ? '' : 'none';
        });
    });
}
</script>

<?php
// Extra scripts passed from individual pages
// Usage: $extraScripts = "<script>...</script>";
if (isset($extraScripts)) {
    echo $extraScripts;
}
?>

</body>
</html>