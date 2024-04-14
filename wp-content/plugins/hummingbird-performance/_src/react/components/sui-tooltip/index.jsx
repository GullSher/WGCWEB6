/**
 * External dependencies
 */
import React from 'react';
import classNames from 'classnames';

/**
 * Tooltip component.
 *
 * @param {Object} props          Component props.
 * @param {string} props.text     Tooltip text.
 * @param {Array}  props.classes  Tooltip classes.
 * @param {Object} props.children Component child elements.
 * @return {*} Tooltip component.
 * @class
 */
export default function Tooltip( { text, classes, children } ) {
	return (
		<span className={ classNames( 'sui-tooltip', classes ) } data-tooltip={ text }>
			{ children }
		</span>
	);
}
