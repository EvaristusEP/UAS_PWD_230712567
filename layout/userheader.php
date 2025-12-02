<header>
    <div class="navbar">
        <div class="navbar-content">
            <h1>Apotek Online</h1>
            <div class="navbar-links">
                <span class="user-info">Hai, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</span>
                <a href="index.php">Beranda</a>
                <a href="cart.php">
                    Keranjang
                    <?php if ($cart_items > 0): ?>
                        <span class="cart-badge"><?php echo $cart_items; ?></span>
                    <?php endif; ?>
                </a>
                <a href="orders.php">Pesanan</a>
                <a href="profile.php">Profil</a>
                <a href="../../logout.php">Logout</a>
            </div>
        </div>
    </div>
</header>