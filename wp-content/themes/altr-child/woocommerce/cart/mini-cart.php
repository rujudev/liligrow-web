<?php
/**
 * Mini-cart
 *
 * Contains the markup for the mini-cart, used by the cart widget.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/mini-cart.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 7.9.0
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_mini_cart'); ?>

<?php if (!WC()->cart->is_empty()): ?>

	<form class="mini-cart-form" method="PUT">
		<ul class="woocommerce-mini-cart cart_list product_list_widget <?php echo esc_attr($args['list_class']); ?>">
			<?php do_action('custom_cart', WC()->cart->get_cart(), Type::MiniCart); ?>
		</ul>

		<p class="woocommerce-mini-cart__total total">
			<?php
			/**
			 * Hook: woocommerce_widget_shopping_cart_total.
			 *
			 * @hooked woocommerce_widget_shopping_cart_subtotal - 10
			 */
			do_action('woocommerce_widget_shopping_cart_total');
			?>
		</p>

		<?php do_action('woocommerce_widget_shopping_cart_before_buttons'); ?>

		<p class="woocommerce-mini-cart__buttons buttons">
			<?php do_action('woocommerce_widget_shopping_cart_buttons'); ?>
		</p>

		<?php do_action('woocommerce_widget_shopping_cart_after_buttons'); ?>
		<?php $nonce = wp_create_nonce('wc_store_api'); ?>

		<script id="custom-mini-cart-script">
			//document.querySelectorAll(".woocommerce-mini-cart-item").forEach((e=>{let t=e.querySelector("button.decrement"),r=e.querySelector("button.increment"),a=e.querySelector("a.remove"),n=e.querySelector(".qty-container"),o=n.querySelector(".qty-result"),c=e.querySelector("input[type=number]"),i=(e.querySelector(".quantity-container"),0),s=0;const l=c.getAttribute("name").match(/\[([^\]]+)\]/g).map((e=>e.slice(1,-1)))[0],u=document.querySelector(".header-right"),m=u.querySelector(".site-header-cart");let p=document.createElement("span");function d(){const e=parseInt(c.getAttribute("value")),t=parseInt(c.getAttribute("max"));Boolean(t)&&(e>=t?(p&&p.remove(),p=document.createElement("span"),p.classList.add("stock-limit"),p.textContent="<?php esc_html_e('Stock limit reached', 'woocommerce'); ?>",n.appendChild(p),r.setAttribute("disabled","disabled"),r.classList.add("disabled")):e<t&&(p&&p.remove(),r.removeAttribute("disabled"),r.classList.remove("disabled")))}async function y(){const e=c.getAttribute("value");{const t=await fetch(`<?php echo esc_url(get_home_url()); ?>/wp-json/wc/store/v1/cart/update-item?key=${l}&quantity=${e}`,{method:"POST",headers:{"Content-Type":"application/json",Nonce:"<?php echo esc_attr($nonce); ?>"}});if(200===t.status&&t.ok){jQuery(m).removeClass("loading").unblock();const t=u.querySelector(".mini-cart-contents .count"),r=u.querySelector(".mini-cart-contents .amount-cart bdi"),a=u.querySelector(".top-header-cart #mini-cart-products-qty"),n=u.querySelector(".woocommerce-mini-cart__total.total .woocommerce-Price-amount.amount bdi");let l=c.getAttribute("data-product_price").split(".");l[1]=l[1].slice(0,2);const p=parseFloat(parseFloat(l.join(".")).toFixed(2))*parseInt(e),y=o.querySelector(".qty-price .woocommerce-Price-amount.amount bdi"),b=parseFloat(p).toFixed(2).replace(".",",").split(","),v=`<span class="integer">${b[0]}</span>`,g=`<span class="decimal">${b[1].substring(0,2)}</span>`;y.innerHTML=`${v},${g}€`;const q=Array.from(u.querySelectorAll(".woocommerce-mini-cart-item")).map((e=>e.querySelector(".qty-price .woocommerce-Price-amount.amount bdi").textContent.split("€").join("").replace(",",".")),0);i=q.reduce(((e,t)=>parseFloat(t)+e),0);const S=Array.from(u.querySelectorAll("input[type=number]")).map((e=>e.value));s=S.reduce(((e,t)=>parseInt(t)+e),0),console.log({cartQtyItems:S,totalCartItemsCount:s});const f=parseFloat(i).toFixed(2).replace(".",",").split(","),h=f[0],A=`<span class="integer">${h}</span>`,x=f[1].substring(0,2),$=`<span class="decimal">${x}</span>`;t.innerText=s,r.innerText=`${h},${x}€`,a.innerText=s,n.innerHTML=`${A},${$}€`,d()}}}t.addEventListener("click",(e=>{e.preventDefault();const t=parseInt(c.getAttribute("value"));t>1&&(jQuery(m).addClass("loading").block({message:null,overlayCSS:{background:"#fff",opacity:.6}}),c.setAttribute("value",t-1),c.value=t-1,y())})),r.addEventListener("click",(e=>{e.preventDefault();const t=parseInt(c.getAttribute("value"));jQuery(m).addClass("loading").block({message:null,overlayCSS:{background:"#fff",opacity:.6}}),c.setAttribute("value",t+1),c.value=t+1,y()})),a.addEventListener("click",(function(t){t.preventDefault();const r=u.querySelector(".widget_shopping_cart"),a=new MutationObserver((t=>{for(const r of t){r.target;const t=document.querySelector(".mini-cart-contents .count"),n=parseInt(t.innerText),o=document.querySelector(".mini-cart-contents .amount-cart bdi"),i=(parseFloat(o.innerText.split("€")[0].replace(",",".")),e.querySelector(".amount bdi")),s=(parseFloat(i.innerText.split("€")[0].replace(",",".")),u.querySelector(".top-header-cart #mini-cart-products-qty")),l=parseInt(s.innerText),m=parseInt(c.getAttribute("value")),p=u.querySelector(".woocommerce-mini-cart__total.total .woocommerce-Price-amount.amount bdi"),d=(p?.textContent??"0,00€").replace(".",",").split(","),y=`<span class="integer">${d[0]}</span>`,b=`<span class="decimal">${d[1].substring(0,2)}</span>`;t.innerText=n-m,o.innerHTML=`${y},${b}€`,s.innerText=l-m,a.disconnect()}}));a.observe(r,{childList:!0})})),c.addEventListener("keydown",(function(e){const t=e.keyCode,r=parseInt(e.target.value),a=parseInt(this.getAttribute("max"));if(13===t){if(e.preventDefault(),NaN===r)return;jQuery(m).addClass("loading").block({message:null,overlayCSS:{background:"#fff",opacity:.6}}),r>a?(this.setAttribute("value",a),this.value=a):(this.setAttribute("value",r),this.value=r),y()}})),d()}));
			document.querySelectorAll(".woocommerce-mini-cart-item").forEach((e=>{let t=e.querySelector("button.decrement"),r=e.querySelector("button.increment"),n=e.querySelector(".remove"),a=e.querySelector(".qty-container"),o=a.querySelector(".qty-result"),c=e.querySelector("input[type=number]"),i=(e.querySelector(".quantity-container"),0),s=0;const l=c.getAttribute("name").match(/\[([^\]]+)\]/g).map((e=>e.slice(1,-1)))[0],u=document.querySelector(".header-right"),m=u.querySelector(".site-header-cart");let p=document.createElement("span");function d(){const e=parseInt(c.getAttribute("value")),t=parseInt(c.getAttribute("max"));Boolean(t)&&(e>=t?(p&&p.remove(),p=document.createElement("span"),p.classList.add("stock-limit"),p.textContent="<?php esc_html_e('Stock limit reached', 'woocommerce'); ?>",a.appendChild(p),r.setAttribute("disabled","disabled"),r.classList.add("disabled")):e<t&&(p&&p.remove(),r.removeAttribute("disabled"),r.classList.remove("disabled")))}async function y(){const e=c.getAttribute("value");{const t=await fetch(`<?php echo esc_url(get_home_url()); ?>/wp-json/wc/store/v1/cart/update-item?key=${l}&quantity=${e}`,{method:"POST",headers:{"Content-Type":"application/json",Nonce:"<?php echo esc_attr($nonce); ?>"}});if(200===t.status&&t.ok){jQuery(m).removeClass("loading").unblock();const t=u.querySelector(".mini-cart-contents .count"),r=u.querySelector(".mini-cart-contents .amount-cart bdi"),n=u.querySelector(".top-header-cart #mini-cart-products-qty"),a=u.querySelector(".woocommerce-mini-cart__total.total .woocommerce-Price-amount.amount bdi");let l=c.getAttribute("data-product_price").split(".");l[1]=l[1].slice(0,2);const p=parseFloat(parseFloat(l.join(".")).toFixed(2))*parseInt(e),y=o.querySelector(".qty-price .woocommerce-Price-amount.amount bdi"),b=parseFloat(p).toFixed(2).replace(".",",").split(","),v=`<span class="integer">${b[0]}</span>`,g=`<span class="decimal">${b[1].substring(0,2)}</span>`;y.innerHTML=`${v},${g}€`;const q=Array.from(u.querySelectorAll(".woocommerce-mini-cart-item")).map((e=>e.querySelector(".qty-price .woocommerce-Price-amount.amount bdi").textContent.split("€").join("").replace(",",".")),0);i=q.reduce(((e,t)=>parseFloat(t)+e),0);const S=Array.from(u.querySelectorAll("input[type=number]")).map((e=>e.value));s=S.reduce(((e,t)=>parseInt(t)+e),0);const f=parseFloat(i).toFixed(2).replace(".",",").split(","),h=f[0],A=`<span class="integer">${h}</span>`,$=f[1].substring(0,2),k=`<span class="decimal">${$}</span>`;t.innerText=s,r.innerText=`${h},${$}€`,n.innerText=s,a.innerHTML=`${A},${k}€`,d()}}}t.addEventListener("click",(e=>{e.preventDefault();const t=parseInt(c.getAttribute("value"));t>1&&(jQuery(m).addClass("loading").block({message:null,overlayCSS:{background:"#fff",opacity:.6}}),c.setAttribute("value",t-1),c.value=t-1,y())})),r.addEventListener("click",(e=>{e.preventDefault();const t=parseInt(c.getAttribute("value"));jQuery(m).addClass("loading").block({message:null,overlayCSS:{background:"#fff",opacity:.6}}),c.setAttribute("value",t+1),c.value=t+1,y()})),n.addEventListener("click",(function(e){e.preventDefault();const t=u.querySelector(".widget_shopping_cart"),r=new MutationObserver((e=>{for(const t of e){t.target;const e=document.querySelector(".mini-cart-contents .count"),n=parseInt(e.innerText),a=document.querySelector(".mini-cart-contents .amount-cart bdi"),o=u.querySelector(".top-header-cart #mini-cart-products-qty"),i=parseInt(o.innerText),s=parseInt(c.getAttribute("value")),l=u.querySelector(".woocommerce-mini-cart__total.total .woocommerce-Price-amount.amount bdi"),m=(l?.textContent??"0,00€").replace(".",",").split(","),p=`<span class="integer">${m[0]}</span>`,d=`<span class="decimal">${m[1].substring(0,2)}</span>`;e.innerText=n-s,a.innerHTML=`${p},${d}€`,o.innerText=i-s,r.disconnect()}}));r.observe(t,{childList:!0})})),c.addEventListener("keydown",(function(e){const t=e.keyCode,r=parseInt(e.target.value),n=parseInt(this.getAttribute("max"));if(13===t){if(e.preventDefault(),NaN===r)return;jQuery(m).addClass("loading").block({message:null,overlayCSS:{background:"#fff",opacity:.6}}),r>n?(this.setAttribute("value",n),this.value=n):(this.setAttribute("value",r),this.value=r),y()}})),d()}));
		</script>
	</form>

<?php else: ?>

	<p class="woocommerce-mini-cart__empty-message">
		<?php esc_html_e('No products in the cart.', 'woocommerce'); ?>
	</p>

<?php endif; ?>

<?php do_action('woocommerce_after_mini_cart'); ?>