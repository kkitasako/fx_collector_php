<?php

/*
 * Collect Exchange data from RRS Feed
 *
 */


class CollectExData {


	private $rss_url;
	private $currency;
	private $init_array;
	private $pub_date;
	private $exchange_rate;

	// Construct Collect Exchange Data Objects
	public function __construct($req_currency) {

		// Set Config Paramete to Array
		$ini_array = parse_ini_file(__ROOT__."/conf/collector.ini",true);
	
		// Set Currency
		$this->currency = $req_currency;
		// Set Currency RRS URL
		$this->rss_url = $ini_array['RRS'][$req_currency];

	 }
 
	 // GET EXCHANGE CURRENCY
	 public function getExCurrency() {
	 	return $this->currency;
	 }

 	// GET EXCHANGE RATE
 	public function getExRate() {
 		return $this->exchange_rate;
 	}

 	// GET PUBLISH DATE
 	public function getPubDate() {
 		// Format & Split PubDate and return Date
		// (date) YYYY-MM-DD (time) HH:MM:SS (timezone) Tokyo
		return  date('Y-m-d', strtotime($this->pub_date));
 	}

 	// GET PUBLISH TIME
 	public function getPubTime() {
 		// Format & Split PubDate and return Time 
		// (date) YYYY-MM-DD (time) HH:MM:SS (timezone) Tokyo
		return date('G:i:s', strtotime($this->pub_date));
 	}

 	// GET PUBLISH TIMEZONE
 	public function getPubTimeZone() {
 		// Format & Split PubDate and return TimeZone
		// (date) YYYY-MM-DD (time) HH:MM:SS (timezone) Tokyo
		return date('e', strtotime($this->pub_date));
	}
	// Request Exchange Data from RRS
	public function requestData() {
		// Request and Parse RRS Data
		$data = simplexml_load_file($this->rss_url, 'SimpleXMLElement', LIBXML_NOCDATA );
  		
		// Set Publish Date
  		$this->pub_date = $data->channel->item->pubDate;
  		
		// Set Exchange Rate
  		$description = $data->channel->item->description;
  		
		// Filter exchange rate from description
  		$this->exchange_rate = trim(substr($description, $of=strpos($description, ' = ')+3 ,strpos($description, ' ',$of)-$of), " ");

	}
}
?>
