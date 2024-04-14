/**
 * External dependencies
 */
import React from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';

/**
 * BoxBuilder component.
 *
 * @since 3.4.0
 */
export default class BoxBuilder extends React.Component {
	render() {
		return (
			<div className={ classNames( 'sui-box-builder', { 'sui-flushed': this.props.flushed } ) }>
				<div className="sui-box-builder-body">
					<div className="sui-builder-fields">
						{ this.props.fields }
					</div>
				</div>
			</div>
		);
	}
}

BoxBuilder.propTypes = {
	fields: PropTypes.node,
	flushed: PropTypes.bool,
};
