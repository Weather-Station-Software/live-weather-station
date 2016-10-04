<?php

namespace WeatherStation\UI\ListTable;

/**
 * Base class for displaying a list of items in an ajaxified HTML table.
 *
 * Modified copy of WP_List_Table from WordPress 4.3.1.
 *
 * @package Includes\Classes
 * @author WordPress
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 2.0.0
 */
class Base {

    public $items;
    protected $_args;
    protected $_pagination_args = array();
    protected $screen;
    private $_actions;
    private $_pagination;
    protected $modes = array();
    protected $_column_headers;
    protected $compat_fields = array( '_args', '_pagination_args', 'screen', '_actions', '_pagination' );
    protected $compat_methods = array( 'set_pagination_args', 'get_views', 'get_bulk_actions', 'bulk_actions',
        'row_actions', 'months_dropdown', 'view_switcher', 'comments_bubble', 'get_items_per_page', 'pagination',
        'get_sortable_columns', 'get_column_info', 'get_table_classes', 'display_tablenav', 'extra_tablenav',
        'single_row_columns' );


    public function __construct( $args = array() ) {
        $args = wp_parse_args( $args, array(
            'plural' => '',
            'singular' => '',
            'ajax' => false,
            'screen' => null,
        ) );

        $this->screen = convert_to_screen( $args['screen'] );

        add_filter( "manage_{$this->screen->id}_columns", array( $this, 'get_columns' ), 0 );

        if ( !$args['plural'] )
            $args['plural'] = $this->screen->base;

        $args['plural'] = sanitize_key( $args['plural'] );
        $args['singular'] = sanitize_key( $args['singular'] );

        $this->_args = $args;

        if ( $args['ajax'] ) {
            // wp_enqueue_script( 'list-table' );
            add_action( 'admin_footer', array( $this, '_js_vars' ) );
        }

        if ( empty( $this->modes ) ) {
            $this->modes = array(
                'list'    => __( 'List View' ),
                'excerpt' => __( 'Excerpt View' )
            );
        }
    }

    public function __get( $name ) {
        if ( in_array( $name, $this->compat_fields ) ) {
            return $this->$name;
        }
    }

    public function __set( $name, $value ) {
        if ( in_array( $name, $this->compat_fields ) ) {
            return $this->$name = $value;
        }
    }
    public function __isset( $name ) {
        if ( in_array( $name, $this->compat_fields ) ) {
            return isset( $this->$name );
        }
    }
    public function __unset( $name ) {
        if ( in_array( $name, $this->compat_fields ) ) {
            unset( $this->$name );
        }
    }
    public function __call( $name, $arguments ) {
        if ( in_array( $name, $this->compat_methods ) ) {
            return call_user_func_array( array( $this, $name ), $arguments );
        }
        return false;
    }
    public function ajax_user_can() {
        die( 'function WP_List_Table::ajax_user_can() must be over-ridden in a sub-class.' );
    }
    public function prepare_items() {
        die( 'function WP_List_Table::prepare_items() must be over-ridden in a sub-class.' );
    }
    protected function set_pagination_args( $args ) {
        $args = wp_parse_args( $args, array(
            'total_items' => 0,
            'total_pages' => 0,
            'per_page' => 0,
        ) );

        if ( !$args['total_pages'] && $args['per_page'] > 0 )
            $args['total_pages'] = ceil( $args['total_items'] / $args['per_page'] );

        // Redirect if page number is invalid and headers are not already sent.
        if ( ! headers_sent() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) && $args['total_pages'] > 0 && $this->get_pagenum() > $args['total_pages'] ) {
            wp_redirect( add_query_arg( 'paged', $args['total_pages'] ) );
            exit;
        }

        $this->_pagination_args = $args;
    }
    public function get_pagination_arg( $key ) {
        if ( 'page' == $key )
            return $this->get_pagenum();

        if ( isset( $this->_pagination_args[$key] ) )
            return $this->_pagination_args[$key];
    }
    public function has_items() {
        return !empty( $this->items );
    }
    public function no_items() {
        _e( 'No items found.' );
    }
    public function search_box( $text, $input_id ) {
        if ( empty( $_REQUEST['s'] ) && !$this->has_items() )
            return;

        $input_id = $input_id . '-search-input';

        if ( ! empty( $_REQUEST['orderby'] ) )
            echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
        if ( ! empty( $_REQUEST['order'] ) )
            echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
        if ( ! empty( $_REQUEST['post_mime_type'] ) )
            echo '<input type="hidden" name="post_mime_type" value="' . esc_attr( $_REQUEST['post_mime_type'] ) . '" />';
        if ( ! empty( $_REQUEST['detached'] ) )
            echo '<input type="hidden" name="detached" value="' . esc_attr( $_REQUEST['detached'] ) . '" />';
        ?>
        <p class="search-box">
            <label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
            <input type="search" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>" />
            <?php submit_button( $text, 'button', '', false, array('id' => 'search-submit') ); ?>
        </p>
        <?php
    }
    protected function get_views() {
        return array();
    }
    public function views() {
        $views = $this->get_views();
        $views = apply_filters( "views_{$this->screen->id}", $views );

        if ( empty( $views ) )
            return;

        echo "<ul class='subsubsub'>\n";
        foreach ( $views as $class => $view ) {
            $views[ $class ] = "\t<li class='$class'>$view";
        }
        echo implode( " |</li>\n", $views ) . "</li>\n";
        echo "</ul>";
    }
    protected function get_bulk_actions() {
        return array();
    }
    protected function bulk_actions( $which = '' ) {
        if ( is_null( $this->_actions ) ) {
            $no_new_actions = $this->_actions = $this->get_bulk_actions();
            $this->_actions = apply_filters( "bulk_actions-{$this->screen->id}", $this->_actions );
            $this->_actions = array_intersect_assoc( $this->_actions, $no_new_actions );
            $two = '';
        } else {
            $two = '2';
        }

        if ( empty( $this->_actions ) )
            return;

        echo "<label for='bulk-action-selector-" . esc_attr( $which ) . "' class='screen-reader-text'>" . __( 'Select bulk action' ) . "</label>";
        echo "<select name='subaction$two' id='bulk-action-selector-" . esc_attr( $which ) . "'>\n";
        echo "<option value='-1' selected='selected'>" . __( 'Bulk Actions' ) . "</option>\n";

        foreach ( $this->_actions as $name => $title ) {
            $class = 'edit' == $name ? ' class="hide-if-no-js"' : '';

            echo "\t<option value='$name'$class>$title</option>\n";
        }

        echo "</select>\n";

        submit_button( __( 'Apply' ), 'subaction', '', false, array( 'id' => "doaction$two" ) );
        echo "\n";
    }
    public function current_action() {
        if ( isset( $_REQUEST['filter_action'] ) && ! empty( $_REQUEST['filter_action'] ) )
            return false;

        if ( isset( $_REQUEST['subaction'] ) && -1 != $_REQUEST['subaction'] )
            return $_REQUEST['subaction'];

        if ( isset( $_REQUEST['subaction2'] ) && -1 != $_REQUEST['subaction2'] )
            return $_REQUEST['subaction2'];

        return false;
    }
    protected function row_actions( $actions, $always_visible = false ) {
        $action_count = count( $actions );
        $i = 0;

        if ( !$action_count )
            return '';

        $out = '<div class="' . ( $always_visible ? 'row-actions visible' : 'row-actions' ) . '">';
        foreach ( $actions as $action => $link ) {
            ++$i;
            ( $i == $action_count ) ? $sep = '' : $sep = ' | ';
            $out .= "<span class='$action'>$link$sep</span>";
        }
        $out .= '</div>';

        $out .= '<button type="button" class="toggle-row"><span class="screen-reader-text">' . __( 'Show more details' ) . '</span></button>';

        return $out;
    }

    protected function view_switcher( $current_mode ) {
        ?>
        <input type="hidden" name="mode" value="<?php echo esc_attr( $current_mode ); ?>" />
        <div class="view-switch">
            <?php
            foreach ( $this->modes as $mode => $title ) {
                $classes = array( 'view-' . $mode );
                if ( $current_mode == $mode )
                    $classes[] = 'current';
                printf(
                    "<a href='%s' class='%s' id='view-switch-$mode'><span class='screen-reader-text'>%s</span></a>\n",
                    esc_url( add_query_arg( 'mode', $mode ) ),
                    implode( ' ', $classes ),
                    $title
                );
            }
            ?>
        </div>
        <?php
    }
    public function get_pagenum() {
        $pagenum = isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 0;

        if ( isset( $this->_pagination_args['total_pages'] ) && $pagenum > $this->_pagination_args['total_pages'] )
            $pagenum = $this->_pagination_args['total_pages'];

        return max( 1, $pagenum );
    }
    protected function get_items_per_page( $option, $default = 20 ) {
        $per_page = (int) get_user_option( $option );
        if ( empty( $per_page ) || $per_page < 1 )
            $per_page = $default;

        return (int) apply_filters( $option, $per_page );
    }
    protected function pagination( $which ) {
        if ( empty( $this->_pagination_args ) ) {
            return;
        }

        $total_items = $this->_pagination_args['total_items'];
        $total_pages = $this->_pagination_args['total_pages'];
        $infinite_scroll = false;
        if ( isset( $this->_pagination_args['infinite_scroll'] ) ) {
            $infinite_scroll = $this->_pagination_args['infinite_scroll'];
        }

        $output = '<span class="displaying-num">' . sprintf( _n( '%s item', '%s items', $total_items ), number_format_i18n( $total_items ) ) . '</span>';

        $current = $this->get_pagenum();

        $current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );

        $current_url = remove_query_arg( array( 'hotkeys_highlight_last', 'hotkeys_highlight_first' ), $current_url );

        $page_links = array();

        $total_pages_before = '<span class="paging-input">';
        $total_pages_after  = '</span>';

        $disable_first = $disable_last = $disable_prev = $disable_next = false;

        if ( $current == 1 ) {
            $disable_first = true;
            $disable_prev = true;
        }
        if ( $current == 2 ) {
            $disable_first = true;
        }
        if ( $current == $total_pages ) {
            $disable_last = true;
            $disable_next = true;
        }
        if ( $current == $total_pages - 1 ) {
            $disable_last = true;
        }

        if ( $disable_first ) {
            $page_links[] = '<span class="tablenav-pages-navspan" aria-hidden="true">&laquo;</span>';
        } else {
            $page_links[] = sprintf( "<a class='first-page' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
                esc_url( remove_query_arg( 'paged', $current_url ) ),
                __( 'First page' ),
                '&laquo;'
            );
        }

        if ( $disable_prev ) {
            $page_links[] = '<span class="tablenav-pages-navspan" aria-hidden="true">&lsaquo;</span>';
        } else {
            $page_links[] = sprintf( "<a class='prev-page' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
                esc_url( add_query_arg( 'paged', max( 1, $current-1 ), $current_url ) ),
                __( 'Previous page' ),
                '&lsaquo;'
            );
        }

        if ( 'bottom' == $which ) {
            $html_current_page  = $current;
            $total_pages_before = '<span class="screen-reader-text">' . __( 'Current Page' ) . '</span><span id="table-paging" class="paging-input">';
        } else {
            $html_current_page = sprintf( "%s<input class='current-page' id='current-page-selector' type='text' name='paged' value='%s' size='%d' aria-describedby='table-paging' />",
                '<label for="current-page-selector" class="screen-reader-text">' . __( 'Current Page' ) . '</label>',
                $current,
                strlen( $total_pages )
            );
        }
        $html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
        $page_links[] = $total_pages_before . sprintf( _x( '%1$s of %2$s', 'paging' ), $html_current_page, $html_total_pages ) . $total_pages_after;

        if ( $disable_next ) {
            $page_links[] = '<span class="tablenav-pages-navspan" aria-hidden="true">&rsaquo;</span>';
        } else {
            $page_links[] = sprintf( "<a class='next-page' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
                esc_url( add_query_arg( 'paged', min( $total_pages, $current+1 ), $current_url ) ),
                __( 'Next page' ),
                '&rsaquo;'
            );
        }

        if ( $disable_last ) {
            $page_links[] = '<span class="tablenav-pages-navspan" aria-hidden="true">&raquo;</span>';
        } else {
            $page_links[] = sprintf( "<a class='last-page' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
                esc_url( add_query_arg( 'paged', $total_pages, $current_url ) ),
                __( 'Last page' ),
                '&raquo;'
            );
        }

        $pagination_links_class = 'pagination-links';
        if ( ! empty( $infinite_scroll ) ) {
            $pagination_links_class = ' hide-if-js';
        }
        $output .= "\n<span class='$pagination_links_class'>" . join( "\n", $page_links ) . '</span>';

        if ( $total_pages ) {
            $page_class = $total_pages < 2 ? ' one-page' : '';
        } else {
            $page_class = ' no-pages';
        }
        $this->_pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";

        echo $this->_pagination;
    }
    public function get_columns() {
        die( 'function WP_List_Table::get_columns() must be over-ridden in a sub-class.' );
    }
    protected function get_sortable_columns() {
        return array();
    }
    protected function get_default_primary_column_name() {
        $columns = $this->get_columns();
        $column = '';

        // We need a primary defined so responsive views show something,
        // so let's fall back to the first non-checkbox column.
        foreach( $columns as $col => $column_name ) {
            if ( 'cb' === $col ) {
                continue;
            }

            $column = $col;
            break;
        }

        return $column;
    }
    protected function get_primary_column_name() {
        $columns = $this->get_columns();
        $default = $this->get_default_primary_column_name();

        // If the primary column doesn't exist fall back to the
        // first non-checkbox column.
        if ( ! isset( $columns[ $default ] ) ) {
            $default = Live_Weather_Station_List_Table::get_default_primary_column_name();
        }

        $column  = apply_filters( 'list_table_primary_column', $default, $this->screen->id );

        if ( empty( $column ) || ! isset( $columns[ $column ] ) ) {
            $column = $default;
        }

        return $column;
    }
    protected function get_column_info() {
        // $_column_headers is already set / cached
        if ( isset( $this->_column_headers ) && is_array( $this->_column_headers ) ) {
            // Back-compat for list tables that have been manually setting $_column_headers for horse reasons.
            // In 4.3, we added a fourth argument for primary column.
            $column_headers = array( array(), array(), array(), $this->get_primary_column_name() );
            foreach ( $this->_column_headers as $key => $value ) {
                $column_headers[ $key ] = $value;
            }

            return $column_headers;
        }

        $columns = get_column_headers( $this->screen );
        $hidden = get_hidden_columns( $this->screen );

        $sortable_columns = $this->get_sortable_columns();
        $_sortable = apply_filters( "manage_{$this->screen->id}_sortable_columns", $sortable_columns );

        $sortable = array();
        foreach ( $_sortable as $id => $data ) {
            if ( empty( $data ) )
                continue;

            $data = (array) $data;
            if ( !isset( $data[1] ) )
                $data[1] = false;

            $sortable[$id] = $data;
        }

        $primary = $this->get_primary_column_name();
        $this->_column_headers = array( $columns, $hidden, $sortable, $primary );

        return $this->_column_headers;
    }
    public function get_column_count() {
        list ( $columns, $hidden ) = $this->get_column_info();
        $hidden = array_intersect( array_keys( $columns ), array_filter( $hidden ) );
        return count( $columns ) - count( $hidden );
    }
    public function print_column_headers( $with_id = true ) {
        list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();

        $current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
        $current_url = remove_query_arg( 'paged', $current_url );

        if ( isset( $_GET['orderby'] ) )
            $current_orderby = $_GET['orderby'];
        else
            $current_orderby = '';

        if ( isset( $_GET['order'] ) && 'desc' == $_GET['order'] )
            $current_order = 'desc';
        else
            $current_order = 'asc';

        if ( ! empty( $columns['cb'] ) ) {
            static $cb_counter = 1;
            $columns['cb'] = '<label class="screen-reader-text" for="cb-select-all-' . $cb_counter . '">' . __( 'Select All' ) . '</label>'
                . '<input id="cb-select-all-' . $cb_counter . '" type="checkbox" />';
            $cb_counter++;
        }

        foreach ( $columns as $column_key => $column_display_name ) {
            $class = array( 'manage-column', "column-$column_key" );

            if ( in_array( $column_key, $hidden ) ) {
                $class[] = 'hidden';
            }

            if ( 'cb' == $column_key )
                $class[] = 'check-column';
            elseif ( in_array( $column_key, array( 'posts', 'comments', 'links' ) ) )
                $class[] = 'num';

            if ( $column_key === $primary ) {
                $class[] = 'column-primary';
            }

            if ( isset( $sortable[$column_key] ) ) {
                list( $orderby, $desc_first ) = $sortable[$column_key];

                if ( $current_orderby == $orderby ) {
                    $order = 'asc' == $current_order ? 'desc' : 'asc';
                    $class[] = 'sorted';
                    $class[] = $current_order;
                } else {
                    $order = $desc_first ? 'desc' : 'asc';
                    $class[] = 'sortable';
                    $class[] = $desc_first ? 'asc' : 'desc';
                }

                $column_display_name = '<a href="' . esc_url( add_query_arg( compact( 'orderby', 'order' ), $current_url ) ) . '"><span>' . $column_display_name . '</span><span class="sorting-indicator"></span></a>';
            }

            $tag = ( 'cb' === $column_key ) ? 'td' : 'th';
            $scope = ( 'th' === $tag ) ? 'scope="col"' : '';
            $id = $with_id ? "id='$column_key'" : '';

            if ( !empty( $class ) )
                $class = "class='" . join( ' ', $class ) . "'";

            echo "<$tag $scope $id $class>$column_display_name</$tag>";
        }
    }
    public function display() {
        $singular = $this->_args['singular'];

        $this->display_tablenav( 'top' );
        ?>
        <table class="wp-list-table <?php echo implode( ' ', $this->get_table_classes() ); ?>">
            <thead>
            <tr>
                <?php $this->print_column_headers(); ?>
            </tr>
            </thead>

            <tbody id="the-list"<?php
            if ( $singular ) {
                echo " data-wp-lists='list:$singular'";
            } ?>>
            <?php $this->display_rows_or_placeholder(); ?>
            </tbody>

            <tfoot>
            <tr>
                <?php $this->print_column_headers( false ); ?>
            </tr>
            </tfoot>

        </table>
        <?php
        $this->display_tablenav( 'bottom' );
    }
    protected function get_table_classes() {
        return array( 'widefat', 'fixed', 'striped', $this->_args['plural'] );
    }
    protected function display_tablenav( $which ) {
        /*if ( 'top' == $which )
            wp_nonce_field( 'bulk-' . $this->_args['plural'] );*/
        ?>
        <div class="tablenav <?php echo esc_attr( $which ); ?>">

            <div class="alignleft actions bulkactions">
                <?php $this->bulk_actions( $which ); ?>
            </div>
            <?php
            $this->extra_tablenav( $which );
            $this->pagination( $which );
            ?>

            <br class="clear" />
        </div>
        <?php
    }
    protected function extra_tablenav( $which ) {}

    public function display_rows_or_placeholder() {
        if ( $this->has_items() ) {
            $this->display_rows();
        } else {
            echo '<tr class="no-items"><td class="colspanchange" colspan="' . $this->get_column_count() . '">';
            $this->no_items();
            echo '</td></tr>';
        }
    }
    public function display_rows() {
        foreach ( $this->items as $item )
            $this->single_row( $item );
    }
    public function single_row( $item ) {
        echo '<tr>';
        $this->single_row_columns( $item );
        echo '</tr>';
    }
    protected function column_default( $item, $column_name ) {}

    protected function column_cb( $item ) {}

    protected function single_row_columns( $item ) {
        list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();

        foreach ( $columns as $column_name => $column_display_name ) {
            $classes = "$column_name column-$column_name";
            if ( $primary === $column_name ) {
                $classes .= ' has-row-actions column-primary';
            }

            if ( in_array( $column_name, $hidden ) ) {
                $classes .= ' hidden';
            }

            // Comments column uses HTML in the display name with screen reader text.
            // Instead of using esc_attr(), we strip tags to get closer to a user-friendly string.
            $data = 'data-colname="' . wp_strip_all_tags( $column_display_name ) . '"';

            $attributes = "class='$classes' $data";

            if ( 'cb' == $column_name ) {
                echo '<th scope="row" class="check-column">';
                echo $this->column_cb( $item );
                echo '</th>';
            } elseif ( method_exists( $this, '_column_' . $column_name ) ) {
                echo call_user_func(
                    array( $this, '_column_' . $column_name ),
                    $item,
                    $classes,
                    $data,
                    $primary
                );
            } elseif ( method_exists( $this, 'column_' . $column_name ) ) {
                echo "<td $attributes>";
                echo call_user_func( array( $this, 'column_' . $column_name ), $item );
                echo $this->handle_row_actions( $item, $column_name, $primary );
                echo "</td>";
            } else {
                echo "<td $attributes>";
                echo $this->column_default( $item, $column_name );
                echo $this->handle_row_actions( $item, $column_name, $primary );
                echo "</td>";
            }
        }
    }
    protected function handle_row_actions( $item, $column_name, $primary ) {
        return $column_name == $primary ? '<button type="button" class="toggle-row"><span class="screen-reader-text">' . __( 'Show more details' ) . '</span></button>' : '';
    }
    public function ajax_response() {
        $this->prepare_items();

        ob_start();
        if ( ! empty( $_REQUEST['no_placeholder'] ) ) {
            $this->display_rows();
        } else {
            $this->display_rows_or_placeholder();
        }

        $rows = ob_get_clean();

        $response = array( 'rows' => $rows );

        if ( isset( $this->_pagination_args['total_items'] ) ) {
            $response['total_items_i18n'] = sprintf(
                _n( '%s item', '%s items', $this->_pagination_args['total_items'] ),
                number_format_i18n( $this->_pagination_args['total_items'] )
            );
        }
        if ( isset( $this->_pagination_args['total_pages'] ) ) {
            $response['total_pages'] = $this->_pagination_args['total_pages'];
            $response['total_pages_i18n'] = number_format_i18n( $this->_pagination_args['total_pages'] );
        }

        die( wp_json_encode( $response ) );
    }
    public function _js_vars() {
        $args = array(
            'class'  => get_class( $this ),
            'screen' => array(
                'id'   => $this->screen->id,
                'base' => $this->screen->base,
            )
        );

        printf( "<script type='text/javascript'>list_args = %s;</script>\n", wp_json_encode( $args ) );
    }
}
