/**
 * External dependencies
 */
import React from 'react';
import classnames from 'classnames';

/**
 * Toggle functional component.
 *
 * @param {Object}  props             Component props.
 * @param {*}       props.text        Toggle text.
 * @param {string}  props.id          Toggle ID.
 * @param {string}  props.name        Toggle name.
 * @param {string}  props.className
 * @param {*}       props.onChange    On change action.
 * @param {boolean} props.checked     Checked status.
 * @param {boolean} props.disabled    Disabled status.
 * @param {boolean} props.hideToggle  Hide toggle icon.
 * @param {string}  props.description Description text.
 *
 * @return {JSX.Element} Toggle component.
 *
 * @class
 */
export default function Toggle( {
	text,
	id,
	name,
	className,
	onChange,
	checked = false,
	hideToggle = false,
	disabled = false,
	description = '',
	...props
} ) {
	return (
		<div className="sui-form-field">
			<label htmlFor={ id } className={classnames(className, 'sui-toggle')}>
				<input
					type="checkbox"
					name={ name }
					id={ id }
					checked={ checked }
					disabled={ disabled }
					onChange={ onChange }
					aria-labelledby={ id + '-label' }
					{ ...props }
				/>

				{ ! hideToggle &&
					<span className="sui-toggle-slider" aria-hidden="true" /> }

				{ text &&
					<span id={ id + '-label' } className="sui-toggle-label">
						{ text }
					</span> }

				{ description &&
					<span id={id + '-description'} className="sui-description">{description}</span>
				}
			</label>
		</div>
	);
}
