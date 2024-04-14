/**
 * External dependencies
 */
import React, {useEffect, useState} from 'react';
import classNames from 'classnames';
import {getFilename} from '@wordpress/url';

/**
 * Internal dependencies
 */
import './style.scss';
import Icon from '../sui-icon';
import Checkbox from '../sui-checkbox';
import Button from '../sui-button';
import Tooltip from '../sui-tooltip';
import Toggle from '../sui-toggle';
import {createInterpolateElement} from "@wordpress/element";

/**
 * WordPress dependencies
 */
const { __, sprintf } = wp.i18n;

/**
 * MinifyAsset component.
 *
 * @since 3.4.0
 *
 * @param {Object} props
 * @return {JSX.Element} Asset div.
 * @function
 */
export const MinifyAsset = ( props ) => {
	const [ loading, setLoading ] = useState( true );
	const [ pendingUpdates, setPendingUpdates ] = useState( false );
	const [ selectedOptions, setSelectedOptions ] = useState( props.options );

	/**
	 * Update loading state on component mount.
	 */
	useEffect( () => {
		const loadingState = ! isProcessed() && ! hasMinifiedExtension() && ! ( props.options.dont_minify || hasDisableSwitch() );

		if ( loading !== loadingState ) {
			setLoading( loadingState );
		}
	}, [] );

	/**
	 * Check if there are changes on component updates.
	 */
	useEffect( () => {
		const hasPendingUpdates = ! window.lodash.isEqual( selectedOptions, props.options );

		if ( hasPendingUpdates !== pendingUpdates ) {
			setPendingUpdates( hasPendingUpdates );
		}
	}, [ props.options ] );

	/**
	 * Is asset processed by asset optimization.
	 *
	 * @return {boolean} Processed status.
	 */
	const isProcessed = () => {
		return false !== props.compressedSize && false !== props.originalSize;
	};

	/**
	 * Is asset compressed by asset optimization.
	 *
	 * @return {boolean} Compressed status.
	 */
	const isCompressed = () => {
		const isSmaller = props.compressedSize < props.originalSize;
		return isProcessed() && ( isSmaller || '0.0' === props.originalSize );
	};

	/**
	 * Asset has been processed but there are no size savings.
	 *
	 * @return {boolean} True if there are no savings.
	 */
	const hasNoSavings = () => {
		return isProcessed() && props.compressedSize === props.originalSize;
	};

	/**
	 * Check if an asset has a min.css or min.js extension.
	 *
	 * @return {boolean} True if asset has *.min.* extension.
	 */
	const hasMinifiedExtension = () => {
		const regex = /\.min\.(css|js)/g;
		return null !== props.src.match( regex );
	};

	/**
	 * Check if asset cannot be optimized.
	 *
	 * @param {string} type Action to check. Default: minify.
	 *                      Accepts: minify, combine, position, defer, async, inline, preload, include.
	 *
	 * @return {boolean} True if asset has a "disable" switch set.
	 */
	const hasDisableSwitch = ( type = 'minify' ) => {
		if ( 'object' !== typeof props.settings ) {
			return false;
		}

		if ( 'object' !== typeof props.settings.disableSwitchers ) {
			return false;
		}

		return window.lodash.includes( props.settings.disableSwitchers, type );
	};

	const getHighlighted = () => {
		return props.highlighted || {};
	};

	const hasSafeModeChanges = () => {
		return Object.values(getHighlighted()).filter(value => value).length;
	};

	/**
	 * Get asset status.
	 *
	 * @return {JSX.Element} Status with icon and tooltip.
	 */
	const getStatus = () => {
		if ( 'fonts' === props.type ) {
			return null;
		}

		let icon;
		let tooltip;

		if( props.safeMode ) {
			if (hasSafeModeChanges()) {
				icon = 'sui-icon-info wphb-asset-status-safe-mode';
				tooltip = __('Preview your changes in Safe Mode and check for any errors on the front-end. If none are found, click Publish to make your changes live.', 'wphb');
			} else {
				return null;
			}
		} else {
			if ( loading ) {
				icon = 'sui-icon-loader sui-loading';
				tooltip = __( 'This file is queued for compression. It will get optimized when someone visits a page that requires it.', 'wphb' );
			} else if ( pendingUpdates ) {
				icon = 'sui-icon-update';
				tooltip = __( 'You need to publish your changes for your new settings to take effect', 'wphb' );
			} else if ( isCompressed() || hasNoSavings() || hasMinifiedExtension() ) {
				icon = 'sui-icon-check-tick';
				tooltip = __( 'This file has been optimized', 'wphb' );
			} else if ( 'OTHER' === props.settings.extension ) {
				icon = 'sui-icon-info';
				tooltip = __( 'This file has no linked URL, it will not be combined/minified', 'wphb' );
			} else {
				return null;
			}
		}

		return (
			<Tooltip classes={ [ 'sui-tooltip-top-left', 'sui-tooltip-constrained', 'wphb-ao-asset-status' ] } text={ tooltip }>
				<Icon classes={ icon } />
			</Tooltip>
		);
	};

	/**
	 * Regenerate selected asset.
	 */
	const regenerateAsset = () => {
		setLoading( true );
		props.onRegenerateClick( props.handle, props.type );
	};

	/**
	 * Get asset icon.
	 *
	 * @param {string} type
	 *
	 * @return {JSX.Element} Asset icon.
	 */
	const getAssetIcon = ( type ) => {
		if ( isCompressed() || hasNoSavings() || hasMinifiedExtension() ) {
			return (
				<Tooltip classes="sui-tooltip-constrained" text={ __( 'If you’ve made changes to this file, you can recompress it without resetting your file structure.', 'wp-smushit' ) }>
					<span className={ 'wphb-filename-extension wphb-filename-extension-' + type } onClick={ regenerateAsset }>
						{ type.toUpperCase() }
					</span>
				</Tooltip>
			);
		}

		return <span className={ 'wphb-filename-extension wphb-filename-extension-' + type }>{ type.toUpperCase() }</span>;
	};

	/**
	 * Build the asset info block.
	 *
	 * @return {JSX.Element} Asset info DIV.
	 */
	const getAssetInfo = () => {
		if ( 'fonts' === props.type ) {
			return (
				<div className="wphb-ao-asset-info">
					<span>{ props.handle }</span>
					<Button
						classes={ classNames( { 'wphb-minification-font-url': 'fonts' === props.type } ) }
						url={ props.src }
						target="blank"
						text={ props.src } />
				</div>
			);
		}

		let divBlock = <span>{ props.originalSize + 'KB' }</span>;
		if ( ! props.originalSize ) {
			divBlock = <span>{ __( 'Filesize Unknown', 'wphb' ) }</span>;
		} else if ( isCompressed() ) {
			const tooltip = sprintf( /* translators: %s - number of saved kb */
				__( 'This assets file size has been reduced by %sKB', 'wphb' ),
				Math.round( ( props.originalSize - props.compressedSize ) * 100 ) / 100
			);

			divBlock =
				<Tooltip classes="sui-tooltip-constrained" text={ tooltip }>
					<s>{ props.originalSize }KB</s>
					<Icon classes="sui-icon-chevron-down" />
					<span className="compressed-size">{ props.compressedSize }KB</span>
				</Tooltip>;
		}

		return (
			<div className="wphb-ao-asset-info">
				<span className="wphb-ao-asset-handle">{ props.handle }</span>
				{ divBlock }
				{ ! props.settings.component && <span className="component">&nbsp;&mdash;&nbsp;</span> }
				{ props.settings.component &&
					<span className="component">
						, { props.settings.component } &mdash; { props.settings.filter },&nbsp;
					</span> }
				<Button url={ props.src } target="blank" text={ getFilename( props.src ) } />

				{props.fileUrl ?
					<div className="wphb-ao-asset-file-url">
						<span>{__('Optimized Version -', 'wphb')}</span>
						<a href={props.fileUrl} target="_blank">{getTruncatedFileName(props.fileUrl)}</a>
					</div>
					: null}
			</div>
		);
	};

	const getTruncatedFileName = (fileUrl) => {
		const fileName = getFilename(fileUrl);
		if (!fileName) {
			return '';
		}

		const parts = fileName.split('.');
		if (!parts || parts.length < 2) {
			return '';
		}

		const exceptExtension = parts[0];
		const extension = parts[1];
		const keepLength = 5;
		const removeLength = exceptExtension.length - keepLength - keepLength;
		const html = '<span>' + exceptExtension.substring(0, keepLength) + '</span>'
			+ '<span>' + exceptExtension.substring(keepLength, removeLength + keepLength) + '</span>'
			+ '<span>' + exceptExtension.substring(removeLength + keepLength, exceptExtension.length) + '</span>'
			+ "." + extension;

		return createInterpolateElement(html, {
			span: <span className="wphb-file-name-part"/>
		});
	};

	/**
	 * Tooltip for minify action.
	 *
	 * @return {string} Tooltip.
	 */
	const getMinifyTooltip = () => {
		if ( hasDisableSwitch() && ! props.options.block ) {
			return __( 'This file type cannot be compressed and will be left alone', 'wphb' );
		} else if ( hasMinifiedExtension() || ( isProcessed() && hasNoSavings() ) ) {
			return __( 'This file is already compressed', 'wphb' );
		} else if ( ! props.options.dont_minify ) {
			return __( 'Compression is on for this file, which aims to reduce its size', 'wphb' );
		}

		return __( 'Compression is off for this file. Turn it on to reduce its size', 'wphb' );
	};

	/**
	 * Tooltip for optimize fonts action.
	 *
	 * @return {string} Tooltip.
	 */
	const getFontsTooltip = () => {
		if ( props.options.fonts ) {
			return __( 'Font is optimized.', 'wphb' );
		}

		return __( 'Font optimization is off for this file. Turn it on to optimize it.', 'wphb' );
	};

	/**
	 * Tooltip for combine action.
	 *
	 * @return {string} Tooltip.
	 */
	const getCombineTooltip = () => {
		if ( hasDisableSwitch( 'combine' ) && ! props.options.block ) {
			return __( 'This file can’t be combined', 'wphb' );
		} else if ( ! props.options.dont_combine ) {
			return __( 'Combine is on for this file, which aims to reduce server requests.', 'wphb' );
		}

		return __( 'Combine is off for this file. Turn it on to combine smaller files together.', 'wphb' );
	};

	/**
	 * Tooltip for "Move to footer" action.
	 *
	 * @return {string} Tooltip.
	 */
	const getPositionTooltip = () => {
		if ( props.options.position ) {
			return __( 'Move to footer is on for this file, which aims to speed up page load.', 'wphb' );
		}

		return __( 'Move to footer is off for this file. Turn it on to load it from the footer.', 'wphb' );
	};

	/**
	 * Tooltip for defer action.
	 *
	 * @return {string} Tooltip.
	 */
	const getDeferTooltip = () => {
		if ( props.options.defer ) {
			return __( 'This file will be loaded only after the page has rendered.', 'wphb' );
		}

		return __( 'Click to turn on the force-loading of this file after the page has rendered.', 'wphb' );
	};

	/**
	 * Tooltip for async action.
	 *
	 * @return {string} Tooltip.
	 */
	const getAsyncTooltip = () => {
		if ( props.options.async ) {
			return __( 'Async is enabled for this file, which will download the file asynchronously and execute it as soon as it’s ready. HTML parsing will be paused while the file is executed.', 'wphb' );
		}

		return __( 'Async is off for this file. Turn it on to download the file asynchronously and execute it as soon as it’s ready. HTML parsing will be paused while the file is executed.', 'wphb' );
	};

	/**
	 * Tooltip for inline action.
	 *
	 * @return {string} Tooltip.
	 */
	const getInlineTooltip = () => {
		if ( hasDisableSwitch( 'inline' ) && ! props.options.block ) {
			return __( 'This file is too large to be inlined. Limits can be overwritten with a "wphb_inline_limit_kb" filter.', 'wphb' );
		} else if ( props.options.inline ) {
			return __( 'Inline CSS is on for this file, which will add the style attributes to an HTML tag.', 'wphb' );
		}

		return __( 'Inline CSS is off for this file. Turn it on to add the style attributes to an HTML tag.', 'wphb' );
	};

	/**
	 * Tooltip for preload action.
	 *
	 * @return {string} Tooltip.
	 */
	const getPreloadTooltip = () => {
		if ( props.options.preload ) {
			return __( 'Preload is on for this file, which will download and cache the file so it is immediately available when the site is loaded.', 'wphb' );
		}

		return __( 'Preload is off for this file. Turn it on to download and cache the file so it is immediately available when the site is loaded.', 'wphb' );
	};

	/**
	 * Tooltip for disable action.
	 *
	 * @return {string} Tooltip.
	 */
	const getDisableTooltip = () => {
		if ( props.options.block ) {
			return __( 'Click to re-include', 'wphb' );
		}

		return __( "Don't load this file", 'wphb' );
	};

	/**
	 * Expand section on mobile view.
	 *
	 * @param {Object} e Target element.
	 */
	const expandSection = ( e ) => {
		if ( window.innerWidth < 783 ) {
			e.currentTarget.classList.toggle( 'open' );
		}
	};

	/**
	 * Get shorthand type.
	 *
	 * @param {string} type
	 * @return {string} Type.
	 */
	const getType = ( type ) => {
		const types = {
			styles: 'css',
			scripts: 'js',
			fonts: 'font',
		};

		return types[ type ];
	};

	const type = getType( props.type );

	const highlightClass = 'wphb-asset-action-highlighted';
	const hasHighlightSwitch = (action) => {
		const highlighted = getHighlighted();

		return highlighted.hasOwnProperty(action) && !!highlighted[action];
	};

	function getActionToggleClassName(action) {
		return classNames({
			[highlightClass]: hasHighlightSwitch(action)
		});
	}

	return (
		<div
			className={ classNames( 'sui-builder-field', 'sui-react', 'wphb-ao-asset', { disabled: props.options.block } ) }
			onClick={ expandSection }
		>
			{ getStatus() }
			<div className="sui-builder-field-label">
				<Checkbox
					id={ props.handle + '-' + type }
					checked={ props.selected }
					disabled={ 'fonts' === props.type || true === props.options.block }
					data={ { 'data-handle': props.handle, 'data-type': props.type } }
					onChange={ props.onAssetSelect } />
				{ getAssetIcon( type ) }
				{ getAssetInfo() }
			</div>

			<div className="wphb-ao-asset-actions">
				{ ( 'scripts' === props.type || 'styles' === props.type ) &&
					<Tooltip text={ getMinifyTooltip() } classes="sui-tooltip-constrained">
						<Toggle
							id={ 'minify' + '-' + props.settings.extension.toLowerCase() + '-' + props.handle }
							text={ <Icon classes="sui-icon-arrows-in" /> }
							className={getActionToggleClassName('dont_minify')}
							checked={ ! props.options.dont_minify }
							disabled={ hasDisableSwitch() || hasMinifiedExtension() }
							hideToggle="true"
							onChange={ props.onSettingChange }
							data-handle={ props.handle }
							data-action="dont_minify"
							data-type={ props.type } />
					</Tooltip> }

				{ 'fonts' === props.type &&
					<Tooltip text={ getFontsTooltip() } classes="sui-tooltip-constrained">
						<Toggle
							id={ 'font-optimize' + '-' + props.settings.extension.toLowerCase() + '-' + props.handle }
							text={ <Icon classes="sui-icon-arrows-compress" /> }
							className={getActionToggleClassName('fonts')}
							checked={ props.options.fonts }
							hideToggle="true"
							onChange={ props.onSettingChange }
							data-handle={ props.handle }
							data-action="fonts"
							data-type={ props.type } />
					</Tooltip> }

				{ ( 'scripts' === props.type || 'styles' === props.type ) &&
					<Tooltip text={ getCombineTooltip() } classes="sui-tooltip-constrained">
						<Toggle
							id={ 'combine' + '-' + props.settings.extension.toLowerCase() + '-' + props.handle }
							text={ <Icon classes="sui-icon-combine" /> }
							className={getActionToggleClassName('dont_combine')}
							checked={ ! props.options.dont_combine }
							disabled={ hasDisableSwitch( 'combine' ) }
							hideToggle="true"
							onChange={ props.onSettingChange }
							data-handle={ props.handle }
							data-action="dont_combine"
							data-type={ props.type } />
					</Tooltip> }

				<Tooltip text={ getPositionTooltip() } classes="sui-tooltip-constrained">
					<Toggle
						id={ 'position' + '-' + props.settings.extension.toLowerCase() + '-' + props.handle }
						text={ <Icon classes="sui-icon-movefooter" /> }
						className={getActionToggleClassName('position')}
						checked={ props.options.position }
						disabled={ hasDisableSwitch( 'position' ) }
						hideToggle="true"
						onChange={ props.onSettingChange }
						data-handle={ props.handle }
						data-action="position"
						data-type={ props.type } />
				</Tooltip>

				{ 'scripts' === props.type && props.settings.isLocal &&
					<Tooltip text={ getDeferTooltip() } classes="sui-tooltip-constrained">
						<Toggle
							id={ 'defer' + '-' + props.settings.extension.toLowerCase() + '-' + props.handle }
							text={ <Icon classes="sui-icon-defer" /> }
							className={getActionToggleClassName('defer')}
							checked={ props.options.defer }
							disabled={ hasDisableSwitch( 'defer' ) }
							hideToggle="true"
							onChange={ props.onSettingChange }
							data-handle={ props.handle }
							data-action="defer"
							data-type={ props.type } />
					</Tooltip> }

				{ 'scripts' === props.type && ! props.settings.isLocal &&
					<Tooltip text={ getAsyncTooltip() } classes="sui-tooltip-constrained">
						<Toggle
							id={ 'async' + '-' + props.settings.extension.toLowerCase() + '-' + props.handle }
							text={ <Icon classes="sui-icon-async" /> }
							className={getActionToggleClassName('async')}
							checked={ props.options.async }
							disabled={ hasDisableSwitch( 'async' ) }
							hideToggle="true"
							onChange={ props.onSettingChange }
							data-handle={ props.handle }
							data-action="async"
							data-type={ props.type } />
					</Tooltip> }

				{ ( 'styles' === props.type || 'fonts' === props.type ) &&
					<Tooltip text={ getInlineTooltip() } classes="sui-tooltip-constrained">
						<Toggle
							id={ 'inline' + '-' + props.settings.extension.toLowerCase() + '-' + props.handle }
							text={ <Icon classes="sui-icon-inlinecss" /> }
							className={getActionToggleClassName('inline')}
							checked={ props.options.inline }
							disabled={ hasDisableSwitch( 'inline' ) }
							hideToggle="true"
							onChange={ props.onSettingChange }
							data-handle={ props.handle }
							data-action="inline"
							data-type={ props.type } />
					</Tooltip> }

				{ ( 'scripts' === props.type || 'styles' === props.type ) &&
					<Tooltip text={ getPreloadTooltip() } classes="sui-tooltip-constrained">
						<Toggle
							id={ 'preload' + '-' + props.settings.extension.toLowerCase() + '-' + props.handle }
							text={ <Icon classes="sui-icon-update" /> }
							className={getActionToggleClassName('preload')}
							checked={ props.options.preload }
							disabled={ hasDisableSwitch( 'preload' ) }
							hideToggle="true"
							onChange={ props.onSettingChange }
							data-handle={ props.handle }
							data-action="preload"
							data-type={ props.type } />
					</Tooltip> }

				<Tooltip text={ getDisableTooltip() }>
					<Toggle
						id={ 'block' + '-' + props.settings.extension.toLowerCase() + '-' + props.handle }
						text={ <Icon classes={ props.options.block ? 'sui-icon-eye' : 'sui-icon-eye-hide' } /> }
						className={getActionToggleClassName('block')}
						checked={ props.options.block }
						hideToggle="true"
						onChange={ props.onSettingChange }
						data-handle={ props.handle }
						data-action="block"
						data-type={ props.type } />
				</Tooltip>
			</div>
		</div>
	);
};
