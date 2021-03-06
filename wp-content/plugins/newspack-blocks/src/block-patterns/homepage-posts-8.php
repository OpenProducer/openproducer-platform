<?php
/**
 * Homepage Posts Pattern 8.
 *
 * @package Newspack_Blocks
 */

return array(
	'title'         => __( 'Homepage Posts Pattern 8', 'newspack-blocks' ),
	'content'       => "<!-- wp:group {\"className\":\"newspack-pattern homepage-posts__style-8\"} -->\n<div class=\"wp-block-group newspack-pattern homepage-posts__style-8\"><div class=\"wp-block-group__inner-container\"><!-- wp:columns -->\n<div class=\"wp-block-columns\"><!-- wp:column {\"width\":50} -->\n<div class=\"wp-block-column\" style=\"flex-basis:50%\"><!-- wp:newspack-blocks/homepage-articles {\"showExcerpt\":false,\"showAvatar\":false,\"postsToShow\":4,\"mediaPosition\":\"left\",\"imageScale\":1} /--></div>\n<!-- /wp:column -->\n\n<!-- wp:column {\"width\":25} -->\n<div class=\"wp-block-column\" style=\"flex-basis:25%\"><!-- wp:newspack-blocks/homepage-articles {\"showExcerpt\":false,\"showImage\":false,\"showAvatar\":false,\"postsToShow\":6,\"typeScale\":2} /--></div>\n<!-- /wp:column -->\n\n<!-- wp:column {\"width\":25} -->\n<div class=\"wp-block-column\" style=\"flex-basis:25%\"><!-- wp:newspack-blocks/homepage-articles {\"showExcerpt\":false,\"showImage\":false,\"showAvatar\":false,\"postsToShow\":6,\"typeScale\":2} /--></div>\n<!-- /wp:column --></div>\n<!-- /wp:columns --></div></div>\n<!-- /wp:group -->",
	'viewportWidth' => 1280,
	'categories'    => array( 'newspack-homepage-posts' ),
);
