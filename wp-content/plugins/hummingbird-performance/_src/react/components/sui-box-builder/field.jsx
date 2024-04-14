/**
 * External dependencies
 */
import React from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';

/**
 * BoxBuilderField component.
 *
 * @since 3.4.0
 */
export default class BoxBuilderField extends React.Component {
	render() {
		return (
			<div className={ classNames( 'sui-builder-field', 'sui-react', this.props.class ) }>
				{ this.props.label &&
					<div className="sui-builder-field-label">
						{ this.props.label }
					</div> }

				{ this.props.description &&
					<small>{ this.props.description }</small> }

				{ this.props.actions }
			</div>
		);
	}
}

BoxBuilderField.propTypes = {
	actions: PropTypes.element,
	class: PropTypes.string,
	description: PropTypes.string,
	label: PropTypes.element,
};
