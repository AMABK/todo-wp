<?php
/*
  Plugin Name: Todo Plugin
  Plugin URI: https://www.optimuse-solutions.com
  Description: Created to ensure you do not miss out on the next get together by your crew
  Version: 1.01
  Author: Mr. Thor
  Author URI: https://www.optimuse-solutions.com
  License: GPL2
 */
add_action('admin_bar_menu', 'toolbar_link_to_mypage', 999);

function toolbar_link_to_mypage($wp_admin_bar) {
    $args = array(
        'id' => 'optimus_todo',
        'title' => 'Todo',
        'href' => 'javascript:;',
        'meta' => array('class' => 'optimus_todo',
            'onclick' => 'return show_todo(this);',
        )
    );
    $wp_admin_bar->add_menu($args);
}

add_action('admin_print_scripts', 'optimus_todo_js', 100);

function optimus_todo_js() {
    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_script('jquery-ui-dialog');
    wp_enqueue_script('jquery-ui-sortable');
    wp_enqueue_script('jquery-ui-draggable');
    wp_enqueue_script('jquery-ui-droppable');
    //print_r ( get_theme_mod('background_color') ); die;
    ?>
    <script type="text/javascript">
        //var cookieName = 'todo_div_wrapper';
        function show_todo(el) {
            var block_state = '';
            jQuery('.todo_div_wrapper').toggle();
            if (jQuery('.todo_div_wrapper').is(':visible')) {
                //console.log('visible');
                document.cookie = 'todo_div_wrapper=block';
                block_state = 'block';
            } else {
                //console.log('none');
                document.cookie = 'todo_div_wrapper=none';
                block_state = 'none';
            }
            jQuery.ajax({
                url: "<?php echo admin_url('admin-ajax.php'); ?>",
                data: {action: 'todo_visibility', 'todo_block_visibility': block_state},
                method: 'post',
                success: function (dataReturn) {
                    //console.log(dataReturn);
                }
            });
            return false;
        }
        var settime = '';
        function todo_process_textarea(el, event) {
            jQuery(document).find('.todo_status').text('Status:');
            jQuery(document).find('.todo_status').show();
            //todo_remove_empty(el);
            //console.log(event);
            if (typeof ajaxurl != 'undefined' && ajaxurl != '') {
                var d = new Date();
                clearTimeout(settime);
                settime = setTimeout(function () {
                    todo_remove_empty();
                }, 1000);
            }
        }
        // check and remove empty fields, then pass data to save
        function todo_remove_empty() {
            var inputArr = [];
            jQuery('#todo_todos .todo_textarea_div').each(function () {
                var val_input = jQuery(this).find('input').val();
                if (jQuery.trim(val_input) != '') {
                    inputArr.push(val_input);
                }
            });
            todo_save_data(inputArr);
        }
        // function saves data passed in array format.
        function todo_save_data(data) {
            //console.log(data);
            jQuery(document).find('.todo_status').text('Status: Adding item to list');
            jQuery.ajax({
                url: "<?php echo admin_url('admin-ajax.php'); ?>",
                data: {action: 'todo_save_data', 'todo_data': data},
                method: 'post',
                success: function (dataReturn) {
                    //console.log(dataReturn);
                    jQuery(document).find('.todo_status').text('Status: Item added to list.');
                }
            });
        }
        // add_new_note
        function add_new_note() {
            var handle = jQuery('#todo_textarea_div').find('.draggable_handle').clone();
            if (jQuery(document).find('#todo_textarea_div').length > 0) {
                jQuery('#todo_textarea_div').clone().attr('id', '').find('input').val('').parent().insertAfter('.todo_textarea_div:last').find('input').focus();
            } else {
                var newField = '';
                newField += '<p class="todo_textarea_div" id="todo_textarea_div" contenteditableXX  onkeyup="">';
                newField += '<span class="draggable_handle">::</span>';
                newField += '<input type="text" oninput="return todo_process_textarea(this,event);" onkeyup="return check_key(event, this);" name="todo_textarea_div_input" class="todo_textarea_div_input" value=""/>';
                newField += '<span class="sm_delete_todo">x</span>';
                newField += '</p>';
                jQuery("#todo_todos").append(newField);
            }
            adjust_div_height();
        }

        // adjust div height - to apply overflow scroll and height 
        function adjust_div_height() {

            var a = jQuery(window).height();
            var b = jQuery('.todo_div_wrapper').offset().top;
            var c = jQuery('.todo_div_wrapper').height();

            //jQuery(window).height() -  jQuery('.todo_div_wrapper').offset().top - 86 - 25 
            var maxheight = a - b - 86 - 25;
            jQuery('#todo_todos').css('max-height', maxheight + 'px');
        }
        // check pressed keys and then do action accordingly.
        function check_key(e, ele) {
            //detect enter 
            if (e.which == 13) {
                add_new_note();
            }
            //detect backspace 
            if (e.which == 8) {
                if (jQuery.trim(jQuery(ele).val()) == '') {
                    if (jQuery('.todo_textarea_div').length > 1) {
                        //console.log( jQuery(ele).closest('.todo_textarea_div').prev().find('input').length ) ;
                        jQuery('.todo_textarea_div:first').attr('id', 'todo_textarea_div').find('input').focus();
                        if (jQuery(ele).closest('.todo_textarea_div').prev().find('input').length == 0) {
                            jQuery(ele).closest('.todo_textarea_div').next().find('input').focus();
                        } else {
                            jQuery(ele).closest('.todo_textarea_div').prev().find('input').focus();
                        }
                        jQuery(ele).closest('.todo_textarea_div').remove();
                        jQuery('.todo_textarea_div:first').attr('id', 'todo_textarea_div').find('input');
                    }
                }
            }
        }



        jQuery(function () {
            // hide whole div based on cookie.
            var myCookie = document.cookie.replace(/(?:(?:^|.*;\s*)todo_div_wrapper\s*\=\s*([^;]*).*$)|^.*$/, "$1");
            if (myCookie == 'none')
                jQuery('.todo_div_wrapper').hide();
            // hide whole div based on cookie.
            var todo_todo_main_block_cookie = document.cookie.replace(/(?:(?:^|.*;\s*)todo_todo_main_block_cookie\s*\=\s*([^;]*).*$)|^.*$/, "$1");

            if (todo_todo_main_block_cookie == 'none') {
                jQuery('.todo_todo_main_block').hide();
                jQuery('.slidetoggle-button').html('&xwedge;');
            }
            // make todo sortable.
            jQuery(".todos").sortable({
                revert: true,
                handle: '.draggable_handle',
            });


            // make div's draggables 
            jQuery(".todo_div_wrapper").draggable({
                containment: "window",
                handle: "p.todo_status,h2",
                cancel: ".add_new_note, .slidetoggle-button",
                scroll: false,
            });
            /*jQuery( ".todo_div_wrapper" ).on( "dragstop", function( event, ui ) {
             //console.log(ui.position);
             });*/
            // save on sort
            jQuery(".todos").on("sortstop", function (event, ui) {
                todo_remove_empty();
            });
            // remove todo
            //.todo_textarea_div .sm_delete_todo
            jQuery(document).on("click", ".todo_textarea_div .sm_delete_todo", function (event, ui) {
                jQuery(this).closest('.todo_textarea_div').remove();
                todo_remove_empty();
                adjust_div_height();
            });


            // + - button 
            jQuery(document).on("click", ".slidetoggle-button", function (event, ui) {

                var toggleButton = jQuery('.slidetoggle-button');
                jQuery('.todo_todo_main_block').slideToggle({
                    done: function () {
                        // variable used for ajax saving , as a data
                        var todo_todo_main_block_cookie_block_state = '';

                        if (jQuery('.todo_todo_main_block').is(':visible') == true) {
                            //console.log('if = ' + jQuery('.todo_todo_main_block').is(':visible')) ; 
                            toggleButton.html('&xvee;');
                            document.cookie = 'todo_todo_main_block_cookie=block';
                            todo_todo_main_block_cookie_block_state = 'block';

                        } else {
                            //console.log('else  = ' + jQuery('.todo_todo_main_block').is(':visible')) ; 
                            toggleButton.html('&xwedge;');
                            document.cookie = 'todo_todo_main_block_cookie=none';
                            todo_todo_main_block_cookie_block_state = 'none';
                        }
                    }
                });

            });

            // adjust div's height to control div going outside of window
            adjust_div_height();

        });
    </script>
<?php
}

add_action('admin_footer', 'my_admin_footer_function');

function my_admin_footer_function() {
    echo '<style type="text/css">
	.todo_div_wrapper  {
		position:fixed;
		right:40px;
		top:100px;
		z-index: 99999;
		background: #FFF;
		width:350px;
		border: 1px solid #e5e5e5;
	}
	.todo_div_wrapper h2 {
		cursor: move;
		background: #0073aa;
		color: #FFF;
		padding: 7px 10px;
		display: block;
		margin: 0;
	}
	.todo_div_wrapper .todo_textarea {
		overflow: auto;
		padding: 2px 6px;
		margin: 0;
		background: lightyellow;
		padding: 7px 9px;
	}
	.todo_div_wrapper .todo_textarea_div {
		overflow: auto;
		margin: 0 0 3px 0 ;
		background: #f7fcfe;
		padding: 3px 2px;
		position: relative;
		//border : 1px dashed #555;
	}
	.todo_div_wrapper p.todo_status {
		cursor: move;
		background: #fff;
		color: #777;
		padding: 7px 10px;
		display: block;
		margin: 0;
		border-top: 1px solid #e5e5e5;
	}
	.draggable_handle {
		padding: 3px;
		margin-right: 5px;
		color: #777777;
		cursor: move;
		font-size: 20px;
		line-height: 10px;
		padding: 2px;
	}
	.todo_textarea_div_input {
		width: 90%;
		background: transparent !important;
		border: none !important;
		box-shadow: none !important;
		margin-left: 15px;
	}
	.todo_controls {
		padding: 3px;
		background: #eaeaea;
	}
	.sm_delete_todo {
		position: absolute;
		top: 8px;
		font-size: 13px;
		right: 3px;
		border: 1px solid #777;
		color: #fff;
		background: #777;
		border-radius: 25px;
		padding: 3px;
		line-height: 0.6;
		height: 8px;
		display:none;
		cursor: pointer;
	}
	.todo_div_wrapper .todo_textarea_div:hover  .sm_delete_todo {
		display:block;
	}
	
	.draggable_handle {
		display: none;
		position: absolute;
		top: 9px;
	}
	
	.todo_div_wrapper .todo_textarea_div:hover  .draggable_handle
	{
		display:inline-block;
	}
	
	span.slidetoggle-button {
		position: absolute;
		right: 10px;
		padding: 0 4px;
		cursor: pointer;
	}
	
	#todo_todos {
		overflow-y : auto; 
	}
	
	</style>';
    if (isset($_GET['debug']) or 1) {
        $todo_data = unserialize(( get_option('todo_data_' . get_current_user_id())));

        //restore data back when upgrading from 1.2 to 1.2.4 +
        if (get_option('todo_bkp') != '1') {
            $todo_data2 = unserialize(( get_option('todo_data')));

            if (is_array($todo_data) && is_array($todo_data2)) {
                $todo_data = array_merge($todo_data, $todo_data2);
            }
        }


        if ($todo_data == null OR count($todo_data) <= 0) {
            $todo_data = array('Add items to the ToDo list.');
        }

        $todo_block_visibility = get_option('todo_block_visibility_' . get_current_user_id());
        if ($todo_block_visibility == null OR $todo_block_visibility == '') {
            $todo_block_visibility = 'block';
        }
        echo '<div class="todo_div_wrapper" style="display:' . $todo_block_visibility . '">
			<h2>Todo List <span class="slidetoggle-button" >&xvee;</span></h2>';
        echo '<div class="todo_todo_main_block">
			<div class="todo_controls"><i class="add_new_note" onclick="add_new_note()"><button>Add</button></i></div>
			<!--<textarea onkeyup="return todo_process_textarea(this);" onchange="return todo_process_textarea(this);" class="todo_textarea" rows="5" cols="20">' . get_option('todo_data') . '</textarea>
			<textarea onkeyup="return todo_process_textarea(this);" onchange="return todo_process_textarea(this);" class="todo_textarea" rows="5" cols="20">' . $todo_data . '</textarea>-->';
        ?>
        <div id="todo_todos" class="todos">
        <?php
        foreach ($todo_data as $key => $line) {
            ?>
                <p class="todo_textarea_div" <?php echo ($key == 0 ) ? 'id="todo_textarea_div"' : ''; ?> contenteditableXX  onkeyup="">
                    <span class="draggable_handle">::</span>
                    <input type="text" oninput="return todo_process_textarea(this,event);" onkeyup="return check_key(event, this);" name="todo_textarea_div_input" class="todo_textarea_div_input" value="<?php echo $line; ?>"/>
                    <span class="sm_delete_todo">x</span>
                </p>
        <?php } ?>
        </div>
        <p class="todo_status">Status: </p>
        </div>	
        <?php
        echo '</div>';
    }
}

add_action('wp_ajax_todo_save_data', 'todo_save_data');

function todo_save_data() {
    $data = array_filter($_REQUEST['todo_data']);
    update_option('todo_bkp', '1');

    if (update_option('todo_data_' . get_current_user_id(), serialize(( $data)))) {
        return 'Added to list.';
    } else {
        return 'Operation failed.';
    }
    //return true ;
    return 'Operation failed.';
    wp_die();
}

add_action('wp_ajax_todo_visibility', 'todo_visibility');

function todo_visibility() {
    $data = $_REQUEST['todo_block_visibility'];
    if (update_option('todo_block_visibility_' . get_current_user_id(), htmlentities($data))) {
        echo htmlentities($data);
        return htmlentities($data);
    } else {
        echo 'Failed.';
        return 'Failed.';
    }
    //return true ;
    return 'Failed.';
    wp_die();
}
?>