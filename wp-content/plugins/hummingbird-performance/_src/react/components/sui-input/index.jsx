/**
 * External dependencies
 */
import React from 'react';

/**
 * Input functional component.
 *
 * @param {Object} props             Component props.
 * @param {*}      props.label       Input label.
 * @param {string} props.id          Input ID.
 * @param {string} props.placeholder Input placeholder.
 * @param {string} props.value       Current value.
 * @param {*}      props.onChange    onChange event handler.
 *
 * @return {JSX.Element} Input component.
 *
 * @class
 */
export default function Input( {
	label,
	id,
	placeholder,
	value,
	onChange
} ) {
	return (
		<div className="sui-form-field">
			<label htmlFor={ id } className="sui-label" aria-label={ placeholder ?? label }>{ label }</label>
			<input
				type="text"
				className="sui-form-control"
				id={ id }
				value={ value }
				aria-labelledby={ id + '-label' }
				placeholder={ placeholder }
				autoComplete="off"
				onChange={ onChange }
			/>
		</div>
	);
}
