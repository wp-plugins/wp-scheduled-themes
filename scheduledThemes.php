<?php
/*
 * Plugin Name: WP Scheduled Themes
 * Plugin URI: http://www.itegritysolutions.ca/community/wordpress/scheduled-themes
 * Description: Schedule a theme to display on the live site for holidays or special events.
 * Author: Adam Erstelle
 * Version: 1.6
 * Author URI: http://www.itegritysolutions.ca/
 * 
 * PLEASE NOTE: If you make any modifications to this plugin file directly, please contact me so that
 *              the plugin can be updated for others to enjoy the same freedom and functionality you
 *              are trying to add. Thank you!
 *
 * Copyright 2011  Adam Erstelle
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as 
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
if(!class_exists('ScheduledThemes')){
	class ScheduledThemes{
		var $themeShouldBeOverridden = false;
		var $themeToOverrideWith;
		var $pluginURL;
		var $pluginDIR;
		var $activeThemes;
		var $localized = "wp-scheduled-themes";
		var $debugLines = array();
		
		/**
		 * Class Constructor, Checks to see if theme should be overridden and registers the plugin with Wordpress
		 */
		function __construct(){			
			$this->pluginURL = WP_PLUGIN_URL . '/' . dirname(plugin_basename(__FILE__));
			$this->pluginDIR = WP_PLUGIN_DIR . '/' . dirname(plugin_basename(__FILE__));

			//Language Setup
			$locale = get_locale();
			$mo = $this->pluginDIR . "/languages/" . strtolower($locale).".mo";
			load_textdomain($this->localized, $mo);
			
			$this->query_wordpress_apis();
			$this->determine_theme_override();
			$this->register_hooks();
		}
		
		/**
		 * Makes the calls into the Wordpress API so that results are 'cached' and APIs only called once
		 */
		function query_wordpress_apis(){
			$this->activeThemes = wp_get_themes();
		}
		
		/**
		 * Centralized place for adding all actions and filters for the plugin into wordpress
		 */
		function register_hooks(){
			
			if(is_admin()){
				register_activation_hook(__FILE__, array(&$this,'install'));
				register_deactivation_hook(__FILE__, array(&$this,'deactivate'));
				add_action("admin_menu", array(&$this,"admin_menu_link"));
				add_action('in_admin_header', array(&$this,"warnIfThemesMissing"));
				add_action('admin_enqueue_scripts',array(&$this,'admin_enqueue_scripts'));
			}
			else{
				add_action('wp_head', array(&$this,'wp_head'));
			}
			
			add_action('ScheduledThemesDailyTask', array(&$this,"runDailyCleanup"));
			if(!wp_next_scheduled('ScheduledThemesDailyTask'))
				wp_schedule_event(time(), 'daily', 'ScheduledThemesDailyTask');
			
			if($this->themeShouldBeOverridden){
 				add_filter('template', array(&$this,'get_template'));
	 			add_filter('stylesheet', array(&$this,'get_stylesheet'));
			}
		}
		
		/**
		 * Runs daily SQL to clean the scheduled entries.
		 * Will add 1 year for those expired and will be repeating
		 * Sets a status of inactive for those that have expired and don't repeat.
		 */
		function runDailyCleanup(){
			global $wpdb;
			$table_name = $wpdb->prefix ."scheduledthemes";
			$sql = "UPDATE $table_name SET startTime = DATE_ADD(startTime,INTERVAL 1 YEAR), endTime = DATE_ADD(endTime,INTERVAL 1 YEAR) WHERE repeatYearly=1 AND endTime < NOW();";
			$wpdb->query($sql);
			$sql = "UPDATE $table_name SET status='inactive' WHERE status='active' AND endTime < NOW() AND repeatYearly=0;";
			$wpdb->query($sql);
		}

		/**
		 * Displays a warning in the administration pages if a theme is missing
		 */
		function warnIfThemesMissing(){
			global $wpdb;
			$tableName = $wpdb->prefix ."scheduledthemes";
			$missingThemes = '';
			$sql = "SELECT distinct(themeName) as themeName FROM $tableName WHERE status='active';";
			$results = $wpdb->get_results($sql);
			
			foreach($results as $result){
				$resultFound=false;
				foreach($this->activeThemes as $theme)
				{
					if($result->themeName == $theme){
						$resultFound=true;
						break;			
					}
				}
				
				if(!$resultFound)
					$missingThemes .= ", " . $result->themeName;
			}
			
			if(strlen($missingThemes)>0)
				$this->display_missing_theme_warning($missingThemes);
		}
		
		/**
		 * Installs the table into the database
		 */
		function install(){
			global $wpdb;
			$table_name = $wpdb->prefix ."scheduledthemes";
			if($wpdb->get_var("SHOW TABLES LIKE '$table_name'")!= $table_name){
				$sql = "CREATE TABLE $table_name (
					id mediumint(3) NOT NULL AUTO_INCREMENT,
					startTime datetime NOT NULL,
					endTime datetime NOT NULL,
					themeName varchar(100) NOT NULL,
					repeatYearly tinyint(1) NOT NULL,
					status char(10) NOT NULL DEFAULT 'active',
					UNIQUE KEY id (id)
					);";
				require_once(ABSPATH.'wp-admin/includes/upgrade.php');
				dbDelta($sql);
				add_option("scheduledthemes_db_version","1.0");
			}
		}

		/**
		 * Removes the daily scheduled task
		 */
		function deactivate(){
			wp_clear_scheduled_hook('ScheduledThemesDailyTask');
		}
		
		/**
		 * Adds the Administration link under Appearance in the Wordpress Menu for Administrators
		 */
		function admin_menu_link(){
			add_theme_page(__('Scheduled Themes',$this->localized), __('Scheduled Themes',$this->localized), 'administrator', basename(__FILE__), array(&$this,'admin_options_page'));
		}
		
		/**
		 * Adds html comments to HEAD on the public site
		 */
		function wp_head(){
			echo "\n<!-- WP Scheduled Themes is installed -->";
			if($this->themeShouldBeOverridden)
				echo "\n<!-- Activated theme has been overridden with: ". $this->themeToOverrideWith['Name']." -->\n";
			/*
			echo "\n<!-- WP Scheduled Themes debug: \n";
			foreach($this->debugLines as $debug)
				echo $debug . "\n";
			echo "\n END DEBUG -->";
			*/
		}
		
		/**
		 * Adds the javascript and CSS to the administration page
		 */
		function admin_enqueue_scripts($hook){
			if($hook != 'appearance_page_scheduledThemes') return;
			
			wp_enqueue_script('jquery-ui-datepicker');
			wp_enqueue_script('jQueryValidator',$this->pluginURL .'/js/jquery.validate.min.js',array('jquery'));
			wp_enqueue_script('scheduledThemesScript',$this->pluginURL .'/js/scheduledThemes.js',array('jquery','jquery-ui-core','jquery-ui-datepicker','jQueryValidator'));
			
			wp_enqueue_style('datepickerStyle',$this->pluginURL .'/css/jquery-ui-1.8.11.custom.css');
			wp_enqueue_style('scheduledThemesStyle',$this->pluginURL .'/css/scheduledThemes.css');
		}
		
		/**
		 * Displays the administration page
		 */
		function admin_options_page(){
			if($_POST['_wpnonce'] && wp_verify_nonce($_POST['_wpnonce'], 'scheduledThemesNonceField'))
				$this->save_schedule();
				
			require_once($this->pluginDIR .'/adminPage.php');
		}
		
		/**
		 * Display a warning when a theme is missing
		 */
		function display_missing_theme_warning($missingThemes){
			require_once($this->pluginDIR .'/warning.php');
		}
		
		/**
		 * Queries the database to see if the regular theme should be overridden for this request
		 */
		function determine_theme_override(){
			global $wpdb;
			$tableName = $wpdb->prefix ."scheduledthemes";
			$sql="SELECT themeName FROM $tableName WHERE now() BETWEEN startTime AND endTime AND STATUS='active';";
			$overRiddenTheme = $wpdb->get_var($sql);
			$this->debugLines[]="Got an overRiddenTheme of $overRiddenTheme";
			if(strlen($overRiddenTheme)>0 ){
				$resultFound=false;
				foreach($this->activeThemes as $theme)
				{
					$this->debugLines[]="Checking to see if " . $overRiddenTheme . " = " . $theme;
					if($theme == $overRiddenTheme)
					{
						$this->debugLines[]="Yay it does, marking it to be the theme we override with";
						$this->themeShouldBeOverridden=true;
						$this->themeToOverrideWith = $theme;
						break;
					}
				}
			}
		}
		
		/**
		 * Reads the schedule from the database for a particular status
		 * @param string $status
		 * @return Ambigous <mixed, NULL, multitype:, unknown>
		 */
		function read_schedule($status){
			global $wpdb;
			$tableName = $wpdb->prefix ."scheduledthemes";
			$sql="SELECT id,date_format(startTime,'%Y-%m-%d') as startTime,date_format(endTime,'%Y-%m-%d') as endTime,themeName,repeatYearly FROM $tableName WHERE status='$status' ORDER BY startTime;";
			$results = $wpdb->get_results($sql);
			return $results;
		}
		
		/**
		 * Persists schedule submitted by the user from administration page
		 */
		function save_schedule(){
			global $wpdb;
			$tableName = $wpdb->prefix ."scheduledthemes";
			
			if($_POST['itemKeys'])
				foreach ($_POST['itemKeys'] as $postKey){
					$themeName=$_POST["items$postKey-themeName"];
					$startTime=$_POST["items$postKey-startTime"] . ' 00:00:00';
					$endTime=$_POST["items$postKey-endTime"] . ' 23:59:59';
					$repeat=0;
					if($_POST["items$postKey-repeatYearly"]=='on')
						$repeat=1;
					$isToDelete=$_POST["items$postKey-delete"];
					
					if(!$isToDelete==1)
						$sql = "UPDATE $tableName SET themeName='$themeName', startTime='$startTime', endTime='$endTime', repeatYearly='$repeat' WHERE id=$postKey;";
					else
						$sql = "DELETE FROM $tableName WHERE id=$postKey;";
					
					$wpdb->query($sql);
				}
			if($_POST['newThemeKeys'])
				foreach ($_POST['newThemeKeys'] as $newKey){
					$themeName=$_POST["newTheme$newKey-themeName"];
					$startTime=$_POST["newTheme$newKey-startTime"] . ' 00:00:00';
					$endTime=$_POST["newTheme$newKey-endTime"] . ' 23:59:59';
					$repeat=0;
					if($_POST["newTheme$newKey-repeatYearly"]=='on')
						$repeat=1;
					
					$sql = "INSERT INTO $tableName (themeName,startTime,endTime,repeatYearly) values ('$themeName','$startTime','$endTime',$repeat);";
					
					$wpdb->query($sql);
				}
		}
		
		/**
		 * Hook for Wordpress to actually override the themes template, if applicable
		 * @param Template $template The template that Wordpress thinks it should display to the user
		 * @return The template that this plugin thinks should be displayed
		 */
		function get_template($template){
			if($this->themeShouldBeOverridden){
				$this->debugLines[]="Overriding the template in the template filter";
				return $this->themeToOverrideWith->get_template();
			}
			return $template;
		}
		
		/**
		 * Hook for Wordpress to actually override the themes stylesheet, if applicable
		 * @param Stylesheet $stylesheet The stylesheet that Wordpress thinks it should display to the user
		 * @return The stylesheet that this plugin thinks should be displayed to the user
		 */
		function get_stylesheet($stylesheet){
			if($this->themeShouldBeOverridden){
				$this->debugLines[]="Overriding the stylesheet in the stylesheet filter";
				return $this->themeToOverrideWith->get_stylesheet();
			}
			return $stylesheet;
		}
	}
}

$scheduledThemes = new ScheduledThemes();
?>