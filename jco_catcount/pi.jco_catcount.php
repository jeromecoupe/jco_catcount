<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$plugin_info = array(
  'pi_name' => 'JCO Category Count',
  'pi_version' =>'1.2',
  'pi_author' =>'Jerome Coupe',
  'pi_author_url' => 'http://twitter.com/jeromecoupe/',
  'pi_description' => 'Returns the number of entries for a given category.',
  'pi_usage' => Jco_catcount::usage()
  );


class Jco_catcount {
	
	/* --------------------------------------------------------------
	* RETURNED DATA
	* ------------------------------------------------------------ */
	/**
	* Data returned from the plugin.
	*
	* @access	public
	* @var string
	*/
	var $return_data = '';
	
	/* --------------------------------------------------------------
	* CONSTRUCTOR
	* ------------------------------------------------------------ */

	/**
	* Constructor.
	*
	* @access	public
	* @return	void
	*/
	function __construct()
	{
		$this->EE =& get_instance();
		$this->return_data = $this->count_catitems();
	}
	
	
	/**
	* Annoyingly, the supposedly PHP5-only EE2 still requires this PHP4
	* constructor in order to function.
	*
	* @access public
	* @return void
	* method first seen used by Stephen Lewis (https://github.com/experience/you_are_here.ee2_addon)
	*/
	public function Jco_catcount()
	{
		$this->__construct();
	}
	
	/* --------------------------------------------------------------
	* USED FUNCTIONS
	* ------------------------------------------------------------ */
	
	/**
	* Return number of items in category.
	*
	* @access	public
	* @return	mixed: integer, boolean
	*/
	public function Count_catitems()
	{
		//Get parameters and set defaults if parameter not provided
		$cat_id = $this->EE->TMPL->fetch_param('cat_id', '');
		$status = $this->EE->TMPL->fetch_param('status', 'open');
		$channel = $this->EE->TMPL->fetch_param('channel', '');
		$site = $this->EE->config->item('site_id');
		
		//check cat id
		if ($cat_id == "")
		{
			return "ERROR: cat_id parameter MUST BE supplied";
		}
		else
		{
			//create cat_id array (explode values if pipe in tag param, assign value)
			$cat_id = (strpos($cat_id, "|")) ? explode('|', $cat_id) : array($cat_id);
			
			//check each category id in array
			foreach ($cat_id as $value)
			{
				if (is_numeric($value))
				{
					if(!$this->_category_exists($value))
					{
						return "ERROR: there is no category with an id of \"".$value."\" in your database";
					}
				}
				else
				{
					return "ERROR: cat_id parameter \"".$value."\" is not a number";
				}
			}
		}
		
		//Parse status parameter and turn it into an array
		if ($status != "")
		{
			//is there a NOT clause ?
			if (strpos($status, "not") === 0)
			{
				$notclause_status = TRUE;
				$status = substr($status, 4);
				$status = explode('|', $status);
			}
			else
			{
				$notclause_status = FALSE;
				$status = explode('|', $status);
			}
		}
		
		//Parse channel parameter and turn it into an array
		if ($channel != "")
		{
			//is there a NOT clause ?
			if (strpos($channel, "not") === 0)
			{
				$notclause_channel = TRUE;
				$channel = substr($channel, 4);
				$channel = explode('|', $channel);
			}
			else
			{
				$notclause_channel = FALSE;
				$channel = explode('|', $channel);
			}
		}
		
		//Query
		//main part
		$this->EE->db->select('exp_category_posts.entry_id');
		$this->EE->db->from('exp_category_posts');
		$this->EE->db->join('exp_channel_titles', 'exp_category_posts.entry_id = exp_channel_titles.entry_id' );
		$this->EE->db->join('exp_channels', 'exp_channel_titles.channel_id = exp_channels.channel_id' );
		$this->EE->db->where('exp_channel_titles.site_id', $site);
		
		//where part for categories
		$this->EE->db->where_in('exp_category_posts.cat_id', $cat_id);
		
		//where part for status
		if ($status != "")
		{
			if ($notclause_status == FALSE)
			{
				$this->EE->db->where_in('exp_channel_titles.status', $status);
			}
			else
			{
				$this->EE->db->where_not_in('exp_channel_titles.status', $status);
			}
		}
		
		//where part for channel
		if ($channel != "")
		{
			if ($notclause_channel == FALSE)
			{
				$this->EE->db->where_in('exp_channels.channel_name', $channel);
			}
			else
			{
				$this->EE->db->where_not_in('exp_channels.channel_name', $channel);
			}
		}
		
		//count results found and return number
		return $this->EE->db->count_all_results();
		echo $this->EE->db->last_query();
	}
	
	/* --------------------------------------------------------------
	* PRIVATE FUNCTIONS
	* ------------------------------------------------------------ */
	
	/**
	* Check if category_id is a number and if it exists in DB
	*
	* @access	private
	* @return	boolean
	*/
	private function _category_exists($category_id)
	{
		//check in DB that the given cat number exists
		$this->EE->db->select('cat_id');
		$this->EE->db->from('exp_categories');
		$this->EE->db->where('cat_id', $category_id);
		if ($this->EE->db->count_all_results() == 0)
		{
			return FALSE;
		}
		else
		{
			return TRUE;
		}
	}
	
	/* --------------------------------------------------------------
	* PLUGIN USAGE
	* ------------------------------------------------------------ */

	/**
	 * Usage
	 *
	 * This function describes how the plugin is used.
	 *
	 * @access	public
	 * @return	string
	 */
	function usage()
	{
		ob_start(); 
		?>

			Description:
	
			Returns the number of entries for a given category.
	
			------------------------------------------------------
			
			Examples:
			{exp:jco_catcount cat_id="33" status="open|closed" channel="channel"}
	
			Returns
			3
	
			------------------------------------------------------
			
			Parameters:
	
			cat_id="1" : Mandatory
			The ids for the category that you want to output the number of entries for
			Plugin checks if the given category id exists in DB
			You can use piped categories like cat_id="32|33"
	
			status="open|closed" : Optional
			Determines the status of entries you want to count.
			Default is "open"
			You can use not clause: status="not closed"
	
			channel="mychannel" : Optional
			Determines the channel of entries you want to count (useful if you use the same category for various channels)
			You can use not clause: channel="not channel1|channel2"
			
			MSM support
			
			Added MSM support: only outputs results for the current site
		
		<?php
		$buffer = ob_get_contents();

		ob_end_clean(); 

		return $buffer;
	}
	  // END

	}


/* End of file pi.jco_catcount.php */ 
/* Location: ./system/expressionengine/third_party/plugin_name/pi.jco_catcount.php */