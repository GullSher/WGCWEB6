/* global WPHB_Admin */

/**
 * External dependencies
 */
import React, {useEffect, useState} from 'react';
import classNames from 'classnames';
import './assets-manual.scss';
import {createInterpolateElement} from "@wordpress/element";
import {dispatch, useSelect} from '@wordpress/data';

/**
 * Internal dependencies
 */
import {STORE_NAME} from '../../data/minify';
import {getString} from '../../../js/utils/helpers';
import Assets from './assets';
import {MinifyAsset} from '../../components/minify-asset';
import Notice from '../../components/sui-notice';
import Button from '../../components/sui-button';
import SupportLink from '../../components/support-link';
import Checkbox from '../../components/sui-checkbox';
import Box from '../../components/sui-box';
import Action from '../../components/sui-box/action';
import Tabs from '../../components/sui-tabs';
import Select from '../../components/sui-select';
import Input from '../../components/sui-input';
import Modal from '../../components/sui-modal';
import Tooltip from '../../components/sui-tooltip';
import Icon from "../../components/sui-icon";
import Toggle from "../../components/sui-toggle";

const cloneDeep = require( 'clone-deep' );

/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;

const defaultOptions = {
	async: { scripts: [] },
	block: { scripts: [], styles: [] },
	defer: { scripts: [] },
	dont_combine: { scripts: [], styles: [] },
	dont_minify: { scripts: [], styles: [] },
	fonts: [],
	inline: { styles: [] },
	position: { scripts: [], styles: [] },
	preload: { scripts: [], styles: [] },
};

const defaultBulkOptions = { async: false, defer: false, dont_combine: false, dont_minify: false, inline: false, position: false, preload: false };

const defaultFilters = { primary: '', secondary: '', type: 'all' };

const defaultSelection = { fonts: [], scripts: [], styles: [] };

/**
 * ManualAssets component.
 *
 * @since 3.4.0
 *
 * @param {Object} props
 * @return {JSX.Element} ManualAssets
 * @function
 */
export const ManualAssets = ( props ) => {
	const [ loading, setLoading ] = useState( props.loading );
	const [ hasOptions, setHasOptions ] = useState( false );
	const [ options, setOptions ] = useState( defaultOptions );
	const [ safeModeOptions, setSafeModeOptions ] = useState( defaultOptions );
	const [ bulk, setBulk ] = useState( defaultBulkOptions );
	const [ selected, setSelected ] = useState( defaultSelection );
	const [ showFilters, setShowFilters ] = useState( false );
	const [ filters, setFilters ] = useState( defaultFilters );
	const [ selectedOptions, setSelectedOptions ] = useState( {} );
	const [ initialized, setInitialized ] = useState( false );
	const { safeMode, collection, hasResolved, isResolving } = useSelect( ( select ) => {
		return {
			safeMode: select( STORE_NAME ).getOption( 'safeMode' ),
			collection: select( STORE_NAME ).getAssets(),
			hasResolved: select( STORE_NAME ).hasFinishedResolution( 'getAssets' ),
			isResolving: select( STORE_NAME ).hasStartedResolution( 'getAssets' ),
		};
	}, [] );

	/**
	 * Set loading state, when data store data has resolved.
	 */
	useEffect( () => {
		props.api
			.post( 'minify_manual_status' )
			.then( ( response ) => updateStateFromApiResponse( response ) )
			.catch( window.console.log );
	}, [] );

	useEffect(() => {
		if (getQueryParam('safe_mode_status') === 'published') {
			WPHB_Admin.notices.show(__('Your safe mode changes have been published and are now live!', 'wphb'), 'success');
			removeQueryParam('safe_mode_status');
		}

		if (!loading && getQueryParam('action') === 'disable_safe_mode') {
			if (hasSafeModeChanges()) {
				showSafeModeConfirmationModal();
			} else {
				discardSafeModeSettings().then(() => {
					WPHB_Admin.notices.show(__('Safe mode has been disabled!', 'wphb'), 'success');
				});
			}
			removeQueryParam('action');
		}

	}, [initialized, loading]);

	const getQueryParam = (name) => {
		const searchParams = location.search;
		const params = new URLSearchParams(searchParams);
		return params.get(name);
	}

	const removeQueryParam = (name) => {
		const searchParams = location.search;
		const params = new URLSearchParams(searchParams);
		if (!params.get(name)) {
			return;
		}

		params.delete(name);
		const newURL = location.href.replace(searchParams, '?' + params.toString());

		history.replaceState({}, "", newURL);
	}

	/**
	 * Set loading state, when data store data has resolved.
	 */
	useEffect( () => {
		if ( loading && hasResolved && hasOptions ) {
			setLoading( false );
		}
	}, [ hasResolved, hasOptions, isResolving ] );

	function getOptions() {
		return safeMode ? safeModeOptions : options;
	}

	function updateOptions(options) {
		if (safeMode) {
			setSafeModeOptions(options);
		} else {
			setOptions(options);
		}
	}

	const saveSettings = (action) => {
		setLoading( true );
		return props.api
			.post( action, getOptions() )
			.then( ( response ) => {
				dispatch( STORE_NAME ).invalidateResolution( 'getAssets' );
				updateStateFromApiResponse( response );
				setSelected( defaultSelection );
				setLoading( false );
			} )
			.catch( window.console.log );
	};

	const publishLiveSettings = () => {
		saveSettings('minify_manual_save_settings').then(() => {
			window.scrollTo(0, 0);
			WPHB_Admin.notices.show(getString('aoSettingsSaved')); // eslint-disable-line camelcase
		});
	};

	const saveAndPreviewSafeModeSettings = () => {
		saveSettings('minify_save_safe_mode_settings').then(() => {
			showSafeMode();
		});
	}

	const discardSafeModeSettings = () => {
		return saveSettings('minify_discard_safe_mode').then(() => {
			dispatch(STORE_NAME).invalidateResolution('getOptions');
		});
	};

	const publishSafeModeSettings = () => {
		return saveSettings('minify_save_and_publish_safe_mode').then(() => {
			dispatch(STORE_NAME).invalidateResolution('getOptions');
		});
	};

	/**
	 * Update state from API response.
	 *
	 * @param {Object} response
	 */
	const updateStateFromApiResponse = ( response ) => {
		setOptions( response.options );
		setSafeModeOptions(response.safe_mode_options);
		setSelectedOptions( response.options );
		setHasOptions( true );
	};

	/**
	 * Check if there are pending updates for selected options.
	 *
	 * @return {boolean} If there are updates.`
	 */
	const hasUpdates = () => {
		return JSON.stringify( selectedOptions ) !== JSON.stringify( getOptions() );
	};

	const hasSafeModeChanges = () => {
		return JSON.stringify(options) !== JSON.stringify(safeModeOptions);
	}

	/**
	 * Show bulk update modal.
	 */
	const showBulkModal = () => {
		window.SUI.openModal(
			'modal-bulk-update',
			'wrap-wphb-minify',
			undefined,
			true
		);
	};

	const showSafeModeConfirmationModal = () => {
		window.SUI.openModal(
			'modal-safe-mode-confirmation',
			'wrap-wphb-minify',
			undefined,
			true
		);
	};

	/**
	 * Process checkboxes on bulk update modal.
	 *
	 * @param {Object} e
	 */
	const bulkChangeCheckbox = ( e ) => {
		const type = e.target.id.slice( 12 ); // Remove 'wphb-filter-' from ID.
		setBulk( { ...bulk, [ type ]: ! bulk[ type ] } );
	};

	/**
	 * Is the action allowed for a specific file type.
	 *
	 * Async and defer are not allowed for styles.
	 * Inline is not available for scripts.
	 *
	 * @param {string} action
	 * @param {string} type
	 * @return {boolean} Is allowed?
	 */
	const actionAllowedForType = ( action, type ) => {
		if ( 'styles' === type && [ 'async', 'defer', 'fonts' ].includes( action ) ) {
			return false;
		}

		if ( 'scripts' === type && [ 'inline', 'fonts' ].includes( action ) ) {
			return false;
		}

		return ! ( 'fonts' === type && [ 'dont_minify', 'dont_combine', 'defer', 'async', 'preload' ].includes( action ) );
	};

	/**
	 * Apply bulk selection.
	 */
	const applyBulk = () => {
		window.SUI.closeModal();

		const newOptions = cloneDeep( getOptions() );

		for ( const type in selected ) {
			if ( 0 === selected[ type ].length ) {
				continue; // If no items are selected, skip.
			}

			for ( const action in newOptions ) {
				if ( 'block' === action ) {
					continue; // Block is not available on bulk updates.
				}

				if ( ! actionAllowedForType( action, type ) ) {
					continue; // Skip options that are not relevant for a specific asset type.
				}

				const isReverseOption = window.lodash.includes( [ 'dont_minify', 'dont_combine' ], action );
				if ( ( ! bulk[ action ] && ! isReverseOption ) || ( isReverseOption && bulk[ action ] ) ) {
					newOptions[ action ][ type ] = window.lodash.difference( newOptions[ action ][ type ], selected[ type ] );
				} else {
					newOptions[ action ][ type ] = window.lodash.union( newOptions[ action ][ type ], selected[ type ] );
				}
			}
		}

		updateOptions( newOptions );
		setBulk( defaultBulkOptions );
	};

	/**
	 * Bulk update modal.
	 *
	 * @return {JSX.Element} Modal element.
	 */
	const bulkUpdateModal = () => {
		const hasCSS = selected.styles.length > 0;
		const hasJS = selected.scripts.length > 0;

		const content = (
			<div className="sui-row">
				<div className="sui-col">
					<div className="sui-form-field">
						<Checkbox id="filter-dont_minify" label={ __( 'Compress', 'wphb' ) } stacked={ true } checked={ bulk.dont_minify } onChange={ bulkChangeCheckbox } />
						<Checkbox id="filter-dont_combine" label={ __( 'Combine', 'wphb' ) } stacked={ true } checked={ bulk.dont_combine } onChange={ bulkChangeCheckbox } />
						{ hasCSS && <Checkbox id="filter-inline" label={ __( 'Inline', 'wphb' ) } stacked={ true } checked={ bulk.inline } onChange={ bulkChangeCheckbox } /> }
						{ hasJS && <Checkbox id="filter-async" label={ __( 'Async', 'wphb' ) } stacked={ true } checked={ bulk.async } onChange={ bulkChangeCheckbox } /> }
					</div>
				</div>
				<div className="sui-col">
					<div className="sui-form-field">
						<Checkbox id="filter-position" label={ __( 'Move to Footer', 'wphb' ) } stacked={ true } checked={ bulk.position } onChange={ bulkChangeCheckbox } />
						<Checkbox id="filter-preload" label={ __( 'Preload', 'wphb' ) } stacked={ true } checked={ bulk.preload } onChange={ bulkChangeCheckbox } />
						{ hasJS && <Checkbox id="filter-defer" label={ __( 'Defer', 'wphb' ) } stacked={ true } checked={ bulk.defer } onChange={ bulkChangeCheckbox } /> }
					</div>
				</div>
			</div>
		);

		const footer = (
			<Button
				onClick={ applyBulk }
				type="button"
				classes="sui-button"
				text={ __( 'Apply', 'wphb' ) } /> );

		return (
			<Modal
				id="bulk-update"
				size="sm"
				title={ __( 'Bulk update', 'wphb' ) }
				isMember={ props.isMember }
				description={ __( 'Choose what bulk update actions you’d like to apply to the selected files. You still have to publish your changes before they will be set live.', 'wphb' ) }
				content={ content }
				footerBtn={ footer }
			/>
		);
	};

	const safeModeConfirmationModal = () => {
		const footer = <>
			<Button
				onClick={() => {
					discardSafeModeSettings().then(() => {
						window.SUI.closeModal();
					});
				}}
				type="button"
				classes="sui-button sui-button-ghost sui-button-red"
				icon="sui-icon-undo"
				disabled={loading}
				text={__('Discard', 'wphb')}/>

			<Button
				onClick={() => {
					publishSafeModeSettings().then(() => {
						window.SUI.closeModal();
					});
				}}
				type="button"
				classes="sui-button sui-button-blue"
				icon="sui-icon-check"
				disabled={loading}
				text={__('Publish', 'wphb')}/>
		</>;

		return (
			<Modal
				id="safe-mode-confirmation"
				size="sm"
				title={__('Unpublished changes', 'wphb')}
				description={__('There are unpublished changes made in safe mode. Do you want to publish the changes to live or discard them?', 'wphb')}
				footer={footer}
			/>
		);
	};

	/**
	 * Process settings update for a selected asse.
	 *
	 * @param {Object} e
	 */
	const onSettingChange = ( e ) => {
		const action = e.target.dataset.action;
		const options = getOptions();
		if ( ! window.lodash.has( options, action ) ) {
			return;
		}

		let type = e.target.dataset.type;
		if ( 'fonts' === type && 'fonts' !== action ) {
			type = 'styles'; // Fonts (except the "Optimize fonts" option) are still part of styles.
		}

		const assets = 'fonts' === action ? options[ action ] : options[ action ][ type ];
		const isSet = window.lodash.includes( assets, e.target.dataset.handle );
		const isReverseOption = window.lodash.includes( [ 'dont_minify', 'dont_combine' ], action );

		let newOption;
		if ( ! isSet && ( e.target.checked || ( isReverseOption && ! e.target.checked ) ) ) {
			newOption = [ ...assets, e.target.dataset.handle ];
		} else {
			let key = assets.indexOf( e.target.dataset.handle );
			newOption = [ ...assets.slice( 0, key ), ...assets.slice( ++key ) ];
		}

		// Make sure we remove selection on disabled assets.
		if ( 'block' === action && ! isSet && window.lodash.includes( selected[ type ], e.target.dataset.handle ) ) {
			let key = selected[ type ].indexOf( e.target.dataset.handle );
			const newSelection = [ ...selected[ type ].slice( 0, key ), ...selected[ type ].slice( ++key ) ];
			setSelected( { ...selected, [ type ]: newSelection } );
		}

		if ( 'fonts' !== action ) {
			newOption = { ...options[ action ], [ type ]: newOption };
		}

		updateOptions({...options, [action]: newOption});
	};

	/**
	 * Regenerate individual asset.
	 *
	 * @param {string} handle
	 * @param {string} type
	 */
	const onRegenerateClick = ( handle, type ) => {
		props.api
			.post( 'minify_regenerate_asset', { handle, type } )
			.then( () => dispatch( STORE_NAME ).invalidateResolution( 'getAssets' ) )
			.catch( window.console.log );
	};

	/**
	 * Check if queue is empty.
	 *
	 * @return {boolean}  True if queue is empty.
	 */
	const isEmptyQueue = () => {
		if ( undefined === collection || undefined === collection.scripts || undefined === collection.styles ) {
			return true;
		}

		const noScripts = 0 === Object.entries( collection.scripts ).length;
		const noStyles = 0 === Object.entries( collection.styles ).length;

		return noScripts && noStyles;
	};

	/**
	 * Show safe mode.
	 */
	const showSafeMode = () => {
		window.location.href = props.links.safeMode;
	};

	/**
	 * Toggle filters.
	 */
	const toggleFilter = () => {
		setShowFilters( ! showFilters );

		if ( showFilters ) {
			document.getElementById( 'wphb-toggle-filter' ).blur();
		}
	};

	/**
	 * "Display files" filter.
	 *
	 * @param {Object} e
	 */
	const setTypeFilter = ( e ) => {
		const newFilters = { ...filters };
		newFilters.type = e.target.id.substring( 0, e.target.id.length - 4 ); // Remove "-tab" from ID.

		setFilters( newFilters );
	};

	/**
	 * Primary text input filter.
	 *
	 * @param {Object} e
	 */
	const setPrimaryFilter = ( e ) => {
		const newFilters = { ...filters };
		newFilters.primary = e.target.value;

		setFilters( newFilters );
	};

	/**
	 * "Sort by" select filter.
	 *
	 * @param {Object} e
	 */
	const setSecondaryFilter = ( e ) => {
		const newFilters = { ...filters };
		newFilters.secondary = e.target.value;

		setFilters( newFilters );
	};

	/**
	 * Clear filters.
	 */
	const clearFilters = () => {
		// Clear select.
		jQuery( '#plugin-select-filter' ).val( null ).trigger( 'change' );
		setFilters( defaultFilters );
	};

	function activateSafeMode() {
		setLoading(true);
		props.api.post('minify_activate_safe_mode', getOptions())
			.then((response) => {
				dispatch(STORE_NAME).invalidateResolution('getOptions');
				window.wphbMixPanel.trackAOUpdated( {
					'Mode': 'safe',
					'assets_found': wphb.stats.assetsFound,
					'total_files': wphb.stats.totalFiles,
					'filesize_reductions': wphb.stats.filesizeReductions,
				} );
				updateStateFromApiResponse( response );
				setLoading(false);
			})
			.catch(window.console.log);
	}

	/**
	 * Toggle safe mode.
	 *
	 * @since 3.4.0
	 *
	 * @param {Object} e
	 */
	const toggleSafeMode = ( e ) => {
		const enableMode = e.target.checked;

		if(enableMode) {
			activateSafeMode();
		} else {
			if (hasSafeModeChanges()) {
				showSafeModeConfirmationModal();
			} else {
				discardSafeModeSettings();
			}
		}
	};

	/**
	 * Sticky header.
	 *
	 * @return {JSX.Element} Header elements.
	 */
	const stickyHeader = () => {
		const safeModeElement = (
			<React.Fragment>
				<Tooltip classes="sui-tooltip-left sui-tooltip-constrained" text={ __( "Test different settings in a safe environment without affecting visitors' experience. The changes done in the safe mode will be only visible to you (as an admin).", 'wphb' ) }>
					<Icon classes="sui-icon-info sui-md" />
				</Tooltip>
				{ __( 'Safe mode', 'wphb' ) }
				<div className="wphb-safemode-toggle">
					<Toggle checked={safeMode} onChange={toggleSafeMode}/>
				</div>
			</React.Fragment>
		);

		const filterBtn = <Button
			type="button"
			id="wphb-toggle-filter"
			onClick={ toggleFilter }
			classes={ [ 'sui-button-icon', 'sui-button-outlined' ] }
			icon="sui-icon-filter sui-md" />;

		const sideTabs = [
			{
				title: __( 'All', 'wphb' ),
				id: 'all',
				checked: 'all' === filters.type,
				onClick: setTypeFilter
			},
			{
				title: __( 'Hosted', 'wphb' ),
				id: 'local',
				checked: 'local' === filters.type,
				onClick: setTypeFilter
			},
			{
				title: __( 'External', 'wphb' ),
				id: 'external',
				checked: 'external' === filters.type,
				onClick: setTypeFilter
			},
		];

		const safeModeSaveButton =
			<Tooltip classes="sui-tooltip-right sui-tooltip-constrained"
					 text={__("Preview your changes on the front-end, then publish to live if no errors are found.", 'wphb')}>
				<Button
					onClick={saveAndPreviewSafeModeSettings}
					type="button"
					icon="sui-icon-eye"
					classes={['sui-button']}
					text={__('Preview', 'wphb')}
				/>
			</Tooltip>;
		const publishButton = <Button
			onClick={publishLiveSettings}
			type="button"
			classes={['sui-button', 'sui-button-blue', {disabled: !hasUpdates()}]}
			text={__('Publish changes', 'wphb')}
		/>;

		return (
			<Box
				stickyType = { true }	
				showFilters = { showFilters }
				boxClass={ classNames( 'sui-box-sticky', { 'wphb-expanded': showFilters } ) }
				headerActions={
					<React.Fragment>
						<div className="sui-status">
							<Button
								onClick={ showBulkModal }
								type="button"
								classes={ classNames( 'sui-button sui-button-ghost', { disabled: ! hasSelection() } ) }
								text={ __( 'Bulk update', 'wphb' ) } />

							{safeMode && safeModeSaveButton}
							{!safeMode && publishButton}
						</div>
						<Action type="right" content={
							<React.Fragment>
								{ 'advanced' === props.mode ? safeModeElement : false }
								{ filterBtn }
							</React.Fragment>
						} />
					</React.Fragment>
				}
				content={
					<React.Fragment>
						<label htmlFor="wphb-secondary-filter" className="sui-label">
							{ __( 'Display files', 'wphb' ) }
						</label>

						<Tabs menu={ sideTabs } sideTabs="true" />

						<div className="sui-row">
							<div className="sui-col-md-6">
								<Select
									onChange={ setSecondaryFilter }
									selectId="plugin-select-filter"
									classes="sui-select-lg"
									selected={ filters.secondary }
									items={ Object.entries( props.filters ) }
									label={ __( 'Sort by', 'wphb' ) }
									placeholder={ __( 'Choose plugin or theme', 'wphb' ) } />
							</div>
							<div className="sui-col-md-6">
								<Input
									onChange={ setPrimaryFilter }
									label="&nbsp;"
									value={ filters.primary }
									placeholder={ __( 'Search by name or extension', 'wphb' ) } />
							</div>
						</div>
					</React.Fragment>
				}
				footerActions={
					<Button
						onClick={ clearFilters }
						type="button"
						classes="sui-button"
						text={ __( 'Clear filters', 'wphb' ) } />
				}
			/>
		);
	};

	/**
	 * Hide assets based on selected filters.
	 *
	 * @param {Object} asset
	 * @return {boolean}  Show asset.
	 */
	const filterAsset = ( asset ) => {
		// Primary filters (search box).
		if ( filters.primary && ! asset.handle.includes( filters.primary ) ) {
			return false;
		}

		// Secondary filters (plugin/theme select).
		if ( filters.secondary && filters.secondary !== asset.settings.filter ) {
			return false;
		}

		// Type filters.
		if ( 'local' === filters.type && ! asset.settings.isLocal ) {
			return false;
		} else if ( 'external' === filters.type && asset.settings.isLocal ) {
			return false;
		}

		return true;
	};

	/**
	 * Process multiselect.
	 *
	 * @param {Object} e
	 */
	const selectGroup = ( e ) => {
		const type = e.target.dataset.type;
		const items = Object.keys( collection[ type ] ).filter( ( item ) => {
			// Remove disabled items.
			return ! window.lodash.includes( getOptions().block[ type ], item );
		} );

		let newSelection;
		if ( items.length === selected[ type ].length ) {
			newSelection = { ...selected, [ type ]: [] };
		} else {
			newSelection = { ...selected, [ type ]: items };
		}

		setSelected( newSelection );
	};

	/**
	 * Handle select checkboxes.
	 *
	 * @param {Object} e
	 */
	const selectAsset = ( e ) => {
		const type = e.target.dataset.type;
		const handle = e.target.dataset.handle;

		let newOption;
		if ( window.lodash.includes( selected[ type ], handle ) ) {
			let key = selected[ type ].indexOf( handle );
			newOption = [ ...selected[ type ].slice( 0, key ), ...selected[ type ].slice( ++key ) ];
		} else {
			newOption = [ ...selected[ type ], handle ];
		}

		setSelected( { ...selected, [ type ]: newOption } );
	};

	/**
	 * Check if any assets have been selected.
	 *
	 * @return {boolean} Has selected items.
	 */
	const hasSelection = () => {
		let selectedItems = 0;
		for ( const type in selected ) {
			selectedItems += selected[ type ].length;
		}

		return selectedItems > 0;
	};

	/**
	 * Check if an option is set for an asset.
	 *
	 * @param {string} handle
	 * @param {string} type
	 * @param {string} option
	 * @param {{}} options
	 *
	 * @return {boolean} Option status.
	 */
	const isOptionSet = (handle, type, option, options) => {
		if ( 'fonts' === type && 'fonts' === option ) {
			return options.fonts.includes( handle );
		}

		if ( ! options.hasOwnProperty( option ) ) {
			return false;
		}

		// These font options are located in relevant 'styles' sections.
		if ( 'fonts' === type && [ 'block', 'inline', 'position' ].includes( option ) ) {
			type = 'styles';
		}

		return window.lodash.includes( options[ option ][ type ], handle );
	};

	function assetSelectedOptions(asset, type) {
		const optionsSelected = Object.keys(getOptions())
			.filter((option) => {
				return actionAllowedForType(option, type);
			})
			.map((option) => {
				return [option, isOptionSet(asset.handle, type, option, getOptions())];
			});

		return window.lodash.fromPairs(optionsSelected);
	}

	/**
	 * Generate asset.
	 *
	 * @param {Object} asset
	 * @param {string} type
	 * @return {JSX.Element} Minify asset.
	 */
	const generateAsset = ( asset, type ) => {
		const selectedAssets = window.lodash.includes( selected[ type ], asset.handle );

		// Get options only for the selected asset.
		const optionsSelected = assetSelectedOptions(asset, type);
		const highlightedActions = assetHighlightedActions(asset, type);

		return (
			<MinifyAsset
				key={ asset.handle }
				handle={ asset.handle }
				src={ asset.src }
				originalSize={ asset.originalSize }
				compressedSize={ asset.compressedSize }
				fileUrl={asset.fileUrl}
				onSettingChange={ onSettingChange }
				onRegenerateClick={ onRegenerateClick }
				selected={ selectedAssets }
				onAssetSelect={ selectAsset }
				settings={ asset.settings }
				highlighted={highlightedActions}
				options={ optionsSelected }
				type={ type }
				safeMode={ safeMode } />
		);
	};

	const assetHighlightedActions = (asset, type) => {
		if (!safeMode) {
			return {};
		}

		let safeModeChanges = Object.keys(options)
			.filter((option) => {
				return actionAllowedForType(option, type);
			})
			.map((option) => {
				const setInOptions = isOptionSet(asset.handle, type, option, options);
				const setInSafeModeOptions = isOptionSet(asset.handle, type, option, safeModeOptions);

				return [option, setInOptions !== setInSafeModeOptions];
			});

		return window.lodash.fromPairs(safeModeChanges);
	}

	let content;

	/**
	 * When any action is performed on the child `MinifyAsset` component, for example, re-generate individual asset, it
	 * will invalidate the `getAssets` Redux resolver with `invalidateResolution( 'getAssets' )`. In turn, this will trigger
	 * the `! hasResolved` check to fire and will set the `content` variable as empty. This causes the `MinifyAsset`
	 * component, which is called in the `generateAsset()` call below, to unmount and mount again.
	 */
	if ( ! hasOptions || ! hasResolved ) {
		content = '';
	} else if ( isEmptyQueue() ) {
		const message = __( "We've completed the file check but haven't been able to load the files. Please try clearing your object cache, refresh the page and wait a few seconds to load the files, or visit your homepage to trigger the file list to show.", 'wphb' );
		content = <Notice
			message={ message }
			classes="sui-notice-info"
			content={
				<React.Fragment>
					<SupportLink
						noFormatting={ true }
						isMember={ props.isMember }
						forumLink={ props.links.support.forum }
						chatLink={ props.links.support.chat } />
					<br />
					<Button
						style={ { marginTop: '10px' } }
						url={ props.links.site }
						target="_blank"
						text={ __( 'Visit homepage', 'wphb' ) }
						classes={ [ 'sui-button', 'sui-button-blue' ] } />
				</React.Fragment>
			}
		/>;
	} else {
		const styles = Object.entries( collection.styles )
			.filter( ( asset ) => filterAsset( asset[ 1 ] ) )
			.map( ( asset ) => generateAsset( asset[ 1 ], 'styles' ) );

		const scripts = Object.entries( collection.scripts )
			.filter( ( asset ) => filterAsset( asset[ 1 ] ) )
			.map( ( asset ) => generateAsset( asset[ 1 ], 'scripts' ) );

		let fonts = ''; // Fonts are not always defined on the REST API.
		if ( undefined !== collection.fonts ) {
			fonts = Object.entries( collection.fonts )
				.filter( ( asset ) => filterAsset( asset[ 1 ] ) )
				.map( ( asset ) => generateAsset( asset[ 1 ], 'fonts' ) );
		}

		content = <React.Fragment>
			{ stickyHeader() }

			{safeMode &&
				<Notice
					classes="sui-notice-warning"
					content={createInterpolateElement(__("You are currently using <strong>safe mode</strong> which enables you to test different settings without affecting your website visitors' experience. You can update the assets, and preview the changes in the frontend of your website to check for any errors in your browser's console or broken UI. When no issues are found, publish your changes to live.<span><strong>Note:</strong> Asset minification is disabled while safe mode is active, which can cause slower page load times. We recommend exiting safe mode or publishing the changes you've made as soon as possible to avoid page load issues.</span>", 'wphb'), {
						'strong': <strong/>,
						'span': <span style={{marginTop: "10px", display: "block"}} />,
					})}
				/>
			}
			
			<Checkbox
				id="bulk-file-css"
				label="CSS"
				size="sm"
				data={ { 'data-type': 'styles' } }
				checked={ Object.keys( collection.styles ).length === selected.styles.length }
				onChange={ selectGroup } />
			{ styles }
			<Checkbox
				id="bulk-file-js"
				label="JavaScript"
				size="sm"
				data={ { 'data-type': 'scripts' } }
				checked={ Object.keys( collection.scripts ).length === selected.scripts.length }
				onChange={ selectGroup } />
			{ scripts }
			{ fonts.length > 0 &&
				<Checkbox
					id="bulk-file-fonts"
					label={ __( 'Fonts', 'wphb' ) }
					size="sm"
					data={ { 'data-type': 'fonts' } }
					disabled={ true } /> }
			{ fonts }
		</React.Fragment>;
	}

	return (
		<React.Fragment>
			{bulkUpdateModal()}
			{safeModeConfirmationModal()}
			<Assets
				loading={ loading }
				mode={ props.mode }
				clearCache={ props.clearCache }
				reCheckFiles={ props.reCheckFiles }
				showModal={ props.showModal }
				content={ content } />
		</React.Fragment>
	);
};
