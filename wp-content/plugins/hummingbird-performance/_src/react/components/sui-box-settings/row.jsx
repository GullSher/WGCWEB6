/**
 * External dependencies
 */
import React from 'react';
import classNames from 'classnames';

/**
 * Functional SettingsRow (sui-box-settings-row) component.
 *
 * @param {Object}  props             Component props.
 * @param {string}  props.label
 * @param {string}  props.description
 * @param {Object}  props.content
 * @param {string}  props.classes
 * @param {boolean} props.wide
 * @return {JSX.Element} SettingsRow component.
 * @class
 */
export default function SettingsRow( {
	label,
	description,
	content,
	classes,
	wide = false,
} ) {
	// Wide layout.
	if ( wide ) {
		return (
			<div className="sui-box-settings-row">
				<div className="sui-box-settings-col-2">
					<span className="sui-settings-label">{ label }</span>
					{ content }
				</div>
			</div>
		);
	}

	if ( description ) {
		return (
			<div className="sui-box-settings-row">
				<div className="sui-box-settings-col-1">
					<span className="sui-settings-label">{ label }</span>
					<span className="sui-description">{ description }</span>
				</div>
				<div className="sui-box-settings-col-2">{ content }</div>
			</div>
		);
	}

	return (
		<div className={ classNames( 'sui-box-settings-row', classes ) }>
			{ content }
		</div>
	);
}
