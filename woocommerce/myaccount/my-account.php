<?php
/**
 * My Account page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/my-account.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * My Account navigation.
 *
 * @since 2.6.0
 */
do_action( 'woocommerce_account_navigation' ); ?>

<div class="woocommerce-MyAccount-content">
	<?php
		/**
		 * My Account content.
		 *
		 * @since 2.6.0
		 */
		do_action( 'woocommerce_account_content' );
	?>
</div>


<style>
	/* Личный кабинет WooCommerce */
.gl-entry .woocommerce {
	display: grid;
	grid-template-columns: 280px minmax(0, 1fr);
	gap: 24px;
	align-items: flex-start;
}

/* Левая колонка */
.woocommerce-MyAccount-navigation {
	background: #fff;
	border: 1px solid #e6e8ec;
	border-radius: 18px;
	padding: 10px;
	box-shadow: 0 8px 28px rgba(15, 23, 42, .06);
}

.woocommerce-MyAccount-navigation ul {
	list-style: none;
	margin: 0;
	padding: 0;
	display: flex;
	flex-direction: column;
	gap: 6px;
}

.woocommerce-MyAccount-navigation li {
	margin: 0;
	padding: 0;
}

.woocommerce-MyAccount-navigation a {
	display: flex;
	align-items: center;
	min-height: 46px;
	padding: 12px 16px;
	border-radius: 12px;
	color: #1f2933;
	font-size: 15px;
	font-weight: 500;
	text-decoration: none;
	transition: .2s ease;
}

.woocommerce-MyAccount-navigation a:hover {
	background: #f4f6f8;
	color: #111827;
}

.woocommerce-MyAccount-navigation .is-active a {
	background: #1f2933;
	color: #fff;
}

/* Правая колонка */
.woocommerce-MyAccount-content {
	background: #fff;
	border: 1px solid #e6e8ec;
	border-radius: 18px;
	padding: 28px;
	box-shadow: 0 8px 28px rgba(15, 23, 42, .06);
}

.woocommerce-MyAccount-content p {
	margin: 0 0 16px;
	color: #4b5563;
	font-size: 16px;
	line-height: 1.6;
}

.woocommerce-MyAccount-content p:last-child {
	margin-bottom: 0;
}

.woocommerce-MyAccount-content a {
	color: #1f2933;
	font-weight: 600;
	text-decoration: underline;
	text-underline-offset: 3px;
}

/* Поля как на checkout */
.woocommerce-MyAccount-content form .form-row {
	margin-bottom: 18px;
}

.woocommerce-MyAccount-content label {
	display: block;
	margin-bottom: 8px;
	color: #1f2933;
	font-size: 14px;
	font-weight: 600;
}

.woocommerce-MyAccount-content input.input-text,
.woocommerce-MyAccount-content input[type="text"],
.woocommerce-MyAccount-content input[type="email"],
.woocommerce-MyAccount-content input[type="password"],
.woocommerce-MyAccount-content input[type="tel"],
.woocommerce-MyAccount-content textarea,
.woocommerce-MyAccount-content select {
	width: 100%;
	height: 50px;
	border: 1px solid #d9dee7;
	border-radius: 12px;
	background: #fff;
	padding: 0 15px;
	color: #111827;
	font-size: 15px;
	outline: none;
	transition: .2s ease;
	box-shadow: none;
}

.woocommerce-MyAccount-content textarea {
	min-height: 120px;
	padding-top: 14px;
	resize: vertical;
}

.woocommerce-MyAccount-content input:focus,
.woocommerce-MyAccount-content textarea:focus,
.woocommerce-MyAccount-content select:focus {
	border-color: #1f2933;
	box-shadow: 0 0 0 3px rgba(31, 41, 51, .08);
}

/* Кнопки */
.woocommerce-MyAccount-content .button,
.woocommerce-MyAccount-content button.button {
	min-height: 50px;
	border: none;
	border-radius: 12px;
	background: #1f2933;
	padding: 13px 22px;
	color: #fff;
	font-size: 15px;
	font-weight: 600;
	cursor: pointer;
	transition: .2s ease;
}

.woocommerce-MyAccount-content .button:hover,
.woocommerce-MyAccount-content button.button:hover {
	background: #111827;
	color: #fff;
}

.woocommerce-MyAccount-content table .button,
.woocommerce-MyAccount-content table button.button {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	min-height: 38px;
	padding: 9px 16px;
	border-radius: 999px;
	font-size: 14px;
	line-height: 1.2;
	text-decoration: none;
	white-space: nowrap;
}

.woocommerce-MyAccount-content .woocommerce-pagination {
	display: flex;
	align-items: center;
	flex-wrap: wrap;
	gap: 8px;
	margin-top: 14px;
}

.woocommerce-MyAccount-content .woocommerce-pagination .button {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	min-height: 38px;
	padding: 9px 16px;
	border-radius: 999px;
	font-size: 14px;
	line-height: 1.2;
	text-decoration: none;
	white-space: nowrap;
}

.woocommerce-MyAccount-content .woocommerce-orders-table__cell-order-actions,
.woocommerce-MyAccount-content .woocommerce-table--order-details tfoot td:last-child {
	display: flex;
	align-items: center;
	flex-wrap: wrap;
	gap: 8px;
}

.woocommerce-MyAccount-content .woocommerce-orders-table__cell-order-actions .button + .button {
	margin-left: 0;
}

/* Таблицы заказов */
.woocommerce-MyAccount-content table {
	width: 100%;
	border-collapse: separate;
	border-spacing: 0;
	border: 1px solid #e6e8ec;
	border-radius: 14px;
	overflow: hidden;
}

.woocommerce-MyAccount-content table th,
.woocommerce-MyAccount-content table td {
	padding: 14px 16px;
	border-bottom: 1px solid #e6e8ec;
	text-align: left;
}

.woocommerce-MyAccount-content table tr:last-child td {
	border-bottom: none;
}
	
.woocommerce-Price-currencySymbol{
 	font-size: 12px;
}
	
	.woocommerce-info{
		border-left-color: var(--gl-color-accent) !important;
	}
	.woocommerce-info .button{
		background: var(--gl-color-accent) !important;
	}
	.woocommerce-info::before{
		background: var(--gl-color-accent);
	}
	div.woocommerce-info::before{
		top: 42px;
	}
	


/* Мобилка */
@media (max-width: 768px) {
	.gl-entry .woocommerce {
		grid-template-columns: 1fr;
		gap: 16px;
	}

	.woocommerce-MyAccount-navigation ul {
		display: grid;
		grid-template-columns: 1fr 1fr;
	}

	.woocommerce-MyAccount-navigation a {
		min-height: 42px;
		padding: 10px 12px;
		font-size: 14px;
	}

	.woocommerce-MyAccount-content {
		padding: 20px;
	}
}

@media (max-width: 480px) {
	.woocommerce-MyAccount-navigation ul {
		grid-template-columns: 1fr;
	}
}
</style>