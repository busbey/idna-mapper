/** Copyright 2007 Sean Busbey
This file is a part of JSON-RSS Reader

    JSON-RSS Reader is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Foobar is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with JSON-RSS Reader.  If not, see <http://www.gnu.org/licenses/>.
 */

/** @brief compare two rss items
	define less than according to GMT timestamps
 */
function compareItems(itemA, itemB)
{
	var dateA = Date.parse(itemA["pubDate"]);
	var dateB = Date.parse(itemB["pubDate"]);
	dateA - dateB;
}

function muxFeeds(listOfFeeds, channelTitle)
{
	jsonrss = {"version":"2.0", "channel":{"title":channelTitle, "generator":"jsonrss Reader", "items":[]}};
	/* XXX someone above this function, should ensure that all items have some kind of date/time stamping */
	listOfFeeds.map(function feed { jsonrss.items = jsonrss.items.concat(feed.items)} );
	jsonrss.Items = jsonrss.items.sort(compareItems);
	jsonrss;
}
