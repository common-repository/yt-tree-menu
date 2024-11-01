<?php
/*
Plugin Name: YT Tree Menu
Plugin URI: http://ytruly.net/
Description: This plugin creates a widget that displays a menu of all pages in relation to the current page in a CMS tree style layout. The menu is filtered to only list specific pages including child pages. The blog page and posts are supported and it also has the option to exclude items and sort the menu by different columns.
Version: 0.4.2
Author URI: http://ytruly.net/
*/

//GET WIDGET OPTIONS
function yt_get_options_widget() {
	$options = get_option("widget_yt_treemenu_widget");
	if (!is_array( $options )) { $options = array('title' => 'Pages','sort' => 'menu_order','posts' => '5','levels' => '0','exclude' => '','hide' => false); }
	else {
		if ($options['hide'] == 'on') { $options['hide'] = true; }
		else { $options['hide'] = false; }
	}
	return $options;
}

//INCLUDE POSTS PAGE IN MENU
function yt_get_home_widget() {
	$page = false;
	if (get_option('show_on_front') == 'page') { $page = get_page(get_option('page_for_posts')); }
	if ($page) { return $page; }
		else { 
		global $post;
		return $post; }
}

function yt_build_path_widget($post,$path = array()) {
	if ($post->post_parent != 0 && !in_array($post->post_parent,$path)) {
		$path[] = $post->post_parent;
		$path = yt_build_path_widget(get_page($post->post_parent),$path);
	}
	return $path;
}

function yt_sort_menu_by_date_widget($array,$limit) {
	if (is_array($array) && isset($array[0]->post_date)) {
		$sort = array();
		for($i=0;$i < count($array);$i++) {
			if (isset($array[$i]->post_date)) { $sort[strtolower($array[$i]->post_date.'-'.$array[$i]->ID.'-'.$i)] = $i; }
		}
		krsort($sort);
		$output = array();
		$count = 0;
		foreach($sort as $x) {
			$count++;
			if ($count <= $limit) { $output[] = $array[$x]; }
		}
	}
	return $output;
}

function is_home_blog_widget() {
	if (get_option('show_on_front') == 'posts' && is_front_page()) { return true; }
}

function is_static_blog_widget($id = false) {
	if (!$id) {
		global $post;
		$id = $post->ID;
	}
	if (get_option('show_on_front') == 'page' && $id == get_option('page_for_posts')) { return true; }
}

function static_blog_parent_widget() {
	$blog = get_page(get_option('page_for_posts'));
	if ($blog) { return $blog->post_parent; }
}

//ADD CLASSES
function yt_class_widget($item, $echo = 0, $current = false, $post_home = false) {
	
	//GET DATA
	if (!is_page()) { $post = yt_get_home_widget(); }
	else { global $post; }
	
	//BASIC CLASSES
	$output = 'page_item page-item-';
	if (!$post_home || get_option('show_on_front') == 'page') { $output .= $item->ID; } else { $output .= '0'; }
	
	//CURRENT PAGE
	if 	($current
		 || ($item->ID == $post->ID && is_page())
		 || (is_front_page() && get_option('show_on_front') == 'posts' && $post_home)
		 || ($item->ID == $post->ID && $post->ID == get_option('page_for_posts') && !is_singular())) 
			{ $output .= ' current_page_item'; }
	
	//CURRENT PAGE PARENT
	if 	((is_page() && $item->ID == $post->post_parent)
		 || ($item->ID == static_blog_parent_widget() && is_static_blog_widget($post->ID))
		 || (!is_page() && is_singular() && $item->ID == get_option('page_for_posts'))
		 || (!is_page() && is_singular() && $post_home)) 
		 	{ $output .= ' current_page_parent'; }
	
	//CURRENT PAGE ANCESTOR
	if 	((isset($post->ancestors[0]) && in_array($item->ID,$post->ancestors))
		 || (!is_page() && is_singular() && $item->ID == get_option('page_for_posts'))
		 || (!is_page() && is_singular() && $post_home)) 
		 	{ $output .= ' current_page_ancestor'; }
	
	//ECHO OR RETURN
	if ($echo != 0) { echo $output; }
	else { return $output; }
	
}

//BUILD THE MENU
function yt_buildmenu_widget($post, $menu = array(), $parent_id = 0, $level = 0, $path = array()) {
	$level++;
	
	//WIDGET OPTIONS
	$options = yt_get_options_widget();
	
	//GET TOP PAGE
	$top = $parent_id;
	if ($options['hide']) {
		if ($top == 0 & isset($post->ancestors[0])) { $top = $post->ancestors[count($post->ancestors)-1]; }
		if ($top == 0) { $top = $post->post_parent; }
		if ($top == 0) { $top = $post->ID; }
	}
	
	//GET TREE PATH
	if ($path == array()) { 
		if (isset($post->ancestors[0])) { $path = $post->ancestors; }
		if (get_option('show_on_front') == 'page' && is_single() && $post->post_parent != 0) { 
			$path = yt_build_path_widget($post); 
			$top = $path[count($path)-1];
			if (!$options['hide']) { $top = 0; }
		}
		if (is_singular()) { $path[] = $post->ID; }
		if (!$options['hide']) { $path[] = 0; }
	}
	
	//GET PAGES WITH ARGS
	$child_pages = get_pages('sort_column='.$options['sort'].'&exclude='.urlencode($options['exclude']));
	
	//LOOP OUT PAGES
	for ($i=0;$i < count($child_pages);$i++) {
		if ($child_pages[$i]->post_parent == $top && (in_array($child_pages[$i]->ID,$path) || in_array($child_pages[$i]->post_parent,$path))) {
			$child_pages[$i]->level = $level;
			$menu[] = $child_pages[$i];
			if ($level < $options['levels'] || $options['levels'] == 0) { $menu = yt_buildmenu_widget($post,$menu,$child_pages[$i]->ID,$level,$path); }
		}
	}
	return $menu;
}

//OUTPUT THE WIDGET
function yt_treemenu_widget_go($args) {
	extract($args);
	echo "\n<!-- YT TREE MENU PLUGIN -->\n";
	echo $before_widget;
	$options = yt_get_options_widget();
	if (trim($options['title']) != '') {
		echo $before_title;
     	echo $options['title'];
		echo $after_title;
	}
	echo "<ul id=\"yttreemenu\" class=\"children\">";
	yt_treemenu_widget();
	echo "</ul>";
	echo $after_widget;
}

//BUILD THE WIDGET
function yt_treemenu_widget() {
	
	if (!is_page()) { $post = yt_get_home_widget(); }
	elseif (get_option('show_on_front') == 'page' && is_single()) { $post = get_page(get_option('page_for_posts')); }
	if (!$post) { global $post; }
	
	$menu = yt_buildmenu_widget($post);
	$options = yt_get_options_widget();
	$l = 1;
	$start = 1;
	
	//OUTPUT SEARCH RESULTS
	if (is_search()
		|| is_date()
		|| is_category()
		|| is_tag()) { 
		yt_output_menu_posts(0); 
		$start = 0;
		$options['hide'] = true;
	
	//IF BLOG IS TOP LEVEL AND TOP LEVEL IS HIDDEN SHOW POSTS	
	} elseif ($options['hide'] 
				&& count($menu) == 0 && (is_home() 
				|| is_single()) && ($post->ID == get_option('page_for_posts') 
				|| is_front_page() && get_option('show_on_front') == 'posts')) {
					yt_output_menu_posts(0);
	
	//OUTPUT MENU
	} else {
		for($i=0;$i < count($menu); $i++) {
			$l = $menu[$i]->level;
			if (isset($menu[$i-1]->level)) {
				if  ($menu[$i]->level > $menu[$i-1]->level) { echo "\n<ul id=\"yttreemenu".$menu[$i]->level."\" class=\"children\">\n"; } 
				elseif ($menu[$i]->level < $menu[$i-1]->level) { 
						for ($a = 0; $a < ($menu[$i-1]->level - $menu[$i]->level); $a++) { echo "</li>\n</ul>\n"; } 
					echo "</li>\n";
				} else { echo "</li>\n"; } 
			}
			
			//IF HOME PAGE IS POSTS
			if ($start == 1 && get_option('show_on_front') == 'posts') {
				$start = 0;
				
				//HOME LINK
				if (!$options['hide']) {
					echo '<li class="yttml_'.$menu[$i]->level.' ';
					yt_class_widget($menu[$i],1,false,true);
					echo '">';
					echo '<a class="yttma_'.$menu[$i]->level;
					
					//CURRENT ITEM CLASS
					if (is_front_page()) { 
						echo ' yttm_current ';
						echo ' yttm_current_'.$menu[$i]->level; }
					
					//PATH ITEM CLASS
					if (is_single()) { 
						echo ' yttm_path ';
						echo ' yttm_path_'.$menu[$i]->level; }
					
					echo '" href="'.get_option('siteurl').'" title="' . __('Home') . '">' . __('Home') . '</a>';
				}
				
				//LOAD BLOG POSTS
				if (!is_page()) { yt_output_menu_posts($menu[$i]->level); }
				if (!$options['hide']) { echo "</li>\n"; }
			}
			
			echo '<li class="yttml_'.$menu[$i]->level.' ';
			yt_class_widget($menu[$i],1);
			echo '">';
			echo '<a class="yttma_'.$menu[$i]->level;
			
			//CURRENT ITEM CLASS
			$cur_span = false;
			if ($menu[$i]->ID == $post->ID && (get_option('show_on_front') == 'page' && !is_single() || get_option('show_on_front') == 'posts')) { 
			$cur_span = true; 
			echo ' yttm_current ';
			echo ' yttm_current_'.$menu[$i]->level; }
			
			//FIND POST PATH
			$path = array();
			if (is_single() && get_option('show_on_front') == 'page') { $path = yt_build_path_widget($post); }
			
			//PATH ITEM CLASS
			if ((isset($post->ancestors[0]) && in_array($menu[$i]->ID,$post->ancestors)) 
				|| (($menu[$i]->ID == get_option('page_for_posts') 
				|| in_array($menu[$i]->ID,$path)) && get_option('show_on_front') == 'page' && is_single())) {
				   echo ' yttm_path ';
				   echo ' yttm_path_'.$menu[$i]->level; }
			
			echo '" href="'.get_permalink($menu[$i]->ID).'" title="' . __(strip_tags($p->post_title)) . '">' . __(strip_tags($menu[$i]->post_title)) . '</a>';
			
			//GET POSTS
			if ($post->ID == get_option('page_for_posts') && $menu[$i]->ID == get_option('page_for_posts') && get_option('show_on_front') == 'page') { 
				yt_output_menu_posts($menu[$i]->level); 
			}
		}
		
		//CLOSE LISTS
		if (count($menu) > 0) {
			if ($l > 1) { for ($x = 1; $x < $l; $x++) { echo "</li>\n</ul>\n"; } }
			echo "</li>\n";
		}
		
		//IF NO PAGES AND BLOG IS FRONT PAGE - OUTPUT RECENT POSTS
		if (count($menu) == 0 && $start == 1 && get_option('show_on_front') == 'posts' && !is_page()) { yt_output_menu_posts(1); }
	
	}
} 

//GET RECENT POSTS
function yt_get_menu_posts($num = 5,$ex = '') {
	global $post, $wp_query;
	
	//OVERRIDE MENU IF SEARCH, TAG, DATE OR CATEGORY
	if (is_search() 
		|| is_category() 
		|| is_tag() 
		|| is_date()) { 
		$output = $wp_query->posts;
		$output = yt_sort_menu_by_date_widget($output,$num);
	}
	
	//GET POSTS WITH ARGS
	else { $myposts = get_posts('numberposts='.urlencode($num).'&exclude='.urlencode($ex));
	
		//SETUP OUTPUT ARRAY AND POPULATE
		$output = array();
		foreach($myposts as $p) :
			setup_postdata($p);
			$output[] = $p;
		endforeach; 
	
	}
	
	//IF NO POSTS RETURN NOTHING
	if (!empty($output)) { return $output; }
}

//OUTPUT MENU POSTS
function yt_output_menu_posts($level = 1, $before = '') {
	$options = yt_get_options_widget();
	if ($options['posts']-0 != 0 && ($options['levels'] == 0 || $options['levels'] > 1 || $options['levels'] == 1 && $options['hide'])) { 
		$posts = yt_get_menu_posts($options['posts']-0,$options['exclude']);
		global $post;
		
		//IF THERE ARE POSTS - LOOP OUT
		if ($posts) {
		
			//IF TOP-LEVEL NOT HIDDEN - ADD UL - LEVEL UP
			if ($options['hide'] == false) { 
				$level++;
				echo "<ul id=\"yt_submenu".$level."\" class=\"children\">\n";
			}
			
			foreach ($posts as $p) {
				echo '<li class="yttml_'.$level.' ';
				
				//CHECK FOR CURRENT POST
				$cur_span = false;
				if ($p->ID == $post->ID && !is_front_page() && is_single()) { $cur_span = true; }
				yt_class_widget($p,1,$cur_span,false);
				echo '"><a class="yttma_'.$level;
				
				//CURRENT ITEM CLASS
				if ($cur_span) { 
					echo ' yttm_current ';
					echo ' yttm_current_'.$level; }
				
				echo '" href="'.get_permalink($p->ID).'" title="'. __(strip_tags($p->post_title)) . '">' . __(strip_tags($p->post_title)) . "</a></li>\n";
			}
			
			//IF TOP LEVEL NOT HIDDEN - ADD UL	
			if ($options['hide'] == false) { echo "</ul>\n"; }
		
		} elseif (is_search() || is_date() || is_category() || is_tag() || is_404()) {
				echo '<li class="yttml_'.$level.'"><a class="yttma_'.$level.'" href="'.get_option('siteurl').'">' . __('Home') . '</a></li>';
		}
	}
}
 
//WIDGET CONTROL OPTIONS
function yt_treemenu_widget_control() {
	$options = get_option("widget_yt_treemenu_widget");
	if (!is_array( $options )) { $options = array('title' => 'Pages','sort' => 'menu_order','posts' => '5','levels' => '0','exclude' => '','hide' => false); }
	if ($_POST['yt_treemenu_widget-Submit']) {
		$options['title'] = htmlspecialchars($_POST['yt_treemenu_widget-WidgetTitle']);
		$options['sort'] = htmlspecialchars($_POST['yt_treemenu_widget-WidgetSort']);
		$options['exclude'] = htmlspecialchars($_POST['yt_treemenu_widget-WidgetExclude']);
		$options['posts'] = htmlspecialchars($_POST['yt_treemenu_widget-WidgetPosts']-0);
		$options['levels'] = htmlspecialchars($_POST['yt_treemenu_widget-WidgetLevels']-0);
		$options['hide'] = htmlspecialchars($_POST['yt_treemenu_widget-WidgetHide']);
    	update_option("widget_yt_treemenu_widget", $options);
  	}
 
?>
    <p>
        <label for="yt_treemenu_widget-WidgetTitle">Widget Title: </label><br />
        <input class="widefat" type="text" id="yt_treemenu_widget-WidgetTitle" name="yt_treemenu_widget-WidgetTitle" value="<?php echo $options['title'];?>" />
    </p>
    <p>
        <label for="yt_treemenu_widget-WidgetSort">Sort by: </label><br />
        <select id="yt_treemenu_widget-WidgetSort" name="yt_treemenu_widget-WidgetSort" class="widefat">
        	<option value="post_title" <?php if ($options['sort'] == 'post_title') { echo "selected=\"selected\""; } ?>>Page title</option>
        	<option value="menu_order" <?php if ($options['sort'] == 'menu_order') { echo "selected=\"selected\""; } ?>>Page order</option>
            <option value="ID" <?php if ($options['sort'] == 'ID') { echo "selected=\"selected\""; } ?>>Page ID</option>
        </select>
    </p>
    <p>
        <label for="yt_treemenu_widget-WidgetExclude">Exclude: </label><br />
        <input class="widefat" type="text" id="yt_treemenu_widget-WidgetExclude" name="yt_treemenu_widget-WidgetExclude" value="<?php echo $options['exclude'];?>" /><br />
        <small>Page IDs &amp; Post IDs, separated by commas.</small>
    </p>
    <p>
        <label for="yt_treemenu_widget-WidgetPosts">Show Posts: </label>
        <select id="yt_treemenu_widget-WidgetPosts" name="yt_treemenu_widget-WidgetPosts">
        	<option value="0" <?php if ($options['posts']-0 == 0) { echo "selected=\"selected\""; } ?>>0</option>
        	<option value="1" <?php if ($options['posts']-0 == 1) { echo "selected=\"selected\""; } ?>>1</option>
            <option value="2" <?php if ($options['posts']-0 == 2) { echo "selected=\"selected\""; } ?>>2</option>
            <option value="3" <?php if ($options['posts']-0 == 3) { echo "selected=\"selected\""; } ?>>3</option>
            <option value="4" <?php if ($options['posts']-0 == 4) { echo "selected=\"selected\""; } ?>>4</option>
            <option value="5" <?php if ($options['posts']-0 == 5) { echo "selected=\"selected\""; } ?>>5</option>
            <option value="6" <?php if ($options['posts']-0 == 6) { echo "selected=\"selected\""; } ?>>6</option>
            <option value="7" <?php if ($options['posts']-0 == 7) { echo "selected=\"selected\""; } ?>>7</option>
            <option value="8" <?php if ($options['posts']-0 == 8) { echo "selected=\"selected\""; } ?>>8</option>
            <option value="9" <?php if ($options['posts']-0 == 9) { echo "selected=\"selected\""; } ?>>9</option>
        </select>
    </p>
    <p>
        <label for="yt_treemenu_widget-WidgetLevels">Show Levels: </label>
        <select id="yt_treemenu_widget-WidgetLevels" name="yt_treemenu_widget-WidgetLevels">
        	<option value="0" <?php if ($options['levels']-0 == 0) { echo "selected=\"selected\""; } ?>>All</option>
        	<option value="1" <?php if ($options['levels']-0 == 1) { echo "selected=\"selected\""; } ?>>1</option>
            <option value="2" <?php if ($options['levels']-0 == 2) { echo "selected=\"selected\""; } ?>>2</option>
            <option value="3" <?php if ($options['levels']-0 == 3) { echo "selected=\"selected\""; } ?>>3</option>
            <option value="4" <?php if ($options['levels']-0 == 4) { echo "selected=\"selected\""; } ?>>4</option>
        </select>
    </p>
    <p>
        <label for="yt_treemenu_widget-WidgetTitle">Exclude Top-Level Pages: </label>
        <?php if ($options['hide']) { $checked = "checked=\"checked\""; } else { $checked = ""; } ?>
        <input type="checkbox" id="yt_treemenu_widget-WidgetHide" name="yt_treemenu_widget-WidgetHide" <?php echo $checked; ?> /><br />
        <small>(Pages with no parent)</small>
    </p>
    	<input type="hidden" id="yt_treemenu_widget-Submit" name="yt_treemenu_widget-Submit" value="1" />
<?php
} 

function init_yt_treemenu() {
	register_sidebar_widget("YT Tree Menu", "yt_treemenu_widget_go");
	register_widget_control("YT Tree Menu", "yt_treemenu_widget_control", 200, 200 );   
}
add_action("plugins_loaded", "init_yt_treemenu");
?>