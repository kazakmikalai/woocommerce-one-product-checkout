// bygi: один товар в заказе (qty=1) + мгновенный редирект на Checkout

// 1) Перед добавлением — корзина должна быть пустой
add_filter('woocommerce_add_to_cart_validation', 'bygi_single_item_cart_enforce', 10, 3);
function bygi_single_item_cart_enforce( $passed, $product_id, $quantity ) {
    if ( WC()->cart && ! WC()->cart->is_empty() ) {
        WC()->cart->empty_cart();
    }
    return $passed;
}

// 2) Форсим количество = 1
add_filter('woocommerce_add_to_cart_quantity', 'bygi_force_qty_one', 10, 2);
function bygi_force_qty_one( $qty, $product_id ) {
    return 1;
}

// 3) Запрещаем менять количество
add_filter('woocommerce_is_sold_individually', 'bygi_sold_individually_global', 10, 2);
function bygi_sold_individually_global( $sold_individually, $product ) {
    return true;
}

// 4) Базовый редирект после добавления (не-AJAX сценарии)
add_filter('woocommerce_add_to_cart_redirect', 'bygi_redirect_to_checkout_after_add', 9999);
function bygi_redirect_to_checkout_after_add( $url ) {
    return wc_get_checkout_url();
}

// 5) Подстраховка на single product без AJAX:
//    если был add-to-cart, ошибок нет и корзина не пуста — уводим на Checkout.
add_action('template_redirect', 'bygi_force_checkout_on_single_after_add', 9999);
function bygi_force_checkout_on_single_after_add() {
    if ( is_admin() || is_checkout() || is_cart() ) {
        return;
    }
    if ( isset($_REQUEST['add-to-cart']) ) {
        if ( function_exists('wc_notice_count') && wc_notice_count('error') === 0 && WC()->cart && ! WC()->cart->is_empty() ) {
            wp_safe_redirect( wc_get_checkout_url() );
            exit;
        }
    }
}
