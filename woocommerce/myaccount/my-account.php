<?php
/**
 * My Account page.
 *
 * @package Gelikon
 * @version 3.5.0
 */

defined('ABSPATH') || exit;
?>

<style>
.woocommerce-account .site-main {
	background: linear-gradient(180deg, rgba(18, 212, 87, .06) 0%, rgba(255, 255, 255, 0) 320px);
}

.woocommerce-account .woocommerce {
	margin-top: 18px;
	color: var(--gl-color-text, #2b2b2b);
}

.woocommerce-account .woocommerce:after {
	content: '';
	display: table;
	clear: both;
}

.woocommerce-account .woocommerce-MyAccount-navigation,
.woocommerce-account .woocommerce-MyAccount-content {
	box-sizing: border-box;
}

.woocommerce-account .woocommerce-MyAccount-navigation {
	float: left;
	width: 300px;
	margin: 0 28px 0 0;
	padding: 18px;
	border: 1px solid var(--gl-color-line, #dde4dd);
	border-radius: var(--gl-radius, 24px);
	background: var(--gl-color-surface, #fff);
	box-shadow: var(--gl-shadow, 0 12px 32px rgba(22, 34, 51, .08));
}

.woocommerce-account .woocommerce-MyAccount-navigation ul {
	display: flex;
	flex-direction: column;
	gap: 8px;
	margin: 0;
	padding: 0;
	list-style: none;
}

.woocommerce-account .woocommerce-MyAccount-navigation li {
	margin: 0;
	padding: 0;
}

.woocommerce-account .woocommerce-MyAccount-navigation a {
	display: flex;
	align-items: center;
	min-height: 48px;
	padding: 12px 16px;
	border: 1px solid transparent;
	border-radius: 16px;
	color: var(--gl-color-muted, #69707d);
	font-size: 15px;
	font-weight: 800;
	line-height: 1.2;
	transition: color .2s ease, background-color .2s ease, border-color .2s ease, transform .2s ease;
}

.woocommerce-account .woocommerce-MyAccount-navigation a:hover,
.woocommerce-account .woocommerce-MyAccount-navigation .is-active a {
	border-color: rgba(18, 212, 87, .24);
	background: rgba(18, 212, 87, .10);
	color: var(--gl-color-heading, #1a1a1a);
}

.woocommerce-account .woocommerce-MyAccount-navigation a:hover {
	transform: translateX(3px);
}

.woocommerce-account .woocommerce-MyAccount-content {
	float: right;
	width: calc(100% - 328px);
	min-height: 420px;
	padding: 28px;
	border: 1px solid var(--gl-color-line, #dde4dd);
	border-radius: var(--gl-radius, 24px);
	background: var(--gl-color-surface, #fff);
	box-shadow: var(--gl-shadow, 0 12px 32px rgba(22, 34, 51, .08));
}

.woocommerce-account .woocommerce-MyAccount-content > p:first-child {
	margin-top: 0;
}

.woocommerce-account .woocommerce-MyAccount-content p {
	color: var(--gl-color-text, #2b2b2b);
	font-size: 16px;
	line-height: 1.7;
}

.woocommerce-account .woocommerce-MyAccount-content p a,
.woocommerce-account .woocommerce-MyAccount-content address a {
	color: var(--gl-color-accent-2, #1ea751);
	font-weight: 800;
	text-decoration: none;
}

.woocommerce-account .woocommerce-MyAccount-content p a:hover,
.woocommerce-account .woocommerce-MyAccount-content address a:hover {
	color: var(--gl-color-accent, #12d457);
}

.woocommerce-account .woocommerce-MyAccount-content h2,
.woocommerce-account .woocommerce-MyAccount-content h3,
.woocommerce-account .woocommerce-MyAccount-content legend {
	margin: 0 0 18px;
	color: var(--gl-color-heading, #1a1a1a);
	font-weight: 900;
	line-height: 1.12;
	letter-spacing: -.03em;
}

.woocommerce-account .woocommerce-MyAccount-content h2 {
	font-size: clamp(26px, 3vw, 36px);
}

.woocommerce-account .woocommerce-MyAccount-content h3,
.woocommerce-account .woocommerce-MyAccount-content legend {
	font-size: clamp(22px, 2vw, 28px);
}

.woocommerce-account .woocommerce-MyAccount-content .woocommerce-Address,
.woocommerce-account .woocommerce-MyAccount-content .woocommerce-address-fields,
.woocommerce-account .woocommerce-MyAccount-content form,
.woocommerce-account .woocommerce-MyAccount-content fieldset {
	border-color: var(--gl-color-line, #dde4dd);
	border-radius: var(--gl-radius-sm, 14px);
}

.woocommerce-account .woocommerce-MyAccount-content .woocommerce-Address {
	padding: 22px;
	background: #fbfcfb;
}

.woocommerce-account .woocommerce-MyAccount-content .woocommerce-Address-title {
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 16px;
	margin-bottom: 14px;
}

.woocommerce-account .woocommerce-MyAccount-content .woocommerce-Address-title h2 {
	margin: 0;
	font-size: 24px;
}

.woocommerce-account .woocommerce-MyAccount-content address {
	color: var(--gl-color-muted, #69707d);
	font-style: normal;
	line-height: 1.65;
}

.woocommerce-account .woocommerce-MyAccount-content table.shop_table {
	overflow: hidden;
	margin: 0 0 24px;
	border: 1px solid var(--gl-color-line, #dde4dd);
	border-radius: 18px;
	border-collapse: separate;
	border-spacing: 0;
	background: #fff;
	box-shadow: none;
}

.woocommerce-account .woocommerce-MyAccount-content table.shop_table th,
.woocommerce-account .woocommerce-MyAccount-content table.shop_table td {
	padding: 16px;
	border-color: var(--gl-color-line, #dde4dd);
	color: var(--gl-color-text, #2b2b2b);
	vertical-align: middle;
}

.woocommerce-account .woocommerce-MyAccount-content table.shop_table th {
	background: #f6faf7;
	color: var(--gl-color-heading, #1a1a1a);
	font-size: 13px;
	font-weight: 900;
	letter-spacing: .04em;
	text-transform: uppercase;
}

.woocommerce-account .woocommerce-MyAccount-content table.shop_table td a:not(.button) {
	color: var(--gl-color-accent-2, #1ea751);
	font-weight: 800;
}

.woocommerce-account .woocommerce-MyAccount-content .woocommerce-orders-table__cell-order-actions .button,
.woocommerce-account .woocommerce-MyAccount-content .woocommerce-button,
.woocommerce-account .woocommerce-MyAccount-content button.button,
.woocommerce-account .woocommerce-MyAccount-content input.button {
	min-height: 44px;
	padding: 0 20px;
	border: 0;
	border-radius: var(--gl-btn-radius, 999px);
	background: var(--gl-color-accent, #12d457);
	color: #fff !important;
	font-size: 14px;
	font-weight: 900;
	line-height: 1;
	box-shadow: 0 12px 24px rgba(18, 212, 87, .22);
	transition: background-color .2s ease, box-shadow .2s ease, transform .2s ease;
}

.woocommerce-account .woocommerce-MyAccount-content .woocommerce-orders-table__cell-order-actions .button:hover,
.woocommerce-account .woocommerce-MyAccount-content .woocommerce-button:hover,
.woocommerce-account .woocommerce-MyAccount-content button.button:hover,
.woocommerce-account .woocommerce-MyAccount-content input.button:hover {
	background: var(--gl-color-accent-2, #1ea751);
	box-shadow: 0 14px 26px rgba(30, 167, 81, .24);
	transform: translateY(-1px);
}

.woocommerce-account .woocommerce-MyAccount-content form .form-row {
	margin: 0 0 16px;
	padding: 0;
}

.woocommerce-account .woocommerce-MyAccount-content label {
	margin: 0 0 8px;
	color: var(--gl-color-heading, #1a1a1a);
	font-size: 14px;
	font-weight: 800;
	line-height: 1.3;
}

.woocommerce-account .woocommerce-MyAccount-content input.input-text,
.woocommerce-account .woocommerce-MyAccount-content input[type='text'],
.woocommerce-account .woocommerce-MyAccount-content input[type='email'],
.woocommerce-account .woocommerce-MyAccount-content input[type='password'],
.woocommerce-account .woocommerce-MyAccount-content input[type='tel'],
.woocommerce-account .woocommerce-MyAccount-content select,
.woocommerce-account .woocommerce-MyAccount-content textarea {
	width: 100%;
	min-height: 52px;
	padding: 12px 16px;
	border: 1px solid #d8e2d9;
	border-radius: 16px;
	background: #fff;
	color: var(--gl-color-text, #2b2b2b);
	font-size: 16px;
	line-height: 1.35;
	box-shadow: none;
	transition: border-color .2s ease, box-shadow .2s ease;
}

.woocommerce-account .woocommerce-MyAccount-content textarea {
	min-height: 110px;
	resize: vertical;
}

.woocommerce-account .woocommerce-MyAccount-content input:focus,
.woocommerce-account .woocommerce-MyAccount-content select:focus,
.woocommerce-account .woocommerce-MyAccount-content textarea:focus {
	outline: none;
	border-color: var(--gl-color-accent-2, #1ea751);
	box-shadow: 0 0 0 4px rgba(18, 212, 87, .14);
}

.woocommerce-account .woocommerce-MyAccount-content .select2-container .select2-selection--single {
	min-height: 52px;
	border: 1px solid #d8e2d9;
	border-radius: 16px;
}

.woocommerce-account .woocommerce-MyAccount-content .select2-container--default .select2-selection--single .select2-selection__rendered {
	padding: 11px 16px;
	color: var(--gl-color-text, #2b2b2b);
	line-height: 30px;
}

.woocommerce-account .woocommerce-MyAccount-content .select2-container--default .select2-selection--single .select2-selection__arrow {
	height: 52px;
}

.woocommerce-account .woocommerce-MyAccount-content fieldset {
	margin: 24px 0;
	padding: 22px;
	border: 1px solid var(--gl-color-line, #dde4dd);
	background: #fbfcfb;
}

.woocommerce-account .woocommerce-message,
.woocommerce-account .woocommerce-info,
.woocommerce-account .woocommerce-error {
	margin: 0 0 18px;
	padding: 16px 18px 16px 52px;
	border: 1px solid var(--gl-color-line, #dde4dd);
	border-radius: 18px;
	background: #fff;
	color: var(--gl-color-text, #2b2b2b);
	box-shadow: 0 10px 28px rgba(22, 34, 51, .06);
}

.woocommerce-account .woocommerce-message:before,
.woocommerce-account .woocommerce-info:before {
	color: var(--gl-color-accent-2, #1ea751);
}

.woocommerce-account .woocommerce-error:before {
	color: #d94141;
}

.woocommerce-account mark,
.woocommerce-account .woocommerce-MyAccount-content mark {
	border-radius: 999px;
	padding: 4px 10px;
	background: rgba(18, 212, 87, .12);
	color: var(--gl-color-accent-2, #1ea751);
	font-weight: 900;
}

@media (max-width: 960px) {
	.woocommerce-account .woocommerce-MyAccount-navigation,
	.woocommerce-account .woocommerce-MyAccount-content {
		float: none;
		width: 100%;
	}

	.woocommerce-account .woocommerce-MyAccount-navigation {
		margin: 0 0 18px;
	}

	.woocommerce-account .woocommerce-MyAccount-navigation ul {
		display: grid;
		grid-template-columns: repeat(2, minmax(0, 1fr));
	}
}

@media (max-width: 640px) {
	.woocommerce-account .woocommerce {
		margin-top: 8px;
	}

	.woocommerce-account .woocommerce-MyAccount-navigation,
	.woocommerce-account .woocommerce-MyAccount-content {
		padding: 16px;
		border-radius: 20px;
	}

	.woocommerce-account .woocommerce-MyAccount-navigation ul {
		grid-template-columns: 1fr;
	}

	.woocommerce-account .woocommerce-MyAccount-navigation a {
		min-height: 44px;
	}

	.woocommerce-account .woocommerce-MyAccount-content table.shop_table,
	.woocommerce-account .woocommerce-MyAccount-content table.shop_table tbody,
	.woocommerce-account .woocommerce-MyAccount-content table.shop_table tr,
	.woocommerce-account .woocommerce-MyAccount-content table.shop_table td {
		display: block;
		width: 100%;
	}

	.woocommerce-account .woocommerce-MyAccount-content table.shop_table thead {
		display: none;
	}

	.woocommerce-account .woocommerce-MyAccount-content table.shop_table tr {
		padding: 12px 0;
		border-bottom: 1px solid var(--gl-color-line, #dde4dd);
	}

	.woocommerce-account .woocommerce-MyAccount-content table.shop_table tr:last-child {
		border-bottom: 0;
	}

	.woocommerce-account .woocommerce-MyAccount-content table.shop_table td {
		padding: 8px 14px;
		border: 0;
	}

	.woocommerce-account .woocommerce-MyAccount-content table.shop_table td:before {
		content: attr(data-title);
		display: block;
		margin-bottom: 4px;
		color: var(--gl-color-muted, #69707d);
		font-size: 12px;
		font-weight: 900;
		letter-spacing: .04em;
		text-transform: uppercase;
	}
}
</style>

<?php
/**
 * My Account navigation.
 *
 * @since 2.6.0
 */
do_action('woocommerce_account_navigation');
?>

<div class="woocommerce-MyAccount-content">
	<?php
		/**
		 * My Account content.
		 *
		 * @since 2.6.0
		 */
		do_action('woocommerce_account_content');
	?>
</div>
