/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch';
import { Button } from '@wordpress/components';
import { compose } from '@wordpress/compose';
import { withSelect, withDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

const DuplicateButton = ( { autosave, createNotice, isSavingPost, postId } ) => {
	const duplicatePrompt = async () => {
		try {
			const newId = await apiFetch( {
				path: `/newspack-popups/v1/${ postId }/duplicate`,
				method: 'POST',
			} );

			if ( isNaN( newId ) ) {
				throw new Error( __( 'Error duplicating prompt.', 'newspack-popups' ) );
			}

			createNotice( 'success', __( 'Prompt duplicated! Redirecting...', 'newspack-popups' ), {
				id: 'newspack-popups__duplicate-prompt-success',
				isDismissible: false,
				type: 'default',
			} );

			// Redirect to edit page for the copy.
			window.location = `/wp-admin/post.php?post=${ newId }&action=edit`;
		} catch ( e ) {
			createNotice( 'error', e?.message || __( 'Error duplicating prompt.', 'newspack-popups' ), {
				id: 'newspack-popups__duplicate-prompt-error',
				isDismissible: true,
				type: 'default',
			} );
		}
	};

	return (
		<Button
			isSecondary
			isBusy={ isSavingPost }
			disabled={ isSavingPost }
			onClick={ () => autosave().then( () => duplicatePrompt() ) }
		>
			{ __( 'Duplicate', 'newspack-popups' ) }
		</Button>
	);
};

export default compose( [
	withSelect( select => {
		const { isSavingPost, getCurrentPostId } = select( 'core/editor' );
		return {
			postId: getCurrentPostId(),
			isSavingPost: isSavingPost(),
		};
	} ),
	withDispatch( dispatch => {
		const { autosave } = dispatch( 'core/editor' );
		const { createNotice } = dispatch( 'core/notices' );
		return {
			autosave,
			createNotice,
		};
	} ),
] )( DuplicateButton );
