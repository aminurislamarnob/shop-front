import { Component } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

export class ErrorBoundary extends Component {
	constructor( props ) {
		super( props );
		this.state = { hasError: false, error: null };
	}

	static getDerivedStateFromError( error ) {
		return { hasError: true, error };
	}

	componentDidCatch( error, info ) {
		// eslint-disable-next-line no-console
		console.error( '[StoreSuite Analytics] Error:', error, info );
	}

	render() {
		if ( this.state.hasError ) {
			return (
				<div className="storesuite-analytics-error">
					<h3>{ __( 'Something went wrong loading analytics.', 'storesuite' ) }</h3>
					<button onClick={ () => window.location.reload() }>
						{ __( 'Reload', 'storesuite' ) }
					</button>
				</div>
			);
		}
		return this.props.children;
	}
}

export default ErrorBoundary;
