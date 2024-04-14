/* global WPHB_Admin */

/**
 * External dependencies
 */
import React from 'react';
import classNames from 'classnames';

/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;

/**
 * Internal dependencies
 */
import Assets from './assets';
import Configurations from './configurations';
import Icon from '../../components/sui-icon';
import Tag from '../../components/sui-tag';
import Toggle from '../../components/sui-toggle';
import BoxBuilderField from '../../components/sui-box-builder/field';

/**
 * AutoAssets component.
 *
 * @since 3.3.0
 */
export default class AutoAssets extends React.Component {
	/**
	 * Component constructor.
	 *
	 * @param {Object} props
	 */
	constructor( props ) {
		super( props );

		this.state = {
			loading: this.props.loading,
			type: 'speedy', // Accepts: 'speedy' or 'basic'.
			assets: {
				styles: {},
				scripts: {},
			},
			enabled: {
				styles: true,
				scripts: true,
				fonts: true,
			},
			exclusions: {
				styles: [],
				scripts: [],
			},
		};

		this.resetSettings = this.resetSettings.bind( this );
		this.handleToggleChange = this.handleToggleChange.bind( this );
		this.updateCheckBox = this.updateCheckBox.bind( this );
		this.updateExclusions = this.updateExclusions.bind( this );
		this.saveSettings = this.saveSettings.bind( this );
	}

	/**
	 * Invoked immediately after a component is mounted.
	 */
	componentDidMount() {
		this.props.api
			.post( 'minify_auto_status' )
			.then( ( response ) => {
				this.setState( {
					loading: false,
					type: response.type,
					assets: response.assets,
					enabled: response.enabled,
					exclusions: response.exclusions,
				} );
			} )
			.catch( ( error ) => window.console.log( error ) );
	}

	/**
	 * Reset asset optimization settings.
	 */
	resetSettings() {
		this.setState( { loading: true } );

		this.props.api
			.post( 'minify_reset_settings' )
			.then( () => {
				WPHB_Admin.notices.show(
					__( 'Settings restored to defaults', 'wphb' )
				);
				this.setState( {
					loading: false,
					enabled: {
						styles: true,
						scripts: true,
						fonts: true,
					},
					exclusions: {
						styles: [],
						scripts: [],
					},
				} );
			} )
			.catch( ( error ) => window.console.log( error ) );
	}

	/**
	 * Handle toggle click (Speedy/Basic).
	 *
	 * @param {Object} e Event.
	 */
	handleToggleChange( e ) {
		if ( ! e.target.checked ) {
			return;
		}

		this.setState( {
			type: e.target.dataset.type,
		} );
	}

	/**
	 * Update files checkbox states.
	 *
	 * @param {Object} e
	 */
	updateCheckBox( e ) {
		if ( 'undefined' === e.target.id ) {
			return;
		}

		const enabled = {
			styles: this.state.enabled.styles,
			scripts: this.state.enabled.scripts,
			fonts: this.state.enabled.fonts,
		};

		if ( 'wphb-auto-css' === e.target.id ) {
			enabled.styles = e.target.checked;
		}

		if ( 'wphb-auto-js' === e.target.id ) {
			enabled.scripts = e.target.checked;
		}

		if ( 'wphb-auto-fonts' === e.target.id ) {
			enabled.fonts = e.target.checked;
		}

		this.setState( { enabled } );
	}

	/**
	 * Update exclusions list.
	 *
	 * @param {Object} e
	 */
	updateExclusions( e ) {
		if ( ! e.target.value ) {
			return;
		}

		const selected = jQuery( '#wphb-auto-exclude' ).find( ':selected' );

		const exclusions = { styles: [], scripts: [] };

		for ( let i = 0; i < selected.length; ++i ) {
			/**
			 * Our values in select are in the format of <type>-<handle>.
			 * So we separate the string into type and handle values.
			 */
			const type = selected[ i ].value.slice( 0, selected[ i ].value.indexOf( '-' ) );
			const handle = selected[ i ].value.slice( selected[ i ].value.indexOf( '-' ) + 1 );
			exclusions[ type ].push( handle );
		}

		this.setState( { exclusions } );
	}

	/**
	 * Save asset optimization settings.
	 */
	saveSettings() {
		this.setState( { loading: true } );

		const settings = {
			type: this.state.type,
			styles: this.state.enabled.styles,
			scripts: this.state.enabled.scripts,
			fonts: this.state.enabled.fonts,
			exclusions: this.state.exclusions,
		};

		this.props.api
			.post( 'minify_auto_save_settings', settings )
			.then( ( r ) => {
				// Automatic type has not changed.
				if (
					'undefined' !== typeof r.notice &&
					false === r.notice
				) {
					WPHB_Admin.notices.show();
				} else {
					window.wphbMixPanel.trackAOUpdated( {
						'Mode': 'undefined' === typeof r.type ? this.state.type : r.type,
						'assets_found': wphb.stats.assetsFound,
						'total_files': wphb.stats.totalFiles,
						'filesize_reductions': wphb.stats.filesizeReductions,
					} );

					WPHB_Admin.notices.show( r.notice, 'success', false );

					// Allow opening a "how-to" modal from the notice.
					const noticeLink = document.getElementById(
						'wphb-basic-hdiw-link'
					);
					if ( noticeLink ) {
						noticeLink.addEventListener( 'click', () => {
							window.SUI.closeNotice( 'wphb-ajax-update-notice' );
							window.SUI.openModal(
								'automatic-ao-hdiw-modal-content',
								'automatic-ao-hdiw-modal-expand'
							);
						} );
					}
				}

				const type =
					'undefined' === typeof r.type ? this.state.type : r.type;

				this.setState( {
					loading: false,
					type,
					assets: r.assets,
					enabled: r.enabled,
					exclusions: r.exclusions,
				} );
			} )
			.catch( ( error ) => window.console.log( error ) );
	}

	/**
	 * Speedy view toggle.
	 *
	 * @return {JSX.Element}  BoxBuilderField element.
	 */
	speedyView() {
		return (
			<BoxBuilderField
				actions={
					<Toggle
						checked={ 'speedy' === this.state.type }
						onChange={ this.handleToggleChange }
						data-type="speedy"
					/>
				}
				class={ classNames( { 'wphb-close-section': 'basic' === this.state.type } ) }
				description={ __( 'Speedy Optimization goes beyond just compressing your files. It also auto-combines smaller files together to help decrease the number of requests made when a page is loaded, and automatic font optimization will speed up the delivery of fonts to improve your site score.', 'wphb' ) }
				label={
					<React.Fragment>
						<Icon classes="sui-icon-hummingbird" />
						<strong>{ __( 'Speedy', 'wphb' ) }</strong>
						<Tag value={ __( 'Recommended', 'wphb' ) } type="sm" />
					</React.Fragment>
				}
			/>
		);
	}

	/**
	 * Basic view toggle.
	 *
	 * @return {JSX.Element}  BoxBuilderField element.
	 */
	basicView() {
		return (
			<BoxBuilderField
				actions={
					<Toggle
						checked={ 'basic' === this.state.type }
						onChange={ this.handleToggleChange }
						data-type="basic"
					/>
				}
				class={ classNames( {
					'wphb-close-section': 'speedy' === this.state.type,
				} ) }
				description={ __( 'Basic Optimization will optimize your files by compressing them. This helps to improve site speed by de-cluttering CSS and JavaScript files, and by generating a faster version of each file.', 'wphb' ) }
				label={
					<React.Fragment>
						<Icon classes="sui-icon-speed-optimize" />
						<strong>{ __( 'Basic', 'wphb' ) }</strong>
					</React.Fragment>
				}
			/>
		);
	}

	/**
	 * Render component.
	 *
	 * @return {JSX.Element}  Assets component.
	 */
	render() {
		return (
			<React.Fragment>
				<Assets
					loading={ this.state.loading }
					mode={ this.props.mode }
					clearCache={ this.props.clearCache }
					reCheckFiles={ this.props.reCheckFiles }
					showModal={ this.props.showModal }
					content={
						<React.Fragment>
							{ this.speedyView() }
							{ this.basicView() }
						</React.Fragment>
					}
				/>
				<Configurations
					loading={ this.state.loading }
					resetSettings={ this.resetSettings }
					saveSettings={ this.saveSettings }
					onEnabledChange={ this.updateCheckBox }
					updateExclusions={ this.updateExclusions }
					assets={ this.state.assets }
					enabled={ this.state.enabled }
					exclusions={ this.state.exclusions }
					view={ this.state.type }
				/>
			</React.Fragment>
		);
	}
}
