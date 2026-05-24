<?php
/**
 * Template used to render an individual row within the downloadable files table.
 *
 * @package StoreSuite
 *
 * @var bool   $disabled_download Indicates if the current downloadable file is disabled.
 * @var array  $file              Product download data.
 * @var string $key               Product download key.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<tr>
	<td class="sort"><span class="storesuite-drag-handle">&#9776;</span></td>
	<td class="file_name">
		<input type="text" class="storesuite-form-control input_text" placeholder="<?php esc_attr_e( 'File name', 'storesuite' ); ?>" name="_wc_file_names[]" value="<?php echo esc_attr( $file['name'] ); ?>" />
		<input type="hidden" name="_wc_file_hashes[]" value="<?php echo esc_attr( $key ); ?>" />
	</td>
	<td class="file_url">
		<input type="text" class="storesuite-form-control input_text storesuite-downloadable-file-url-input" placeholder="<?php esc_attr_e( 'https://', 'storesuite' ); ?>" name="_wc_file_urls[]" value="<?php echo esc_attr( $file['file'] ); ?>" />
		<?php if ( $disabled_download ) : ?>
			<span class="disabled">*</span>
		<?php endif; ?>
	</td>
	<td class="file_url_choose" width="15%" colspan="2">
		<a href="#" class="storesuite-upload-file-button" data-choose="<?php esc_attr_e( 'Choose file', 'storesuite' ); ?>" data-update="<?php esc_attr_e( 'Insert file URL', 'storesuite' ); ?>" aria-label="<?php esc_attr_e( 'Choose file', 'storesuite' ); ?>" title="<?php esc_attr_e( 'Choose file', 'storesuite' ); ?>">
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M18.4,7.379a1.128,1.128,0,0,1-.769-.754h0a8,8,0,1,0-15.1,5.237A1.046,1.046,0,0,1,2.223,13.1,5.5,5.5,0,0,0,.057,18.3,5.622,5.622,0,0,0,5.683,23H11a1,1,0,0,0,1-1h0a1,1,0,0,0-1-1H5.683a3.614,3.614,0,0,1-3.646-2.981,3.456,3.456,0,0,1,1.376-3.313A3.021,3.021,0,0,0,4.4,11.141a6.113,6.113,0,0,1-.073-4.126A5.956,5.956,0,0,1,9.215,3.05,6.109,6.109,0,0,1,9.987,3a5.984,5.984,0,0,1,5.756,4.28,2.977,2.977,0,0,0,2.01,1.99,5.934,5.934,0,0,1,.778,11.09.976.976,0,0,0-.531.888h0a.988.988,0,0,0,1.388.915c4.134-1.987,6.38-7.214,2.88-12.264A6.935,6.935,0,0,0,18.4,7.379Z"/><path d="M18.707,16.707a1,1,0,0,0,0-1.414l-1.586-1.586a3,3,0,0,0-4.242,0l-1.586,1.586a1,1,0,0,0,1.414,1.414L14,15.414V23a1,1,0,0,0,2,0V15.414l1.293,1.293a1,1,0,0,0,1.414,0Z"/></svg>
		</a>
		<a href="#" class="storesuite-delete-file" aria-label="<?php esc_attr_e( 'Delete', 'storesuite' ); ?>" title="<?php esc_attr_e( 'Delete', 'storesuite' ); ?>">
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M21,4H17.9A5.009,5.009,0,0,0,13,0H11A5.009,5.009,0,0,0,6.1,4H3A1,1,0,0,0,3,6H4V19a5.006,5.006,0,0,0,5,5h6a5.006,5.006,0,0,0,5-5V6h1a1,1,0,0,0,0-2ZM11,2h2a3.006,3.006,0,0,1,2.829,2H8.171A3.006,3.006,0,0,1,11,2Zm7,17a3,3,0,0,1-3,3H9a3,3,0,0,1-3-3V6H18Z"/><path d="M10,18a1,1,0,0,0,1-1V11a1,1,0,0,0-2,0v6A1,1,0,0,0,10,18Z"/><path d="M14,18a1,1,0,0,0,1-1V11a1,1,0,0,0-2,0v6A1,1,0,0,0,14,18Z"/></svg>
		</a>
	</td>
</tr>
