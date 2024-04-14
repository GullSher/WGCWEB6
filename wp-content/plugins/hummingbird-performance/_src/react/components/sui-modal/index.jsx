/**
 * External dependencies
 */
import React from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';

/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;

/**
 * Internal dependencies
 */
import Button from '../sui-button';

/**
 * Modal component.
 */
export default class Modal extends React.Component {
	/**
	 * Close modal.
	 */
	closeModal() {
		window.SUI.closeModal();
	}

	/**
	 * Render component.
	 *
	 * @return {*} Modal component.
	 */
	render() {
		const sizeClass = this.props.size ? 'sui-modal-' + this.props.size : '';

		return (
			<div className={ classNames( 'sui-modal', sizeClass ) }>
				<div role="dialog" id={ 'modal-' + this.props.id } className="sui-modal-content" aria-modal="true" aria-labelledby={ 'modal-title-' + this.props.id } aria-describedby={ 'modal-description-' + this.props.id }>
					<div className="sui-box">

						<div className="sui-box-header sui-flatten sui-content-center sui-spacing-top--60">
							<Button
								type="button"
								classes={ [ 'sui-button-icon', 'sui-button-float--right' ] }
								icon="sui-icon-close sui-md"
								text={ <span className="sui-screen-reader-text">{ __( 'Close', 'wphb' ) }</span> }
								onClick={ this.closeModal } />

							<h3 className="sui-box-title sui-lg" id={ 'modal-title-' + this.props.id }>
								{ this.props.title }
							</h3>

							{ this.props.description &&
								<p className="sui-description" id={ 'modal-description-' + this.props.id }>
									{ this.props.description }
								</p> }
						</div>

						{this.props.content &&
							<div className="sui-box-body">
								{this.props.content}
							</div>
						}

						<div className="sui-box-footer sui-flatten sui-content-center">
							{this.props.footer && this.props.footer}

							{!this.props.footer && this.props.footerBtn &&
								<>
									<Button
										onClick={this.closeModal}
										type="button"
										classes={['sui-button', 'sui-button-ghost']}
										text={__('Cancel', 'wphb')}/>

									{this.props.footerBtn}
								</>
							}
						</div>

						{ this.props.isMember && this.props.imagePath &&
							<img
								className="sui-image"
								alt={ this.props.title }
								src={ this.props.imagePath }
								srcSet={ this.props.imageSrcSet }
							/> }
					</div>

				</div>
			</div>
		);
	}
}

Modal.propTypes = {
	id: PropTypes.string,
	isMember: PropTypes.bool,
	imagePath: PropTypes.string,
	imageSrcSet: PropTypes.string,
	size: PropTypes.string,
	title: PropTypes.string,
	description: PropTypes.string,
	content: PropTypes.element,
	footerBtn: PropTypes.element,
};
