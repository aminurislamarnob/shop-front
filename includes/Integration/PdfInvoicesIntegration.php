<?php

namespace PluginizeLab\StoreSuite\Integration;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WC_Order;

/**
 * Surfaces third-party PDF-invoice / packing-slip document actions inside the
 * StoreSuite frontend order list and order details pages.
 *
 * Supported plugins (detected dynamically, whichever are active):
 * - WooCommerce PDF Invoices & Packing Slips (WPO).
 * - WebToffee Print Invoices, Packing Slips, Delivery Notes & Shipping Labels.
 *
 * The class is fully self-contained: the order templates only expose generic
 * StoreSuite extension hooks, and this integration injects the document links.
 */
class PdfInvoicesIntegration {

	/**
	 * Constructor. Bails unless at least one supported plugin is active.
	 */
	public function __construct() {
		if ( ! $this->is_wpo_active() && ! $this->is_webtoffee_active() ) {
			return;
		}

		add_action( 'storesuite_order_list_row_actions', array( $this, 'render_list_row_actions' ) );
		// Priority 5 so the Documents card appears above notes / customer-history (default 10).
		add_action( 'storesuite_after_order_details_action', array( $this, 'render_order_details_documents' ), 5 );
	}

	/**
	 * Whether WooCommerce PDF Invoices & Packing Slips (WPO) is active.
	 *
	 * @return bool
	 */
	protected function is_wpo_active(): bool {
		return function_exists( 'WPO_WCPDF' );
	}

	/**
	 * Whether WebToffee Print Invoices, Packing Slips etc. is active.
	 *
	 * @return bool
	 */
	protected function is_webtoffee_active(): bool {
		return class_exists( 'Wf_Woocommerce_Packing_List_Admin' );
	}

	/**
	 * Render document links inside the order list row-actions dropdown.
	 *
	 * @param WC_Order $order Current order.
	 */
	public function render_list_row_actions( $order ) {
		if ( ! $order instanceof WC_Order || ! current_user_can( 'edit_shop_orders' ) ) {
			return;
		}

		// Trashed orders have no printable documents.
		if ( 'trash' === $order->get_status() ) {
			return;
		}

		$documents = $this->get_documents_for_order( $order, 'list_page' );

		foreach ( $documents as $document ) {
			if ( empty( $document['url'] ) ) {
				continue;
			}
			?>
			<li>
				<a href="<?php echo esc_url( $document['url'] ); ?>" class="dropdown-link" target="_blank" rel="noopener noreferrer">
					<?php echo esc_html( $document['label'] ); ?>
				</a>
			</li>
			<?php
		}
	}

	/**
	 * Render a "Documents" card in the order details sidebar.
	 *
	 * @param WC_Order $order Current order.
	 */
	public function render_order_details_documents( $order ) {
		if ( ! $order instanceof WC_Order || ! current_user_can( 'edit_shop_orders' ) ) {
			return;
		}

		$documents = array_filter(
			$this->get_documents_for_order( $order, 'detail_page' ),
			static function ( $document ) {
				return ! empty( $document['url'] );
			}
		);

		if ( empty( $documents ) ) {
			return;
		}
		?>
		<div class="storesuite-card storesuite-order-documents">
			<div class="card-title-with-link">
				<h3 class="storesuite-card-title"><?php esc_html_e( 'Documents', 'storesuite' ); ?></h3>
			</div>
			<div class="storesuite-card-content">
				<div class="storesuite-order-documents-list">
					<?php foreach ( $documents as $document ) : ?>
						<a href="<?php echo esc_url( $document['url'] ); ?>" class="my-storesuite-button my-storesuite-button-light" target="_blank" rel="noopener noreferrer">
							<?php echo esc_html( $document['label'] ); ?>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Build a normalized list of documents for an order, merged from every
	 * active supported plugin.
	 *
	 * @param WC_Order $order   The order.
	 * @param string   $context Rendering context: 'list_page' or 'detail_page'.
	 *
	 * @return array<int, array{label:string, url:string, exists:bool}>
	 */
	public function get_documents_for_order( WC_Order $order, string $context ): array {
		$documents = array();

		if ( $this->is_wpo_active() ) {
			$documents = array_merge( $documents, $this->get_wpo_documents( $order ) );
		}

		if ( $this->is_webtoffee_active() ) {
			$documents = array_merge( $documents, $this->get_webtoffee_documents( $order, $context ) );
		}

		return $documents;
	}

	/**
	 * Collect WooCommerce PDF Invoices & Packing Slips (WPO) documents.
	 *
	 * Mirrors the admin order-actions logic in the plugin's includes/Admin.php.
	 *
	 * @param WC_Order $order The order.
	 *
	 * @return array<int, array{label:string, url:string, exists:bool}>
	 */
	protected function get_wpo_documents( WC_Order $order ): array {
		$items = array();

		if ( ! function_exists( 'wcpdf_get_document' ) ) {
			return $items;
		}

		$documents = WPO_WCPDF()->documents->get_documents( 'enabled', 'any' );

		foreach ( $documents as $document ) {
			$type = $document->get_type();
			$doc  = wcpdf_get_document( $type, $order );

			if ( ! $doc || ! $doc->is_enabled( 'pdf' ) ) {
				continue;
			}

			$items[] = array(
				'label'  => $doc->get_title(),
				'url'    => WPO_WCPDF()->endpoint->get_document_link( $order, $type ),
				'exists' => is_callable( array( $doc, 'exists' ) ) ? $doc->exists() : false,
			);
		}

		return $items;
	}

	/**
	 * Collect WebToffee Print Invoices / Packing Slips documents.
	 *
	 * Reuses the plugin's own `wt_print_actions` button filter so every document
	 * the plugin exposes is included, with URLs and labels already built.
	 *
	 * @param WC_Order $order   The order.
	 * @param string   $context Rendering context: 'list_page' or 'detail_page'.
	 *
	 * @return array<int, array{label:string, url:string, exists:bool}>
	 */
	protected function get_webtoffee_documents( WC_Order $order, string $context ): array {
		$items = array();

		$buttons = apply_filters( 'wt_print_actions', array(), $order, $order->get_id(), $context );

		if ( empty( $buttons ) || ! is_array( $buttons ) ) {
			return $items;
		}

		foreach ( $buttons as $button ) {
			$normalized = $this->normalize_webtoffee_button( $button );

			if ( ! empty( $normalized['url'] ) ) {
				$items[] = $normalized;
			}
		}

		return $items;
	}

	/**
	 * Normalize a single WebToffee button entry into label + url + exists.
	 *
	 * The `wt_print_actions` filter may yield ready-built anchor HTML or an
	 * associative array describing the action; both shapes are handled.
	 *
	 * @param mixed $button Button data (HTML string or array).
	 *
	 * @return array{label:string, url:string, exists:bool}
	 */
	protected function normalize_webtoffee_button( $button ): array {
		$normalized = array(
			'label'  => '',
			'url'    => '',
			'exists' => false,
		);

		// Ready-built anchor HTML: extract the href and inner text.
		if ( is_string( $button ) ) {
			if ( preg_match( '/href=["\']([^"\']+)["\']/', $button, $href_match ) ) {
				$normalized['url'] = html_entity_decode( $href_match[1] );
			}

			$text = wp_strip_all_tags( $button );
			if ( '' !== trim( $text ) ) {
				$normalized['label'] = trim( $text );
			}

			return $normalized;
		}

		if ( is_array( $button ) ) {
			$normalized['label'] = (string) ( $button['name'] ?? $button['label'] ?? $button['text'] ?? '' );
			$normalized['url']   = (string) ( $button['url'] ?? $button['link'] ?? $button['href'] ?? '' );

			// Some entries only carry a ready-built HTML fragment.
			if ( '' === $normalized['url'] && isset( $button['html'] ) && is_string( $button['html'] ) ) {
				return $this->normalize_webtoffee_button( $button['html'] );
			}
		}

		if ( '' === $normalized['label'] && '' !== $normalized['url'] ) {
			$normalized['label'] = __( 'Document', 'storesuite' );
		}

		return $normalized;
	}
}
