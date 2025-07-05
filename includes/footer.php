            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/app.js"></script>
    <?php if(isset($_GET['success'])): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: '<?php echo htmlspecialchars($_GET['success']); ?>',
            timer: 3000,
            showConfirmButton: false
        });
    </script>
    <?php endif; ?>
    
    <?php if(isset($_GET['error'])): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: '<?php echo htmlspecialchars($_GET['error']); ?>'
        });
    </script>
    <?php endif; ?>
</body>
</html>
