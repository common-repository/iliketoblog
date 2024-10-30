<?php
/**
 * Plugin Name: iLikeToBlog
 * Plugin URI: http://iliketoblog.com/
 * Version: 1.1
 * Author: <a href="http://davesweblog.com/">David Parr</a>
 * Description: Free link exchange service for bloggers. Build up quality organic backlinks for your blog. <a href="http://iliketoblog/about">Learn More</a>
 * Copyright 2009  David Parr  (email : dave@iliketoblog.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

if(!function_exists('iltb_ap'))
{
	function iltb_ap()
	{
		global $iLikeToBlog;
		if(!isset($iLikeToBlog))
		{
			return;
		}
		
		add_options_page('iLikeToBlog', 'iLikeToBlog', 9, basename(__FILE__), array(&$iLikeToBlog, 'print_admin_page'));
	}
}

if(!class_exists('iLikeToBlog'))
{
	class iLikeToBlog {
	
		var $admin_options_name;
		
		function get_admin_options()
		{
			$iltb_admin_options = array('unique_key' => '');
			
			$iltb_options = get_option($this->admin_options_name);
			if(!empty($iltb_options))
			{
				foreach($iltb_options as $key => $value)
				{
					$iltb_admin_options[$key] = $value;
				}
			}
			
			update_option($this->admin_options_name, $iltb_admin_options);
			return $iltb_admin_options;
		}
		
		function iLikeToBlog()
		{
		    $this->admin_options_name = 'iLikeToBlogOptions';
		}
		
		function init()
		{
			$this->get_admin_options();
		}
		
		function print_admin_page()
		{
			global $iltb_domain;
			
			$options = $this->get_admin_options();
			
			if(isset($_POST['update_iLikeToBlogSettings']))
			{
				if(isset($_POST['unique_key']))
				{
					$options['unique_key'] = $_POST['unique_key'];
				}
				
				update_option($this->admin_options_name, $options);
				
				echo '<div class="updated"><p>Settings Updated</p></div>';
			}
			
			?>
			
			<div class="wrap">
				<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
					<h2>iLikeToBlog Widget</h2>
					<p>Don't forget to go to appearance &gt; widgets and add the iLikeToBlog widget to your sidebar.</p>
					<h2>iLikeToBlog Settings</h2>
					<p>Here you can edit various settings related to your current installation of the iLikeToBlog plugin.</p>
					<h3>Unique Key</h3>
					<p>Enter the unique key for your blog. This was given to you when you completed registration.</p>
					<p><input type="text" name="unique_key" value="<?php echo $options['unique_key']; ?>" /></p>
					<p><input type="submit" value="Update" name="update_iLikeToBlogSettings" /></p>
				</form>
			</div>
			
			<?php
		}
		
		function iltb_get_links($args)
		{
			extract($args);
			
			$options = $this->get_admin_options();
			
			echo $before_widget . $before_title . 'Links' . $after_title;
			
			$this->iltb_output_links();
		}
		
		function iltb_output_links()
		{
		    echo '<ul>';
			
			$parser = $this->iltb_get_links_xml();
			
			if(!$parser)
			{
			   echo '</ul>';
			}
			else
			{
			    if(class_exists('ILTBXMLParser'))
				{
				    foreach($parser->document->link as $link)
				    {
					    echo '<li><a href="http://' . $link->url[0]->tagData . '">' . $link->title[0]->tagData . '</a></li>';
				    }
				}
				else
				{
				    foreach($parser->link as $link)
					{
						echo '<li><a href="' . $link->url . '">' . $link->title . '</a></li>';
					}
				}
			}
			
			echo '</ul>';
		}
		
		function iltb_get_links_xml()
		{
			$options = $this->get_admin_options();
			$url = 'http://iliketoblog.com/links/get/' . $options['unique_key'];
			
			if(!function_exists('simplexml_load_file'))
			{
				include(dirname(__FILE__) . '/XMLParser.php');
				$contents = @file_get_contents($url);			
				$contents = str_replace('http://', '', $contents);
				$parser = new ILTBXMLParser($contents, true);
				$parser->Parse();
			}
			else
			{
			    $parser = simplexml_load_file($url);
			}
			
			return $parser;
		}
		
		function iltb_loaded()
		{
			$wp_ops = array('title' => 'Partners', "description" => "Display your iltb exchange links");
			wp_register_sidebar_widget('iLikeToBlog', 'iLikeToBlog', array($this, 'iltb_get_links'), $wp_ops);
		}
		
		function iltb_action_links($links, $file)
		{
		    if($file == plugin_basename(dirname(__FILE__) . '/iLikeToBlog.php'))
			{
			    $links[] = "<a href='options-general.php?page=iLikeToBlog.php'>Settings</a>";
			}
			
			return $links;
		}
	
	}
}

$iLikeToBlog = new iLikeToBlog();
add_action('admin_menu', 'iltb_ap');
add_action('plugins_loaded', array(&$iLikeToBlog, 'iltb_loaded'));
add_filter('plugin_action_links', array($iLikeToBlog, 'iltb_action_links'), -10, 2);

function iltb_show_links()
{
	global $iLikeToBlog;
	
	$iLikeToBlog->iltb_output_links();
}
?>
