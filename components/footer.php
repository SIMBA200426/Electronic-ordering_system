</main> <!-- End of .container main -->

<footer style="background: var(--card); border-top: 1px solid var(--border); padding: 2rem 0; margin-top: auto;">
    <div class="container" style="text-align: center; color: var(--muted); font-size: 0.875rem;">
        <p>&copy; <?= date('Y') ?> ElectroShop. All rights reserved.</p>
    </div>
</footer>

<?php 
// Close the wrapper if it was opened (Admin/Customer mode)
// We detect this same way as header, or just check if the div is likely open.
// Since we don't strictly track state between files, we can check the mode again
// or just close the div if we are in those paths.
$isAdminPath = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false);
$isCustomerPath = (strpos($_SERVER['PHP_SELF'], '/customer/') !== false);
if (($isAdminPath && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') || 
    ($isCustomerPath && isset($_SESSION['role']) && $_SESSION['role'] === 'customer')) {
    echo '</div><!-- .app-wrapper -->';
}
?>

<script src="<?= BASE_URL ?>/assets/js/app.js"></script>
</body>
</html>
