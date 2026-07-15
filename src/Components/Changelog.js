import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Card, CardBody, Spinner, Button } from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';

// Number of changelog releases revealed per "Load more" click.
const RELEASES_PER_PAGE = 4;

/**
 * Render a changelog entry, converting the readme.txt inline markup
 * (**bold** and `code`) into elements.
 *
 * @param {string} text Raw entry text from readme.txt.
 * @return {Array} React children.
 */
const formatEntry = ( text ) =>
	text
		.split( /(\*\*[^*]+\*\*|`[^`]+`)/g )
		.filter( ( part ) => part !== '' )
		.map( ( part, index ) => {
			if ( part.startsWith( '**' ) && part.endsWith( '**' ) ) {
				return <strong key={ index }>{ part.slice( 2, -2 ) }</strong>;
			}
			if ( part.startsWith( '`' ) && part.endsWith( '`' ) ) {
				return <code key={ index }>{ part.slice( 1, -1 ) }</code>;
			}
			return part;
		} );

const Changelog = () => {
	const [ releases, setReleases ] = useState( [] );
	const [ isLoading, setIsLoading ] = useState( true );
	const [ hasError, setHasError ] = useState( false );
	const [ visibleCount, setVisibleCount ] = useState( RELEASES_PER_PAGE );

	useEffect( () => {
		apiFetch( { path: '/storesuite/v1/changelog' } )
			.then( ( response ) => {
				setReleases( response?.releases || [] );
			} )
			.catch( () => {
				setHasError( true );
			} )
			.finally( () => {
				setIsLoading( false );
			} );
	}, [] );

	return (
		<div
			className="storesuite-section storesuite-section--medium"
			id="storesuite-changelog"
		>
			<Card className="storesuite-form-header-card">
				<CardBody className="storesuite-form-section-header">
					<h3 className="storesuite-section-title">
						{ __( 'Changelog', 'storesuite' ) }
					</h3>
					<p className="storesuite-section-description">
						{ __(
							"What's new in StoreSuite — every release, version by version.",
							'storesuite'
						) }
					</p>
				</CardBody>
			</Card>
			<Card>
				<CardBody className="storesuite-form-section-body">
					{ isLoading && (
						<div className="storesuite-loading">
							<Spinner />
						</div>
					) }

					{ ! isLoading && ( hasError || releases.length === 0 ) && (
						<p className="storesuite-changelog-empty">
							{ __(
								'No changelog entries found.',
								'storesuite'
							) }
						</p>
					) }

					{ ! isLoading && releases.length > 0 && (
						<div className="storesuite-changelog">
							{ releases
								.slice( 0, visibleCount )
								.map( ( release, index ) => (
									<div
										key={ release.version }
										className="storesuite-changelog-release"
									>
										<div className="storesuite-changelog-marker">
											<span className="storesuite-changelog-dot" />
										</div>
										<div className="storesuite-changelog-details">
											<div className="storesuite-changelog-release-header">
												<span className="storesuite-changelog-version">
													{ 'v' + release.version }
												</span>
												{ index === 0 && (
													<span className="storesuite-changelog-latest">
														{ __(
															'Latest',
															'storesuite'
														) }
													</span>
												) }
											</div>
											<ul className="storesuite-changelog-entries">
												{ release.entries.map(
													( entry, entryIndex ) => (
														<li key={ entryIndex }>
															{ formatEntry(
																entry
															) }
														</li>
													)
												) }
											</ul>
										</div>
									</div>
								) ) }
						</div>
					) }

					{ ! isLoading && releases.length > visibleCount && (
						<div className="storesuite-changelog-load-more">
							<Button
								variant="secondary"
								onClick={ () =>
									setVisibleCount(
										( count ) => count + RELEASES_PER_PAGE
									)
								}
							>
								{ __( 'Load more', 'storesuite' ) }
							</Button>
						</div>
					) }
				</CardBody>
			</Card>
		</div>
	);
};

export default Changelog;
