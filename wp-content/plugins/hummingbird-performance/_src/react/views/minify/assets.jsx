/* global SUI */

/**
 * External dependencies
 */
import React from 'react';

/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;

/**
 * Internal dependencies
 */
import './assets.scss';
import Action from '../../components/sui-box/action';
import Box from '../../components/sui-box';
import Button from '../../components/sui-button';
import Tooltip from '../../components/sui-tooltip';
import Tabs from '../../components/sui-tabs';
import BoxBuilder from '../../components/sui-box-builder';

/**
 * Assets component.
 *
 * @since 2.7.2
 */
export default class Assets extends React.Component {
	/**
	 * Component constructor.
	 *
	 * @param {Object} props
	 */
	constructor( props ) {
		super( props );
		this.onTabClick = this.onTabClick.bind( this );
		this.showHowDoesItWork = this.showHowDoesItWork.bind( this );
	}

	/**
	 * Component header.
	 *
	 * @return {JSX.Element}  Header action buttons.
	 */
	getHeaderActions() {
		const buttons = (
			<React.Fragment>
				<Tooltip classes="sui-tooltip-constrained" text={ __( 'Added/removed plugins or themes? Update your file list to include new files, and remove old ones', 'wphb' ) }>
					<Button
						text={ __( 'Re-Check Files', 'wphb' ) }
						classes={ [ 'sui-button', 'sui-button-ghost' ] }
						icon="sui-icon-update"
						onClick={ this.props.reCheckFiles }
					/>
				</Tooltip>
				<Tooltip classes={ [ 'sui-tooltip-constrained', 'sui-tooltip-top-right' ] } text={ __( 'Clears all local or hosted assets and recompresses files that need it', 'wphb' ) }>
					<Button
						text={ __( 'Clear cache', 'wphb' ) }
						classes="sui-button"
						onClick={ this.props.clearCache }
					/>
				</Tooltip>
			</React.Fragment>
		);

		return <Action type="right" content={ buttons } />;
	}

	/**
	 * Show "How does it work" modal.
	 */
	showHowDoesItWork() {
		let current = 'auto';
		let other = 'manual';
		let mode = 'automatic';

		if ( 'advanced' === this.props.mode ) {
			current = 'manual';
			other = 'auto';
			mode = 'manual';
		}

		// Reset tab selection.
		const label = document.getElementById( 'hdw-' + current + '-trigger-label' );
		if ( label ) {
			label.classList.add( 'active' );
			document
				.getElementById( 'hdw-' + other + '-trigger-label' )
				.classList.remove( 'active' );
		}

		SUI.openModal(
			mode + '-ao-hdiw-modal-content',
			'wphb-basic-hdiw-link'
		);
	}

	/**
	 * Handle "Automatic"/"Manual" button click.
	 *
	 * @param {Object} e
	 */
	onTabClick( e ) {
		if ( 'manual-tab' === e.target.id && 'advanced' === this.props.mode ) {
			return;
		}

		if ( 'auto-tab' === e.target.id && 'basic' === this.props.mode ) {
			return;
		}

		const type = 'advanced' === this.props.mode ? 'basic' : 'advanced';

		if ( this.props.showModal ) {
			SUI.openModal(
				'wphb-' + type + '-minification-modal',
				'wphb-switch-to-' + type
			);
		} else {
			window.WPHB_Admin.minification.switchView( type );
		}
	}

	/**
	 * Component body.
	 *
	 * @param {JSX.Element} content Content.
	 *
	 * @return {JSX.Element}  Content.
	 */
	getContent( content ) {
		const sideTabs = [
			{
				title: __( 'Automatic', 'wphb' ),
				id: 'auto',
				checked: 'basic' === this.props.mode,
				onClick: this.onTabClick
			},
			{
				title: __( 'Manual', 'wphb' ),
				id: 'manual',
				checked: 'advanced' === this.props.mode,
				onClick: this.onTabClick
			},
		];

		return (
			<React.Fragment>
				<p>
					{ __(
						'Optimizing your assets will compress and organize them in a way that improves page load times. You can choose to use our automated options, or manually configure each file yourself.',
						'wphb'
					) }
				</p>

				<div className="sui-actions">
					<Button
						text={ __( 'How Does it Work?', 'wphb' ) }
						icon="sui-icon-info"
						id="wphb-basic-hdiw-link"
						onClick={ this.showHowDoesItWork }
					/>
				</div>

				<div id="wphb-optimization-type-selector">
					<Tabs menu={sideTabs} sideTabs="true"/>
				</div>

				{ 'advanced' === this.props.mode &&
					<p className="sui-description">
						{ __( 'Manually configure your optimization settings (compress, combine, move, inline, defer, async, and preload) and then publish your changes.', 'wphb' ) }
					</p> }

				<BoxBuilder flushed={ true } fields={ content } />
			</React.Fragment>
		);
	}

	/**
	 * Render component.
	 *
	 * @return {JSX.Element}  Assets component.
	 */
	render() {
		const type = 'advanced' === this.props.mode ? 'manual' : 'auto';

		return (
			<Box
				boxClass={ 'box-minification-assets-' + type }
				loading={ this.props.loading }
				title={ __( 'Assets Optimization', 'wphb' ) }
				headerActions={ this.getHeaderActions() }
				content={ this.getContent( this.props.content ) }
			/>
		);
	}
}
