import Fetcher from './utils/fetcher';
import { getString } from './utils/helpers';

const { useEffect, useState } = wp.element;

const { PluginPostStatusInfo } = wp.editPost;
const { registerPlugin } = wp.plugins;
const { select, dispatch } = wp.data;
const { __ } = wp.i18n;

if ( wphb.mixpanel.enabled ) {
	require( './mixpanel' );
	window.wphbMixPanel.init();
}

const generatedNoticeId = 'critical-generated-notice';
const revertedNoticeId  = 'critical-reverted-notice';
const errorNoticeId     = 'critical-error-notice';
const noticeIdPrefix    = 'wphb-gb-';

/**
 * Handle mixpanel events.
 *
 * @param {string} eventName Event name.
 */
 const trackMixPanelEvents = ( eventName ) => {
	if ( ! wphb.mixpanel.enabled ) {
		return;
	}

	window.wphbMixPanel.trackGutenbergEvent( eventName );
};

/**
 * Handle clear cache action.
 */
const handleClearCache = () => {
	const postId = select( 'core/editor' ).getCurrentPostId();
	Fetcher.caching.clearCacheForPost( postId ).then( showNotice );
};

/**
 * Show notice.
 *
 * @param {string} key        Key for the notice.
 * @param {string} message    Message to display.
 * @param {string} noticeType Notice type.
 */
const showNotice = ( key = 'notice', message = false, noticeType = 'success' ) => {
	const notices = select( 'core/notices' ).getNotices();
	const noticeId = noticeIdPrefix + key;
	if ( ! notices.find( ( notice ) => notice.id === noticeId ) ) {
		const text = message ? message : getString( 'notice' );
		dispatch( 'core/notices' ).createNotice( noticeType, text, {
			id: noticeId,
		} );
	}
};

/**
 * Hide notice.
 */
 const hideNotice = () => {
	dispatch( 'core/notices' ).removeNotice( noticeIdPrefix + generatedNoticeId );
	dispatch( 'core/notices' ).removeNotice( noticeIdPrefix + revertedNoticeId );
	dispatch( 'core/notices' ).removeNotice( noticeIdPrefix + errorNoticeId );
};

/**
 * Add clear cache button.
 *
 * @return {*} Element
 * @class
 */
const MyPluginPostStatusInfo = () => {
	const pageCache = getString( 'pageCache' );
	const criticalCss = getString( 'criticalCss' );
	const defaultCssFile = getString( 'singlePostCriticalCSSStatus' );
	const [ singlePostCriticalStatus, setCriticalStatus ] = useState( defaultCssFile );
	const MINUTE_MS = 10000;

	useEffect( () => {
		if ( 'processing' === singlePostCriticalStatus ) {
			const interval = setInterval( () => {
				const postId = select( 'core/editor' ).getCurrentPostId();
				Fetcher.minification.getCriticalStatusForSinglePost( postId ).then( ( res ) => {
					setCriticalStatus( res?.singlePostCriticalCSSStatus );
					const singlePostCriticalDetail = res?.singlePostCriticalDetail;

					if ( 'complete' === singlePostCriticalDetail.status ) {
						const getResult = singlePostCriticalDetail.result;
						const message = 'COMPLETE' !== getResult && '' !== singlePostCriticalDetail.error_message ? singlePostCriticalDetail.error_message : __( 'Critical CSS generated successfully', 'wphb' );
						showNotice( generatedNoticeId, message, 'COMPLETE' === getResult ? 'success' : 'error' );
						if ( 'ERROR' === getResult ) {
							window.wphbMixPanel.track( 'error_encountered', {
								critical_css_error: res?.errorCode
							} );
						}
					}
				} );
			}, MINUTE_MS );

			return () => clearInterval( interval );
		}
	}, [ singlePostCriticalStatus ] );

	/**
	 * Handle to create critical css.
	 */
	const handleCreateCSS = () => {
		if ( ! getString( 'isMember' ) ) {

			if ( wphb.mixpanel.enabled ) {
				window.wphbMixPanel.trackEoUpsell( 'critical_css_upsell', 'gutenberg' );
			}
			window.open( getString( 'gutenbergUTM' ), '_blank' );

			return;
		}

		trackMixPanelEvents( 'generate' );
		hideNotice();

		const postId = select( 'core/editor' ).getCurrentPostId();
		Fetcher.minification.createCSSForPost( postId ).then( ( res ) => {
			setCriticalStatus( res?.singlePostCriticalCSSStatus );
			const message = res?.message;

			if ( 'error' === res?.singlePostCriticalCSSStatus && message ) {
				showNotice( errorNoticeId, message, 'error' );
			}
		} );
	};
	/**
	 * Handle to re-create critical css.
	 */
	const handleRecreateCSS = () => {
		const postId = select( 'core/editor' ).getCurrentPostId();

		trackMixPanelEvents( 'regenerate' );
		hideNotice();

		Fetcher.minification.reCreateCSSForPost( postId ).then( ( res ) => {
			setCriticalStatus( res?.singlePostCriticalCSSStatus );
			const message = res?.message;

			if ( 'error' === res?.singlePostCriticalCSSStatus &&  message ) {
				showNotice( errorNoticeId, message );
			}
		} );
	};
	/**
	 * Handle to revert critical css.
	 */
	const handleRevertCSS = () => {
		const postId = select( 'core/editor' ).getCurrentPostId();

		trackMixPanelEvents( 'revert' );
		hideNotice();

		Fetcher.minification.revertCSSForPost( postId ).then( ( res ) => {
			setCriticalStatus( res?.singlePostCriticalCSSStatus );
			const message = res?.message;
			if ( message ) {
				showNotice( revertedNoticeId, message );
			}
		} );
	};

	return (
		<>
			{ pageCache && <PluginPostStatusInfo className="wphb-clear-cache">
				<input
					type="submit"
					value={ getString( 'button' ) }
					onClick={ handleClearCache }
					className="components-button is-button is-default is-secondary is-large editor-post-trash"
				/>
			</PluginPostStatusInfo>
			}
			{ criticalCss && ( 'complete' !== singlePostCriticalStatus
				? <PluginPostStatusInfo key="wphb-create-critical" className="wphb-create-critical">
					<button onClick={ handleCreateCSS } disabled={ 'processing' === singlePostCriticalStatus ? true : false } className="button components-button is-button is-default is-secondary is-large editor-post-trash">
						{ 'processing' === singlePostCriticalStatus ? __( 'Generating Critical CSS', 'wphb' ) : getString( 'criticalCreateButton' ) }
						{
							'processing' === singlePostCriticalStatus
								? <span style={ { visibility: 'visible', display: 'block' } } className="spinner"></span>
								: ''
						}
					</button>
				</PluginPostStatusInfo>

				: <>
					<PluginPostStatusInfo key="wphb-regenerate-critical" className="wphb-regenerate-critical">
						<input
							type="submit"
							value={ getString( 'criticalRecreateButton' ) }
							onClick={ handleRecreateCSS }
							className="components-button is-button is-default is-secondary is-large editor-post-trash"
						/>
					</PluginPostStatusInfo>
					<PluginPostStatusInfo key="wphb-revert-critical" className="wphb-revert-critical">
						<input
							type="submit"
							value={ getString( 'criticalRevertButton' ) }
							onClick={ handleRevertCSS }
							className="components-button is-button is-default is-secondary is-large editor-post-trash"
						/>
					</PluginPostStatusInfo>
				</>

			) }
			{ getString( 'displayProLabelButton' ) && <PluginPostStatusInfo key="wphb-create-critical" className="wphb-create-critical">
					<button onClick={ handleCreateCSS } className="button components-button is-button is-default is-secondary is-large editor-post-trash">
						{ getString( 'criticalCreateButton' ) }
					</button>
				</PluginPostStatusInfo>
			}
		</>
	);
};
registerPlugin( 'wphb', { render: MyPluginPostStatusInfo } );
