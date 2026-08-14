<footer class="footer">


<div class="container text-center">

<p>
© 2026 EWU Innovation Hub | Developed by The Problematic Four
</p>


</div>


</footer>


<!-- Bootstrap JS -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


<script src="assets/js/script.js"></script>


</body>

</html>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const navLinks = document.getElementById('navLinks');

    if (mobileMenuToggle && navLinks) {
        mobileMenuToggle.addEventListener('click', function () {
            navLinks.classList.toggle('show');
        });
    }
});
</script>