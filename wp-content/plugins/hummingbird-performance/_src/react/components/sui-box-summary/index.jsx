/**
 * External dependencies
 */
import React from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';

/**
 * Internal dependencies
 */
import './style.scss';
import Loader from '../loader';

/**
 * Summary box component.
 *
 * @since 3.4.0
 */
export default class BoxSummary extends React.Component {
	/**
	 * Get box class.
	 *
	 * @return {string} Class name.
	 */
	getBoxClass() {
		if ( ! this.props.hideBranding ) {
			return '';
		}

		return this.props.brandingHeroImage ? 'sui-rebranded' : 'sui-unbranded';
	}

	/**
	 * Get branded image.
	 *
	 * @return {{}|{'backgroundImage: url(': string}} Branded image styles.
	 */
	getBrandedImage() {
		if ( this.props.brandingHeroImage ) {
			return { backgroundImage: 'url(' + this.props.brandingHeroImage + ')' };
		}

		return {};
	}

	/**
	 * Render component.
	 *
	 * @return {*} Box component.
	 */
	render() {
		return (
			<div className={ classNames( 'sui-box', 'sui-summary', this.getBoxClass() ) }>
				<Loader loading={ this.props.loading } />
				<div className="sui-summary-image-space" aria-hidden="true" style={ this.getBrandedImage() }></div>
				<div className="sui-summary-segment">
					{ this.props.summarySegmentLeft }
				</div>

				<div className="sui-summary-segment">
					{ this.props.summarySegmentRight }
				</div>
			</div>
		);
	}
}

BoxSummary.propTypes = {
	summarySegmentLeft: PropTypes.element,
	summarySegmentRight: PropTypes.element,
	brandingHeroImage: PropTypes.string,
	hideBranding: PropTypes.bool,
};
