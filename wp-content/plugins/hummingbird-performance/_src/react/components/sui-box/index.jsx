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
import Icon from '../sui-icon';
import Loader from '../loader';

/**
 * Box component.
 */
export default class Box extends React.Component {
	/**
	 * Generate header.
	 *
	 * @param {string}      title         Box title.
	 * @param {string}      icon          Icon name to use, false for no icon.
	 * @param {JSX.Element} headerActions Action component.
	 * @return {*} Box header.
	 */
	static boxHeader( title = '', icon = '', headerActions = null ) {
		return (
			<React.Fragment>
				{ ( title || icon ) && (
					<h3 className="sui-box-title">
						{ icon && <Icon classes={ 'sui-icon-' + icon } /> }
						{ ' ' + title }
					</h3>
				) }

				{ headerActions }
			</React.Fragment>
		);
	}

	/**
	 * Get inner sticky conetnt.
	 *
	 * @return {*} Content.
	 */
	 renderContent() {
		const boxHeader = Box.boxHeader(
			this.props.title,
			this.props.icon,
			this.props.headerActions
		);

		let classesArray;

		if ( this.props.stickyType ) {
			classesArray = ['sui-box','sui-box-header', this.props.boxClass];
		} else {
			classesArray = ['sui-box-header'];
		}

		return (
			<React.Fragment>
				<Loader loading={ this.props.loading } text={ this.props.loadingText } />
				{ ! this.props.hideHeader && (
					<div className={ classNames( classesArray ) } >{ boxHeader }</div>
				)
				}
				{ ( ! this.props.stickyType || this.props.showFilters ) && this.props.content && (
					<div
						className={ classNames(
							'sui-box-body',
							this.props.boxBodyClass
						) }
					>
						{ this.props.content }
					</div>
				) }
				{ ( ! this.props.stickyType || this.props.showFilters ) && this.props.footerActions && (
					<div className="sui-box-footer">
						{ this.props.footerActions }
					</div>
				) }
			</React.Fragment>
		);
	}

	/**
	 * Render component.
	 *
	 * @return {*} Box component.
	 */
	render() {
		const boxRender = this.renderContent();
		
		if ( this.props.stickyType ) {
			return (
				<React.Fragment>
					{ boxRender }
				</React.Fragment>
			);
		} else {
			return (
				<div className={ classNames( 'sui-box', this.props.boxClass ) }>
					{ boxRender }
				</div>
			);
		}
	}
}

Box.propTypes = {
	boxClass: PropTypes.oneOfType( [
		PropTypes.string,
		PropTypes.array,
	] ),
	boxBodyClass: PropTypes.oneOfType( [
		PropTypes.string,
		PropTypes.array,
	] ),
	title: PropTypes.string,
	icon: PropTypes.string,
	hideHeader: PropTypes.bool,
	headerActions: PropTypes.element,
	content: PropTypes.element,
	footerActions: PropTypes.element,
	loading: PropTypes.bool,
	loadingText: PropTypes.string,
	stickyType: PropTypes.bool,
	showFilters: PropTypes.bool,
};
