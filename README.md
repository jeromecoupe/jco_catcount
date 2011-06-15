#Description:
Simple EE2 only plugin returning the number of items in any given categories

#Examples:
	{exp:jco_catcount cat_id="33" status="open|closed" channel="channel"}

	{exp:jco_catcount cat_id="{category_id}" status="open|closed" channel="channel"}

#Parameters:

`cat_id="1" or cat_id="1|2"`

* Mandatory
* The id(s) for the category that you want to output the number of entries for. You can use piped category ids if needed.
* Plugin checks if the given category id exists in DB

`status="open|closed"`

* Optional
* Defaults to "open"
* Determines the status of entries you want to count.
* You can use not clause: `status="not closed"`

`channel="mychannel"`

* Optional
* Determines the channel of entries you want to count (useful if you use the same category for various channels)
* You can use not clause: `channel="not channel1|channel2"`

#Multiple site manager
* compatible
* only returns entries belonging to the current site
* no `site="1"` parameter but can be easily added if needed (ping me)