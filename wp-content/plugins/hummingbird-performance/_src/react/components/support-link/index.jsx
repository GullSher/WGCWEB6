/**
 * External dependencies
 */
import React from 'react';
import Button from '../sui-button';

/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;

/**
 * SupportLink functional component.
 *
 * @param {Object}  props              Component props.
 * @param {boolean} props.isMember     Membership status.
 * @param {string}  props.chatLink     Link to start live chat.
 * @param {string}  props.forumLink    Link to support forums.
 * @param {boolean} props.noFormatting Wrap content inside <p> tag.
 * @return {JSX.Element} Button component.
 * @class
 */
export default function SupportLink( { isMember, chatLink, forumLink, noFormatting = false } ) {
	const content = <React.Fragment>
		{ __( 'Still having trouble?', 'wphb' ) }&nbsp;
		<Button
			url={ isMember ? chatLink : forumLink }
			target="blank"
			text={ isMember ? __( 'Start a live chat.', 'wphb' ) : __( 'Open a support ticket.', 'wphb' ) }
		/>
	</React.Fragment>;

	if ( noFormatting ) {
		return content;
	}

	return (
		<p className="sui-description">
			{ content }
		</p>
	);
}
