/**
 * External dependencies
 */
import React from 'react';
import classNames from 'classnames';

/**
 * Internal dependencies
 */
import './style.scss';

/**
 * Functional List component.
 *
 * @param {Object} props              Component props.
 * @param {Object} props.elements
 * @param {Object} props.header
 * @param {Array}  props.extraClasses Extra classes.
 * @return {*} List component.
 * @class
 */
export default function List( { elements, header, extraClasses } ) {
	const items = Object.values( elements ).map( ( element, id ) => {
		return (
			<li key={ id }>
				<span className="sui-list-label">{ element.label }</span>
				<span className="sui-list-detail">{ element.details }</span>
			</li>
		);
	} );

	const classes = classNames( 'sui-list', { extraClasses } );

	return (
		<ul className={ classes }>
			{ header && (
				<li className="wphb-list-header">
					<span>{ header[ 0 ] }</span>
					<span>{ header[ 1 ] }</span>
				</li>
			) }
			{ items }
		</ul>
	);
}
