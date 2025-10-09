<?php
/**
 * Seriously Simple Podcasting integration file.
 *
 * @package Activitypub
 */

namespace Activitypub\Integration;

use Activitypub\Transformer\Post;

use function Activitypub\generate_post_summary;
use function Activitypub\object_to_uri;

/**
 * Compatibility with the Seriously Simple Podcasting plugin.
 *
 * This is a transformer for the Seriously Simple Podcasting plugin,
 * that extends the default transformer for WordPress posts.
 *
 * @see https://wordpress.org/plugins/seriously-simple-podcasting/
 */
class Seriously_Simple_Podcasting extends Post {
	/**
	 * Gets the attachment for a podcast episode.
	 *
	 * This method is overridden to add the audio file as an attachment.
	 *
	 * @return array The attachments array.
	 */
	public function get_attachment() {
		$post       = $this->item;
		$attachment = array(
			'type' => \esc_attr( ucfirst( \get_post_meta( $post->ID, 'episode_type', true ) ?? 'Audio' ) ),
			'url'  => \esc_url( \get_post_meta( $post->ID, 'audio_file', true ) ),
			'name' => \esc_attr( \get_the_title( $post->ID ) ?? '' ),
		);

		$icon = \get_post_meta( $post->ID, 'cover_image', true );
		if ( ! $icon ) {
			$icon = $this->get_icon();
		}

		if ( $icon ) {
			$attachment['icon'] = \esc_url( object_to_uri( $icon ) );
		}

		return array( $attachment );
	}

	/**
	 * Gets the object type for a podcast episode.
	 *
	 * Always returns 'Note' for the best possible compatibility with ActivityPub.
	 *
	 * @return string The object type.
	 */
	public function get_type() {
		return 'Note';
	}

	/**
	 * Returns the content for the ActivityPub Item.
	 *
	 * The content will be generated based on the user settings.
	 *
	 * @return string The content.
	 */
	public function get_content() {
		return generate_post_summary( $this->item );
	}
}
