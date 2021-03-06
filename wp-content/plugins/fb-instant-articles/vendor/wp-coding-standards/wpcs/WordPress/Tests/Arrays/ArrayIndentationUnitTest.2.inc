<?php
// @codingStandardsChangeSetting WordPress.Arrays.ArrayIndentation tabIndent false
$ok = array(
    'value',
    123,
);

$ok_with_keys = [
    'key1' => 'value',
    'key2' => 'value', // Comment after item.
];

$ok_nested = array(
    'key1' => array(
        'key1' => 'value',
        'key2' => 'value',
    ),
    'key2' => 'value',
);


$bad_phpcs_style = array(
                    'value',
                    123, // Comment after item.
                   );

$bad_phpcs_style_with_keys = array(
                              'key1' => 'value',
                              'key2' => 'value',
                             );

$bad_phpcs_style_nested = array(
                           'key1' => [
                                      'key1' => 'value',

                                      'key2' => 'value',

                                     ],
                           'key2' => 'value',
                          );

// Arrays with initial indent.
            $bad_mixed_indent = [
'value',
                                                    123,
                    ];

    $bad_mixed_indent_with_keys = array(

            'key1' => 'value',
'key2' => 'value',
        );

        $bad_mixed_indent_nested = [
                'key1' => array(
                    'key1' => 'value',
                        'key2' => 'value',
                            ),

                                'key2' => 'value',
    ];

$empty = [


    ];

// Same-line items in mixed arrays should be ignored.
$mixed_1 = array('something', 'else', array(
        'key1' => 'value',
            'key2' => 'value', // Comment after item.
    ),
);

$mixed_2 = array(
    array(
            'key1' => 'value',
        'key2' => 'value', // Comment after item.
        ),
        'something', 'else',
);

// Issue 998: don't fix the indentation for the closer if it's not on its own line.
$a = array(
    'foo' => bar(1, 29));

$a = array(
    'foo' => bar(1, 29) );

/*
 * Issue 973 - multi-line value indentation.
 */
// OK.
get_current_screen()->add_help_tab( array(
    'id'        => 'overview',
    'content'   =>
        '<p>' . __( 'You can manage comments made on your site similar to the way you manage posts and other content. This screen is customizable in the same ways as other management screens, and you can act on comments using the on-hover action links or the Bulk Actions.' ) . '</p>' .
        '<p>' . __( 'You can manage comments made on your site similar to the way you manage posts and other content. This screen is customizable in the same ways as other management screens, and you can act on comments using the on-hover action links or the Bulk Actions.' ) . '</p>'
) );

// Too little overall indentation.
get_current_screen()->add_help_tab( array(
'id'        => 'overview',
'content'   =>
    '<p>' . __( 'You can manage comments made on your site similar to the way you manage posts and other content. This screen is customizable in the same ways as other management screens, and you can act on comments using the on-hover action links or the Bulk Actions.' ) . '</p>' .
    '<p>' . __( 'You can manage comments made on your site similar to the way you manage posts and other content. This screen is customizable in the same ways as other management screens, and you can act on comments using the on-hover action links or the Bulk Actions.' ) . '</p>'
) );

// Too much overall indentation.
get_current_screen()->add_help_tab( array(
        'id'        => 'overview',
        'content'   =>
            '<p>' . __( 'You can manage comments made on your site similar to the way you manage posts and other content. This screen is customizable in the same ways as other management screens, and you can act on comments using the on-hover action links or the Bulk Actions.' ) . '</p>' .
            '<p>' . __( 'You can manage comments made on your site similar to the way you manage posts and other content. This screen is customizable in the same ways as other management screens, and you can act on comments using the on-hover action links or the Bulk Actions.' ) . '</p>'
) );


// Too little overall with more than the minimum indentation for some subsequent lines.
    get_current_screen()->add_help_tab( array(
    'id'        => 'screen-content',
    'content'   => '<p>' . __('You can customize the display of this screen&#8217;s contents in a number of ways:') . '</p>' .
                    '<ul>' .
                        '<li>' . __('You can hide/display columns based on your needs and decide how many posts to list per screen using the Screen Options tab.') . '</li>' .
                        '<li>' . __( 'You can filter the list of posts by post status using the text links above the posts list to only show posts with that status. The default view is to show all posts.' ) . '</li>' .
                        '<li>' . __('You can view posts in a simple title list or with an excerpt using the Screen Options tab.') . '</li>' .
                        '<li>' . __('You can refine the list to show only posts in a specific category or from a specific month by using the dropdown menus above the posts list. Click the Filter button after making your selection. You also can refine the list by clicking on the post author, category or tag in the posts list.') . '</li>' .
                    '</ul>'
    ) );

// Too much overall with more than the minimum indentation for some subsequent lines.
    get_current_screen()->add_help_tab( array(
                'id'        => 'bulk-actions',
                'content'   =>
                    '<p>' . __('You can also edit or move multiple posts to the trash at once. Select the posts you want to act on using the checkboxes, then select the action you want to take from the Bulk Actions menu and click Apply.') . '</p>' .
                            '<p>' . __('When using Bulk Edit, you can change the metadata (categories, author, etc.) for all selected posts at once. To remove a post from the grouping, just click the x next to its name in the Bulk Edit area that appears.') . '</p>'
    ) );

// Too much overall with less than the minimum indentation for some subsequent lines.
get_current_screen()->add_help_tab( array(
        'id'        => 'screen-content',
        'content'   =>
'<div>' .
    '<ul>' .
        '<li>' . __('You can hide/display columns based on your needs and decide how many posts to list per screen using the Screen Options tab.') . '</li>' .
    '</ul>' .
'</div>'
) );

    // Mixed indentation.
    get_current_screen()->add_help_tab( array(
            'id'        => 'overview',
    // Here we have a comment.
'title'     =>
                __('Overview') .
                'something',
            /* Here we have a comment. */
            'content'   => 'value' .
                'value' .
        'value' .
                    'value',
    ) );

// OK: Ignore empty lines when found as subsequent line in an item.
get_current_screen()->add_help_tab( array(
    'id'        => 'something' .

        'something else',
) );

/*
 * Issue: Subsequent lines with Heredoc/Nowdoc syntax should be ignored.
 */
// Should only report the first line and fix the first line + the comma on the last line.
get_current_screen()->add_help_tab( array(
        'id'        => <<<EOD
Here comes some text.
And some more text.
EOD
,
) );

// Should report the second line and fix the second line + the comma on the last line.
get_current_screen()->add_help_tab( array(
    'id'		=>
<<<EOD
Here comes some text.
And some more text.
EOD
,
) );

/*
 * Heredoc/Nowdoc subsequent line fixer: lines before/after the heredoc/nowdoc *should* be fixed.
 */
// Should report on the 'id' line and the first line after the heredoc and fix 'id' line
// and both lines after the heredoc.
get_current_screen()->add_help_tab( array(
'id'        => <<<EOD
Here comes some text.
And some more text.
EOD
. '</p>'
. '</hr>',
) );

// Should report on the heredoc opener and fix the opener and the line after the heredoc.
get_current_screen()->add_help_tab( array(
    'id'        =>
<<<EOD
Here comes some text.
And some more text.
EOD
. '</p>',
) );

// Should report on the 'id' line and the line after. Should fix both + line after the heredoc.
get_current_screen()->add_help_tab( array(
        'id'        =>
            '<p>' . <<<EOD
Here comes some text.
And some more text.
EOD
    . '</p>',
) );

// OK. Not pretty, but not handled by this sniff as both first line + line after have acceptable alignment.
get_current_screen()->add_help_tab( array(
    'id'        =>
        '<p>' .
<<<EOD
Here comes some text.
And some more text.
EOD
        . '</p>',
) );

/*
 * Issue 985 - arrays with comments between items.
 */
// OK.
get_current_screen()->add_help_tab( array(
    'id'        => 'overview',
    // Here we have a comment.
    'title'     => __('Overview'),
    /* Here we have a comment. */
    'content'   => 'value',
) );

// Too little overall indentation.
get_current_screen()->add_help_tab( array(
'id'        => 'overview',
// Here we have a comment.
'title'     => __('Overview'),
/* Here we have a comment. */
'content'   => 'value',
) );

// Too much overall indentation.
get_current_screen()->add_help_tab( array(
            'id'        => 'overview',
            // Here we have a comment.
            'title'     => __('Overview'),
            /* Here we have a comment. */
            'content'   => 'value',
) );

// Mixed indentation.
get_current_screen()->add_help_tab( array(
            'id'        => 'overview',
    // Here we have a comment.
'title'     =>
                __('Overview'),
        /* Here we have a comment. */
            'content'   => 'value',
) );

// Various multi-line comments.
$my_array = [
/**
 * Docblock about a filtered value...
 */
'truthy' => apply_filters( '...', true ),
];

$my_array = [
        /**
         * Docblock about a filtered value...
         */
        'truthy' => apply_filters( '...', true ),
];

$my_array = [
/*
 * Multi-line inline comment about something...
 */
'truthy' => apply_filters( '...', true ),
];

$my_array = [
        /*
         * Multi-line inline comment about something...
         */
        'truthy' => apply_filters( '...', true ),
];

$my_array = array(
'something' =>
    /**
     * Docblock about a filtered value...
     */
    $a = apply_filters( '...', true ),
);

$my_array = [
        'something' =>
        /**
         * Docblock about a filtered value...
         */
        $a = apply_filters( '...', true ),
];

$my_array = [
'something' =>
/*
 * Multi-line inline comment about something...
 */
apply_filters( '...', true ),
];

$my_array = [
        'something' =>
            /*
             * Multi-line inline comment about something...
             */
            apply_filters( '...', true ),
];

// @codingStandardsChangeSetting WordPress.Arrays.ArrayIndentation tabIndent true
