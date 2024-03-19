<?php
if (!defined('WPVIVID_PLUGIN_DIR'))
{
    die;
}

if ( ! class_exists( 'WP_List_Table' ) )
{
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

if ( ! class_exists( 'WPvivid_Snapshots_List_Ex' ) )
{
    class WPvivid_Snapshots_List_Ex extends WP_List_Table
    {
        public $page_num;
        public $Snapshots_list;

        public function __construct( $args = array() )
        {
            parent::__construct(
                array(
                    'plural' => 'snapshots',
                    'screen' => 'snapshots'
                )
            );
        }

        protected function get_table_classes()
        {
            return array( 'widefat striped' );
        }

        public function get_columns()
        {
            $columns = array();
            $columns['cb'] = __( 'cb', 'wpvivid-snapshot-database' );
            $columns['wpvivid_time'] = __( 'Time', 'wpvivid-snapshot-database' );
            $columns['wpvivid_type'] = __( 'Type', 'wpvivid-snapshot-database' );
            $columns['wpvivid_prefix'] = __( 'Prefix', 'wpvivid-snapshot-database' );
            $columns['wpvivid_comment'] = __( 'Comment', 'wpvivid-snapshot-database' );
            $columns['wpvivid_actions'] = __( 'Actions', 'wpvivid-snapshot-database' );
            return $columns;
        }

        public function column_cb( $data )
        {
            echo '<input type="checkbox"/>';
        }

        public function _column_wpvivid_time( $data )
        {
            $time = gmdate('M-d-Y H:i', $data['time']);
            echo '<td>' . esc_html( $time ) . '</td>';
        }

        public function _column_wpvivid_type( $data )
        {
            echo '<td>' . esc_html( $data['type'] ) . '</td>';
        }

        public function _column_wpvivid_prefix( $data )
        {
            echo '<td>' . esc_html( $data['id'] ) . '</td>';
        }

        public function _column_wpvivid_comment( $data )
        {
            echo '<td>' . esc_html($data['comment'] ) . '</td>';
        }

        public function _column_wpvivid_actions( $data )
        {
            echo '<td>
                    <div style="cursor:pointer;float:left;">
                        <span class="dashicons dashicons-update wpvivid-dashicons-green wpvivid-snapshot-restore"></span>
                        <span class="wpvivid-snapshot-restore">Restore</span>
                        <span style="width:1rem;">|</span>
                        <span class="dashicons dashicons-trash wpvivid-dashicons-grey wpvivid-snapshot-delete"></span>
                        <span class="wpvivid-snapshot-delete">Delete</span>
                    </div>
                </td>';
        }

        public function set_list($Snapshots_list,$page_num=1)
        {
            $this->Snapshots_list=$Snapshots_list;
            $this->page_num=$page_num;
        }

        public function get_pagenum()
        {
            if($this->page_num=='first')
            {
                $this->page_num=1;
            }
            else if($this->page_num=='last')
            {
                $this->page_num=$this->_pagination_args['total_pages'];
            }
            $pagenum = $this->page_num ? $this->page_num : 0;

            if ( isset( $this->_pagination_args['total_pages'] ) && $pagenum > $this->_pagination_args['total_pages'] )
            {
                $pagenum = $this->_pagination_args['total_pages'];
            }

            return max( 1, $pagenum );
        }

        public function prepare_items()
        {
            $columns = $this->get_columns();
            $hidden = array();
            $sortable = array();
            $this->_column_headers = array($columns, $hidden, $sortable);

            $total_items =sizeof($this->Snapshots_list);

            $this->set_pagination_args(
                array(
                    'total_items' => $total_items,
                    'per_page'    => 10,
                )
            );
        }

        public function has_items()
        {
            return !empty($this->Snapshots_list);
        }

        public function display_rows()
        {
            $this->_display_rows($this->Snapshots_list);
        }

        private function _display_rows($Snapshots_list)
        {
            $page=$this->get_pagenum();

            $page_file_list=array();
            $count=0;
            while ( $count<$page )
            {
                $page_file_list = array_splice( $Snapshots_list, 0, 10);
                $count++;
            }
            foreach ( $page_file_list as $data)
            {
                $this->single_row($data);
            }
        }

        public function single_row($data)
        {
            ?>
            <tr  class='wpvivid-snapshot-row' slug="<?php echo esc_attr($data['id']);?>">
                <?php $this->single_row_columns( $data ); ?>
            </tr>
            <?php
        }

        protected function display_tablenav( $which ) {
            $css_type = '';
            if ( 'top' === $which ) {
                wp_nonce_field( 'bulk-' . $this->_args['plural'] );
                $css_type = 'margin: 0 0 10px 0';
            }
            else if( 'bottom' === $which ) {
                $css_type = 'margin: 10px 0 0 0';
            }

            $total_pages     = $this->_pagination_args['total_pages'];
            if ( $total_pages >1 && 'top' === $which)
            {
                ?>
                <div class="tablenav <?php echo esc_attr( $which ); ?>" style="<?php echo esc_attr($css_type); ?>">
                    <input type="submit" id="wpvivid_delete_snapshots_action" class="button action" value="Delete the selected snapshots">
                    <?php
                    $this->extra_tablenav( $which );
                    $this->pagination( $which );
                    ?>

                    <br class="clear" />
                </div>
                <?php
            }
            else if($total_pages >1)
            {
                ?>
                <div class="tablenav <?php echo esc_attr( $which ); ?>" style="<?php echo esc_attr($css_type); ?>">
                    <?php
                    $this->extra_tablenav( $which );
                    $this->pagination( $which );
                    ?>

                    <br class="clear" />
                </div>
                <?php
            }
            else if($total_pages <=1 && 'top' === $which)
            {
                ?>
                <input type="submit" id="wpvivid_delete_snapshots_action" class="button action" value="Delete the selected snapshots">
                <p></p>
                <?php
            }
        }

        public function display()
        {
            $singular = $this->_args['singular'];

            $this->display_tablenav( 'top' );

            $this->screen->render_screen_reader_content( 'heading_list' );
            ?>
            <table class="wp-list-table <?php echo esc_attr(implode( ' ', $this->get_table_classes() )); ?>">
                <thead>
                <tr>
                    <?php $this->print_column_headers(); ?>
                </tr>
                </thead>

                <tbody id="the-list"
                    <?php
                    if ( $singular ) {
                        echo esc_attr(" data-wp-lists='list:$singular'");
                    }
                    ?>
                >
                <?php $this->display_rows_or_placeholder(); ?>
                </tbody>

            </table>
            <?php
            $this->display_tablenav( 'bottom' );
        }
    }
}

