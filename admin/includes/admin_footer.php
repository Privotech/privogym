    </div> <!-- admin-content -->
</div> <!-- admin-main -->

<script>
// Sidebar toggle for smaller screens
const sidebarBtn = document.getElementById('sidebarToggle');
const sidebar = document.getElementById('adminSidebar');
if (sidebarBtn && sidebar) {
    sidebarBtn.addEventListener('click', () => {
        sidebar.classList.toggle('show');
    });
}
</script>
<script src="<?= asset('js/main.js') ?>"></script>
</body>
</html>
