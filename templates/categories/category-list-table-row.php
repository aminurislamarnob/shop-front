<tr>
	<td><?php echo esc_html( $dash_prefix . $category->name ); ?></td>
	<td><?php echo esc_html( wp_trim_words( $category->description, '9', '...' ) ); ?></td>
	<td><?php echo esc_html( $category->slug ); ?></td>
	<td><?php echo esc_html( $category->count ); ?></td>
	<td class="text-right" data-title="<?php esc_attr_e( 'Actions', 'shop-front' ); ?>">
		<div class="msfc-dropdown">
			<span class="msfc-dropdown-icon">
				<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-three-dots" viewBox="0 0 16 16">
					<path d="M3 9.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3"/>
				</svg>
			</span>
			<ul class="msfc-dropdown-menu">
				<li>
					<a href="<?php echo esc_url( get_home_url() . '/product-category/' . $category->slug . '/' ); ?>" class="dropdown-link">
						<?php echo esc_html__( 'View', 'shop-front' ); ?>
					</a>
				</li>
				<li>
					<a href="<?php echo esc_url( get_home_url() . '/msfc-product-category/edit-product-category/' . $category->term_id . '/' ); ?>" class="dropdown-link"><?php echo esc_html__( 'Edit', 'shop-front' ); ?></a>
				</li>
				<li>
					<form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="POST">
						<?php wp_nonce_field( 'msfc_wfm_cat_dlt_non_220', 'msfc_wfm_cat_dlt_non_nonce' ); ?>
						<input type="hidden" name="id" value="<?php echo esc_attr( $category->term_id ); ?>">
						<input type="hidden" name="action" value="msfc_wfm_dlt_product_cat_action">
						<button type="submit" class="inline-button dropdown-link"><?php echo esc_html__( 'Delete', 'shop-front' ); ?></button>
					</form>
				</li>
			</ul>
		</div>
	</td>
</tr>