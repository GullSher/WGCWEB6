/**
 * External dependencies
 */
import React from 'react';
import classNames from 'classnames';

/**
 * Checkbox component.
 */
export default class Checkbox extends React.Component {
	/**
	 * Get element content.
	 *
	 * @return {JSX.Element} Checkbox element.
	 */
	getElement() {
		return (
			<React.Fragment>
				<label
					htmlFor={ 'wphb-' + this.props.id }
					className={ classNames( 'sui-checkbox', {
						'sui-checkbox-sm':
							'undefined' !== typeof this.props.size &&
							'sm' === this.props.size,
					}, {
						'sui-checkbox-stacked': this.props.stacked
					} ) }
				>
					<input
						type="checkbox"
						id={ 'wphb-' + this.props.id }
						aria-labelledby={ 'wphb-' + this.props.id + '-label' }
						checked={ this.props.checked }
						disabled={ this.props.disabled }
						onChange={ this.props.onChange }
						{ ...this.props.data }
					/>
					<span aria-hidden="true"></span>
					{ this.props.label && (
						<span id={ 'wphb-' + this.props.id + '-label' }>
							{ this.props.label }
						</span>
					) }
				</label>
				{ this.props.description && (
					<span className="sui-description sui-checkbox-description">
						{ this.props.description }
					</span>
				) }
			</React.Fragment>
		);
	}

	/**
	 * Render component.
	 *
	 * @return {JSX.Element}  Select component.
	 */
	render() {
		if ( this.props.stacked ) {
			return this.getElement();
		}

		return (
			<div className="sui-form-field">
				{ this.getElement() }
			</div>
		);
	}
}

Checkbox.defaultProps = {
	id: '',
	description: '',
	label: '',
	checked: false,
	disabled: false,
	stacked: false,
};
