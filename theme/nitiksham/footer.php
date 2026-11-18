<?php /** @package Nitiksham */ ?>
<footer class="site-footer">
    <div class="shell footer-main">
        <div class="footer-brand-block">
            <span class="footer-wordmark">NITIKSHAM<span>.</span></span>
            <p><?php esc_html_e( 'Thoughtful strategy. Distinctive design. Meaningful growth.', 'nitiksham' ); ?></p>
        </div>
        <div class="footer-meta">
            <span class="footer-small-label"><?php esc_html_e( 'What we do', 'nitiksham' ); ?></span>
            <p><?php esc_html_e( 'Brand Strategy · Digital Marketing · Creative Design · Traditional Marketing', 'nitiksham' ); ?></p>
        </div>
    </div>
    <div class="shell footer-bottom">
        <small>© <?php echo esc_html( date_i18n( 'Y' ) ); ?> <?php esc_html_e( 'Nitiksham Marketing. All rights reserved.', 'nitiksham' ); ?></small>
        <a href="#site-header"><?php esc_html_e( 'Back to top', 'nitiksham' ); ?> ↑</a>
    </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
